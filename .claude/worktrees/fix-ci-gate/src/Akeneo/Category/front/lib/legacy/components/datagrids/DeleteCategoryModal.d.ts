import { FC } from 'react';
declare type DeleteCategoryModalProps = {
    categoryLabel: string;
    closeModal: () => void;
    deleteCategory: () => void;
    message: string;
};
declare const DeleteCategoryModal: FC<DeleteCategoryModalProps>;
export { DeleteCategoryModal };
