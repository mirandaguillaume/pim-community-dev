import {useCallback, useContext, useEffect, useMemo, useRef, useState} from 'react';
import {CategoryTreeContext} from '../components';
import {
  BackendCategoryTree,
  buildTreeNodeFromCategoryTree,
  CategoryTreeModel,
  convertToCategoryTree,
  DropTarget,
  TreeNode,
} from '../models';
import {findByIdentifiers, findLoadedDescendantsIdentifiers, findOneByIdentifier, update} from '../helpers';
import {useFetch, useRoute, useRouter} from '@akeneo-pim-community/shared';
import {arrayUnique, useBooleanState} from 'akeneo-design-system';
import {moveCategory} from '../infrastructure';

type Move = {
  identifier: number;
  target: DropTarget;
  status: 'pending' | 'ready';
  onMove: () => void;
};

const useCategoryTreeNode = (id: number) => {
  const router = useRouter();
  const {nodes, setNodes, ...rest} = useContext(CategoryTreeContext);
  const [move, setMove] = useState<Move | null>(null);
  const node = useMemo(() => findOneByIdentifier(nodes, id), [id, nodes]);
  const children = useMemo(() => (!node ? [] : findByIdentifiers(nodes, node.childrenIds)), [node, nodes]);
  const [isOpen, open, close] = useBooleanState(children.length > 0);

  const url = useRoute('pim_enrich_categorytree_children', {
    _format: 'json',
    id: id.toString(),
    select_node_id: '-1',
    with_items_count: '0',
    include_parent: '0',
    include_sub: '0',
    context: 'manage',
  });

  const [childrenData, loadChildren, loadChildrenStatus] = useFetch<BackendCategoryTree>(url);

  const getCategoryPosition = (treeNode: TreeNode<CategoryTreeModel>): number => {
    if (!treeNode.parentId) {
      return 0;
    }
    const categoryParent = findOneByIdentifier(nodes, treeNode.parentId);

    return categoryParent?.childrenIds.indexOf(treeNode.identifier) || 0;
  };

  const moveTo = useCallback(
    (movedCategoryId: number, target: DropTarget, onMove: () => void) => {
      const targetParentNode = findOneByIdentifier(
        nodes,
        target.position === 'in' ? target.identifier : target.parentId
      );
      if (!targetParentNode) {
        console.error(`Parent node ${target.parentId} not found to set the move of node ${target.identifier}`);
        return;
      }

      setMove({
        identifier: movedCategoryId,
        target,
        status:
          target.position === 'in' && targetParentNode.type === 'node' && targetParentNode.childrenStatus === 'idle'
            ? 'pending'
            : 'ready',
        onMove,
      });
    },
    [nodes]
  );

  const doMove = useCallback(
    async (move: Move) => {
      const {identifier, target, status} = move;
      if (status !== 'ready') {
        return;
      }

      if (identifier === target.identifier) {
        return;
      }

      // Compute previousCategoryId from current nodes before the functional update
      const targetParentNodeForApi = findOneByIdentifier(
        nodes,
        target.position === 'in' ? target.identifier : target.parentId
      );
      const previousCategoryId = targetParentNodeForApi
        ? determineAfterWhichCategoryIdentifierToMove(target, targetParentNodeForApi.childrenIds)
        : null;

      // Use functional updater to correctly merge with any pending state updates
      // (e.g. children loaded by useFetch in the same React 18 batch)
      setNodes(currentNodes => {
        const movedNode = findOneByIdentifier(currentNodes, identifier);
        if (!movedNode) {
          console.error(`Failed to move node ${identifier} : Node not found`);
          return currentNodes;
        }

        const targetParentNode = findOneByIdentifier(
          currentNodes,
          target.position === 'in' ? target.identifier : target.parentId
        );
        if (!targetParentNode) {
          console.error(`Failed to move node ${identifier} : Target parent node ${target.parentId} not found`);
          return currentNodes;
        }

        if (!movedNode.parentId) {
          console.error(`Failed to move node ${identifier} : Can not move root node`);
          return currentNodes;
        }
        const originalParentNode = findOneByIdentifier(currentNodes, movedNode.parentId);
        if (!originalParentNode) {
          console.error(`Failed to move node ${identifier} : Moved parent Node ${movedNode.parentId} not found`);
          return currentNodes;
        }

        let newNodesList = currentNodes;

        // update the children of parent
        // We ensure that the moved node is not in the list
        const parentChildrenIds = targetParentNode.childrenIds.filter(id => id !== movedNode.identifier);

        const movedIndex = parentChildrenIds.findIndex(id => id === target.identifier);

        parentChildrenIds.splice(
          target.position === 'in' ? 0 : target.position === 'after' ? movedIndex + 1 : movedIndex,
          0,
          movedNode.identifier
        );

        newNodesList = update(newNodesList, {
          ...targetParentNode,
          childrenIds: parentChildrenIds,
          type: targetParentNode.type === 'leaf' ? 'node' : targetParentNode.type,
          childrenStatus: 'loaded',
        });

        // update parent id for the original node
        newNodesList = update(newNodesList, {
          ...movedNode,
          parentId: targetParentNode.identifier,
        });

        // remove the original id from the original parent's children
        // update the original parent
        if (originalParentNode.identifier !== targetParentNode.identifier) {
          const updateOriginalParentChildren = originalParentNode.childrenIds.filter(id => id !== movedNode.identifier);
          newNodesList = update(newNodesList, {
            ...originalParentNode,
            childrenIds: updateOriginalParentChildren,
            type:
              originalParentNode.type !== 'root' ? (updateOriginalParentChildren.length > 0 ? 'node' : 'leaf') : 'root',
          });
        }

        return newNodesList;
      });

      // Call to backend to persist the movement
      const persistSuccess = await moveCategory(router, {
        identifier,
        parentId: target.position === 'in' ? target.identifier : target.parentId,
        previousCategoryId,
      });

      if (!persistSuccess) {
        console.error(`Failed to persist node ${identifier}`);
      }
    },
    [nodes, router, setNodes]
  );

  const determineAfterWhichCategoryIdentifierToMove = (target: DropTarget, childrenIds: number[]): number | null => {
    if (target.position === 'after') {
      return target.identifier;
    }

    if (target.position === 'before') {
      const targetIndex = childrenIds.indexOf(target.identifier) - 1;
      if (targetIndex >= 0 && childrenIds[targetIndex]) {
        return childrenIds[targetIndex];
      }
    }

    return null;
  };

  // When a category is deleted, update its parent's node and remove the category's node and its descendants.
  const onDeleteCategory = () => {
    if (!node || !node.parentId) {
      return;
    }

    const nodesToRemove = [node.identifier, ...findLoadedDescendantsIdentifiers(nodes, node)];
    const updatedNodes = nodes.filter(treeNode => !nodesToRemove.includes(treeNode.identifier));

    const parentNode = findOneByIdentifier(nodes, node.parentId);
    if (!parentNode) {
      setNodes(updatedNodes);
      return;
    }

    const updatedParentChildren = parentNode.childrenIds.filter(childId => childId !== node.identifier);
    const updatedParent: TreeNode<CategoryTreeModel> = {
      ...parentNode,
      childrenIds: updatedParentChildren,
      type: parentNode.type !== 'root' ? (updatedParentChildren.length > 0 ? 'node' : 'leaf') : 'root',
    };

    setNodes(update(updatedNodes, updatedParent));
  };

  // When a category is created inside the node, force reload children and open it.
  const onCreateCategory = useCallback(() => {
    if (node) {
      setNodes(nodes => {
        const updatedNodes = update(nodes, {
          ...node,
          childrenStatus: 'to-reload',
        });
        return [...updatedNodes];
      });
      open();
    }
  }, [node, open, setNodes]);

  // The node is read from the updater's `nodes`, by id, and not from this render's `node`: this
  // effect rewrites the very node it would otherwise depend on, so `node` as a dependency would
  // re-run it on its own output -- and spreading a render-time snapshot could also overwrite an
  // update queued in the same batch, such as a move's.
  useEffect(() => {
    if (!Array.isArray(childrenData)) {
      return;
    }
    const newChildren: TreeNode<CategoryTreeModel>[] = childrenData.map(child => {
      return buildTreeNodeFromCategoryTree(convertToCategoryTree(child), id);
    });

    setNodes((nodes: TreeNode<CategoryTreeModel>[]) => {
      const currentNode = findOneByIdentifier(nodes, id);
      if (!currentNode) {
        return nodes;
      }

      const updatedNodes = update(nodes, {
        ...currentNode,
        childrenIds: newChildren.map(child => child.identifier),
        childrenStatus: 'loaded',
        type: currentNode.type !== 'root' ? (newChildren.length > 0 ? 'node' : 'leaf') : 'root',
      });

      return arrayUnique<TreeNode<CategoryTreeModel>>(
        [...updatedNodes, ...newChildren],
        (node, currentNode) => node.identifier === currentNode.identifier
      );
    });
  }, [childrenData, id, setNodes]);

  useEffect(() => {
    if (node?.childrenStatus === 'to-reload') {
      loadChildren();
    }
  }, [node?.childrenStatus, loadChildren]);

  // doMove changes with every `nodes` update, and loading the children before a move updates
  // `nodes`: as a dependency it would re-run the effect below mid-move and load the children
  // again. The effect has to fire on `move` transitions only, while calling the latest doMove.
  const latestDoMove = useRef(doMove);
  useEffect(() => {
    latestDoMove.current = doMove;
  }, [doMove]);

  useEffect(() => {
    if (move === null) {
      return;
    }

    if (move.status === 'pending') {
      const loadChildrenBeforeMove = async () => {
        await loadChildren();

        setMove({
          ...move,
          status: 'ready',
        });
      };
      loadChildrenBeforeMove();

      return;
    }

    if (move.status === 'ready') {
      latestDoMove.current(move);
      move.onMove();
      setMove(null);

      return;
    }
  }, [move, loadChildren]);

  // Functional, for the same reason as the children effect above: replacing the list with
  // `update(nodes, ...)` built from this render could drop an update queued in the same batch.
  useEffect(() => {
    if (loadChildrenStatus !== 'error' && loadChildrenStatus !== 'fetching') {
      return;
    }
    const childrenStatus = loadChildrenStatus === 'error' ? 'idle' : 'loading';

    setNodes((nodes: TreeNode<CategoryTreeModel>[]) => {
      const currentNode = findOneByIdentifier(nodes, id);

      return currentNode ? update(nodes, {...currentNode, childrenStatus}) : nodes;
    });
  }, [loadChildrenStatus, id, setNodes]);

  return {
    node,
    children,
    loadChildren,
    moveTo,
    getCategoryPosition,
    onDeleteCategory,
    onCreateCategory,
    isOpen,
    open,
    close,
    ...rest,
  };
};

export {useCategoryTreeNode};
