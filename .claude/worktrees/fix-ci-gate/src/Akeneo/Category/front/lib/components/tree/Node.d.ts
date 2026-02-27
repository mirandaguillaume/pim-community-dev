import { FC } from 'react';
import { CategoryTreeModel } from '../../models';
declare type Props = {
    id: number;
    label: string;
    code: string;
    index?: number;
    orderable?: boolean;
    followCategory?: (category: CategoryTreeModel) => void;
    onCategoryMoved?: () => void;
    addCategory?: (parentCode: string, onCreate: () => void) => void;
    deleteCategory?: (identifier: number, label: string, code: string, numberOfProducts: number, onDelete: () => void) => void;
};
declare const Node: FC<Props>;
export { Node };
