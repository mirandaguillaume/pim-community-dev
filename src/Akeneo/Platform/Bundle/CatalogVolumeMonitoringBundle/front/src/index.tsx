import React from 'react';
import {createRoot} from 'react-dom/client';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {MicroFrontendDependenciesProvider, Routes, Translations} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import translations from './translations.json';
import {CatalogVolumeMonitoringApp} from './feature';
import {FakePIM} from './FakePIM';
import {getMockCatalogVolume} from './feature';

createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      <MicroFrontendDependenciesProvider routes={routes as Routes} translations={translations as Translations}>
        <FakePIM>
          <CatalogVolumeMonitoringApp getCatalogVolumes={getMockCatalogVolume} />
        </FakePIM>
      </MicroFrontendDependenciesProvider>
    </ThemeProvider>
  </React.StrictMode>
);
