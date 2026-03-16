import React, {StrictMode} from 'react';
import {createHashRouter, createRoutesFromElements, Route, RouterProvider} from 'react-router-dom';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {ConnectedAppsListPage} from '../connect/pages/ConnectedAppsListPage';
import {ConnectedAppPage} from '../connect/pages/ConnectedAppPage';
import {ConnectedAppDeletePage} from '../connect/pages/ConnectedAppDeletePage';
import {OpenAppPage} from '../connect/pages/OpenAppPage';
import {QueryClientProvider, QueryClient} from 'react-query';
import {ConnectedAppCatalogPage} from '../connect/pages/ConnectedAppCatalogPage';
import {RegenerateSecretPage} from '../connect/pages/RegenerateSecretPage';

const client = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 10 * 1000, // 10s
      cacheTime: 5 * 60 * 1000, // 5m
    },
  },
});

const router = createHashRouter(
  createRoutesFromElements(
    <>
      <Route path="/connect/connected-apps/:connectionCode/regenerate-secret" element={<RegenerateSecretPage />} />
      <Route path="/connect/connected-apps/:connectionCode/catalogs/:catalogId" element={<ConnectedAppCatalogPage />} />
      <Route path="/connect/connected-apps/:connectionCode/open" element={<OpenAppPage />} />
      <Route path="/connect/connected-apps/:connectionCode/delete" element={<ConnectedAppDeletePage />} />
      <Route path="/connect/connected-apps/:connectionCode" element={<ConnectedAppPage />} />
      <Route path="/connect/connected-apps" element={<ConnectedAppsListPage />} />
    </>
  )
);

export const ConnectedApps = withDependencies(() => (
  <StrictMode>
    <QueryClientProvider client={client}>
      <AkeneoThemeProvider>
        <RouterProvider router={router} />
      </AkeneoThemeProvider>
    </QueryClientProvider>
  </StrictMode>
));
