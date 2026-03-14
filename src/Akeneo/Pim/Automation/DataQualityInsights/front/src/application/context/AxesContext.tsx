import React, {createContext, FC, ReactNode, useContext} from 'react';

export type AxesContextState = {
  axes: string[];
};

export const AxesContext = createContext<AxesContextState>({
  axes: [],
});

AxesContext.displayName = 'AxesContext';

export const useAxesContext = (): AxesContextState => {
  return useContext(AxesContext);
};

type ProviderProps = AxesContextState & {children?: ReactNode};

export const AxesContextProvider: FC<ProviderProps> = ({children, ...axes}) => {
  return <AxesContext.Provider value={axes}>{children}</AxesContext.Provider>;
};
