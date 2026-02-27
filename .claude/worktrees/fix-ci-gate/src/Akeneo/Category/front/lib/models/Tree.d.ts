declare type TreeNode<T> = {
    identifier: number;
    label: string;
    code: string;
    parentId: number | null;
    childrenIds: number[];
    data: T;
    type: 'leaf' | 'root' | 'node';
    childrenStatus: 'idle' | 'loaded' | 'loading' | 'to-reload';
};
declare type PlaceholderPosition = 'bottom' | 'top' | 'middle' | 'none';
declare type DraggedNode = {
    parentId: number;
    position: number;
    identifier: number;
};
declare type HoveredNode = {
    parentId: number;
    position: number;
    identifier: number;
};
declare type DropTargetPosition = 'after' | 'before' | 'in';
declare type DropTarget = {
    position: DropTargetPosition;
    parentId: number;
    identifier: number;
};
export type { TreeNode, PlaceholderPosition, DraggedNode, HoveredNode, DropTargetPosition, DropTarget };
