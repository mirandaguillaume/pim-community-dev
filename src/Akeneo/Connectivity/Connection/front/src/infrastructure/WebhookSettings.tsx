import React, {StrictMode} from 'react';
import {createHashRouter, createRoutesFromElements, Route, RouterProvider} from 'react-router-dom';
import {Index} from '../webhook/pages/Index';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';

const router = createHashRouter(createRoutesFromElements(<Route path='/*' element={<Index />} />));

const WebhookSettings = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <RouterProvider router={router} />
        </AkeneoThemeProvider>
    </StrictMode>
));

export {WebhookSettings};
