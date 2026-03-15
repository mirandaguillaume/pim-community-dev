import React, {createContext, FC, useContext, ReactNode} from 'react';
import {AttributeGroupsStatusCollection, useFetchAllAttributeGroupsStatus} from '../../infrastructure/hooks';

type AttributeGroupsStatusContextState = {
  load: () => void;
  status: AttributeGroupsStatusCollection;
};

const AttributeGroupsStatusContext = createContext<AttributeGroupsStatusContextState>({
  load: () => {},
  status: {},
});

AttributeGroupsStatusContext.displayName = 'AttributeGroupsStatusContext';

const useAttributeGroupsStatusContext = () => {
  return useContext(AttributeGroupsStatusContext);
};

const AttributeGroupsStatusProvider: FC<{children?: ReactNode}> = ({children}) => {
  const {load, status} = useFetchAllAttributeGroupsStatus();

  return (
    <AttributeGroupsStatusContext.Provider value={{load, status}}>{children}</AttributeGroupsStatusContext.Provider>
  );
};

export {AttributeGroupsStatusProvider, useAttributeGroupsStatusContext, AttributeGroupsStatusContext};
