import React, {FC, PropsWithChildren} from 'react';
import {DependenciesContext} from '@akeneo-pim-community/shared';
import {dependencies} from './dependencies';

const DependenciesProvider: FC<PropsWithChildren> = ({children}) => {
  return <DependenciesContext.Provider value={dependencies}>{children}</DependenciesContext.Provider>;
};

export {DependenciesProvider};
