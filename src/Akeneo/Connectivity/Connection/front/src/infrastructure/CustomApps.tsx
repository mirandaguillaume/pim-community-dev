import React, {StrictMode} from 'react';
import {createHashRouter, createRoutesFromElements, Route, RouterProvider} from 'react-router-dom';
import {QueryClientProvider, QueryClient} from 'react-query';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {CreateCustomAppPage} from '../connect/pages/CreateCustomAppPage';
import {DeleteCustomAppPromptPage} from '../connect/pages/DeleteCustomAppPromptPage';

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
            <Route path='/connect/custom-apps/create' element={<CreateCustomAppPage />} />
            <Route path='/connect/custom-apps/:customAppId/delete' element={<DeleteCustomAppPromptPage />} />
        </>
    )
);

export const CustomApps = withDependencies(() => (
    <StrictMode>
        <QueryClientProvider client={client}>
            <AkeneoThemeProvider>
                <RouterProvider router={router} />
            </AkeneoThemeProvider>
        </QueryClientProvider>
    </StrictMode>
));
