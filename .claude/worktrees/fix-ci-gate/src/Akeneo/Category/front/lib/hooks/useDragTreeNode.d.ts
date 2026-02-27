import { TreeNode } from '../models';
declare const useDragTreeNode: <T>(node: TreeNode<T> | undefined, index: number) => {
    draggedNode: import("../models/Tree").DraggedNode | null;
    isDraggable: boolean | undefined;
    isDragged: () => boolean;
    onDragStart: () => void;
};
export { useDragTreeNode };
