import { CategoryTreeModel, DropTarget, TreeNode } from '../models';
declare const useCategoryTreeNode: (id: number) => {
    node: TreeNode<CategoryTreeModel> | undefined;
    children: TreeNode<CategoryTreeModel>[];
    loadChildren: () => Promise<void>;
    moveTo: (movedCategoryId: number, target: DropTarget, onMove: () => void) => void;
    getCategoryPosition: (treeNode: TreeNode<CategoryTreeModel>) => number;
    onDeleteCategory: () => void;
    onCreateCategory: () => void;
    isOpen: boolean;
    open: () => void;
    close: () => void;
};
export { useCategoryTreeNode };
