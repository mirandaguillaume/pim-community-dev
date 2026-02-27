import React, { FC } from 'react';
import { CategoryTreeModel, TreeNode } from '../../models';
declare type CategoryTreeState = {
    nodes: TreeNode<CategoryTreeModel>[];
    setNodes: React.Dispatch<React.SetStateAction<TreeNode<CategoryTreeModel>[]>>;
};
declare const CategoryTreeContext: React.Context<CategoryTreeState>;
declare type Props = {
    root: CategoryTreeModel;
};
declare const CategoryTreeProvider: FC<Props>;
export type { CategoryTreeState };
export { CategoryTreeProvider, CategoryTreeContext };
