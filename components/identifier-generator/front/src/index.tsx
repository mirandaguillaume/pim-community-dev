import React from 'react';
import {createRoot} from 'react-dom/client';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {
  DangerousMicrofrontendAutomaticAuthenticator,
  MicroFrontendDependenciesProvider,
  Routes,
} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import {IdentifierGeneratorApp} from './feature';
import {IdentifierGeneratorContextProvider} from './feature/context';

DangerousMicrofrontendAutomaticAuthenticator.enable('admin', 'admin');

createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      <MicroFrontendDependenciesProvider routes={routes as Routes}>
        <IdentifierGeneratorContextProvider>
          <IdentifierGeneratorApp />
        </IdentifierGeneratorContextProvider>
      </MicroFrontendDependenciesProvider>
    </ThemeProvider>
  </React.StrictMode>
);
