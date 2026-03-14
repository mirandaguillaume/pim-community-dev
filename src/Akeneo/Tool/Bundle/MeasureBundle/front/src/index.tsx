import React from 'react';
import {createRoot} from 'react-dom/client';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {MicroFrontendDependenciesProvider, Routes} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import {MeasurementApp, ConfigContext, UnsavedChangesContext} from './feature';

const unsavedChanges = {
  hasUnsavedChanges: false,
  setHasUnsavedChanges: (hasChanges: boolean) => (unsavedChanges.hasUnsavedChanges = hasChanges),
};

createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      <MicroFrontendDependenciesProvider routes={routes as Routes}>
        <ConfigContext.Provider value={{families_max: 10, operations_max: 10, units_max: 10}}>
          <UnsavedChangesContext.Provider value={unsavedChanges}>
            <MeasurementApp />
          </UnsavedChangesContext.Provider>
        </ConfigContext.Provider>
      </MicroFrontendDependenciesProvider>
    </ThemeProvider>
  </React.StrictMode>
);
