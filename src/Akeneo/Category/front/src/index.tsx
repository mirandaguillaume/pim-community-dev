import {MicroFrontendDependenciesProvider, Routes as SharedRoutes} from '@akeneo-pim-community/shared';
import {pimTheme} from 'akeneo-design-system';
import React, {StrictMode} from 'react';
import {createRoot} from 'react-dom/client';
import {Route, HashRouter as Router, Routes} from 'react-router-dom';
import {ThemeProvider} from 'styled-components';
import {FakePIM} from './FakePIM';
import {Page as ConfigurationPage, ConfigurationProvider} from './configuration';
import {CategoriesApp} from './feature';
import {routes} from './routes.json';

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <ThemeProvider theme={pimTheme}>
      <ConfigurationProvider>
        <MicroFrontendDependenciesProvider routes={routes as SharedRoutes}>
          <FakePIM>
            <Router basename="/">
              <Routes>
                <Route path="/configuration" element={<ConfigurationPage />} />
                <Route path="/*" element={
                  <CategoriesApp
                    setCanLeavePage={canLeavePage => console.debug('Can leave page:', canLeavePage)}
                    setLeavePageMessage={leavePageMessage => console.debug('Leave page message:', leavePageMessage)}
                  />
                } />
              </Routes>
            </Router>
          </FakePIM>
        </MicroFrontendDependenciesProvider>
      </ConfigurationProvider>
    </ThemeProvider>
  </StrictMode>
);
