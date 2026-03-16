import React, {StrictMode} from 'react';
import {createHashRouter, createRoutesFromElements, Route, RouterProvider} from 'react-router-dom';
import {AppActivatePage} from '../connect/pages/AppActivatePage';
import {AppAuthenticatePage} from '../connect/pages/AppAuthenticatePage';
import {AppAuthorizePage} from '../connect/pages/AppAuthorizePage';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';

const router = createHashRouter(
  createRoutesFromElements(
    <>
      <Route path="/connect/apps/activate" element={<AppActivatePage />} />
      <Route path="/connect/apps/authorize" element={<AppAuthorizePage />} />
      <Route path="/connect/apps/authenticate" element={<AppAuthenticatePage />} />
    </>
  )
);

export const Apps = withDependencies(() => (
  <StrictMode>
    <AkeneoThemeProvider>
      <RouterProvider router={router} />
    </AkeneoThemeProvider>
  </StrictMode>
));
