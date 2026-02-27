import { FC } from 'react';
declare type DeleteCategoryModalProps = {
    categoryLabel: string;
    closeModal: () => void;
    deleteCategory: () => void;
    message: string;
    numberOfProducts?: number;
    categoryId: number;
};
declare const DeleteCategoryModal: FC<DeleteCategoryModalProps>;
export { DeleteCategoryModal };
