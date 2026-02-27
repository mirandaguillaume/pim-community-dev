import React, { FC } from 'react';
declare type EditCategoryState = {
    setCanLeavePage: (canLeavePage: boolean) => void;
};
declare const EditCategoryContext: React.Context<EditCategoryState>;
declare type Props = {
    setCanLeavePage: (canLeavePage: boolean) => void;
};
declare const EditCategoryProvider: FC<Props>;
export { EditCategoryProvider, EditCategoryContext };
