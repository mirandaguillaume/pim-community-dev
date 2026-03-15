import React, {createContext, FC, ReactNode} from 'react';

type EditCategoryState = {
  setCanLeavePage: (canLeavePage: boolean) => void;
};

const EditCategoryContext = createContext<EditCategoryState>({
  setCanLeavePage: () => {},
});

type Props = {
  children?: ReactNode;
  setCanLeavePage: (canLeavePage: boolean) => void;
};

const EditCategoryProvider: FC<Props> = ({children, setCanLeavePage}) => {
  return <EditCategoryContext.Provider value={{setCanLeavePage}}>{children}</EditCategoryContext.Provider>;
};

export {EditCategoryProvider, EditCategoryContext};
