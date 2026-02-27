import { FC } from 'react';
import { CategoryTreeModel } from '../../models';
declare type Props = {
    root: CategoryTreeModel | null;
    orderable?: boolean;
    followCategory?: (category: CategoryTreeModel) => void;
    addCategory?: (parentCode: string, onCreate: () => void) => void;
    deleteCategory?: (identifier: number, label: string, code: string, numberOfProducts: number, onDelete: () => void) => void;
};
declare const CategoryTree: FC<Props>;
export { CategoryTree };
