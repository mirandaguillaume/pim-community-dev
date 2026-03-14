import React from 'react';
import {createRoot} from 'react-dom/client';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {MicroFrontendDependenciesProvider, Routes} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import {FakePIM} from './FakePIM';

createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      <MicroFrontendDependenciesProvider routes={routes as Routes}>
        <FakePIM />
      </MicroFrontendDependenciesProvider>
    </ThemeProvider>
  </React.StrictMode>
);
