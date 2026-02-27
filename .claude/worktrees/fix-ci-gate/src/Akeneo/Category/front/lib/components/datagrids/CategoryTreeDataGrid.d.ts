import { FC } from 'react';
import { CategoryTreeModel } from '../../models';
declare type Props = {
    trees: CategoryTreeModel[];
    refreshCategoryTrees: () => void;
};
declare const CategoryTreesDataGrid: FC<Props>;
export { CategoryTreesDataGrid };
