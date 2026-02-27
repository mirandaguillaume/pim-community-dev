import { CategoryTreeModel, TreeNode } from '../models';
declare const findRoot: <T>(treeNodes: TreeNode<T>[]) => TreeNode<T> | undefined;
declare const findByIdentifiers: <T>(treeNodes: TreeNode<T>[], identifiers: number[]) => TreeNode<T>[];
declare const findOneByIdentifier: <T>(treeNodes: TreeNode<T>[], identifier: number) => TreeNode<T> | undefined;
declare const isDescendantOf: <T>(treeNodes: TreeNode<T>[], identifier: number, parentId: number) => boolean;
declare const findLoadedDescendantsIdentifiers: <T>(treeNodes: TreeNode<T>[], parent: TreeNode<T>) => number[];
declare const update: <T>(treeNodes: TreeNode<T>[], updatedNode: TreeNode<T>) => TreeNode<T>[];
declare const buildNodesFromCategoryTree: (root: CategoryTreeModel) => TreeNode<CategoryTreeModel>[];
export { findByIdentifiers, findOneByIdentifier, findRoot, update, isDescendantOf, findLoadedDescendantsIdentifiers, buildNodesFromCategoryTree, };
