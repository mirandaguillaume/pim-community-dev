import { PlaceholderPosition, TreeNode } from '../models';
import { DropTarget } from '../components/providers/OrderableTreeProvider';
import { CursorPosition } from '../components';
declare type ReorderOnDropHandler = (identifier: number, target: DropTarget, callback: () => void) => void;
declare const useDropTreeNode: <T>(node: TreeNode<T> | undefined, reorder: ReorderOnDropHandler) => {
    dropTarget: DropTarget | null;
    onDrop: () => void;
    onDragOver: (target: Element, cursorPosition: CursorPosition) => void;
    placeholderPosition: PlaceholderPosition;
    onDragEnd: () => void;
    onDragEnter: () => void;
    onDragLeave: () => void;
};
export type { ReorderOnDropHandler };
export { useDropTreeNode };
