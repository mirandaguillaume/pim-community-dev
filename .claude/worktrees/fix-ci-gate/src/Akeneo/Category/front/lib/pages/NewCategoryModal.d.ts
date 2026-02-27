import { FC } from 'react';
declare type NewCategoryModalProps = {
    closeModal: () => void;
    onCreate: () => void;
    parentCode?: string;
};
declare const NewCategoryModal: FC<NewCategoryModalProps>;
export { NewCategoryModal };
