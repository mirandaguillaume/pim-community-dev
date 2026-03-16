import React, {StrictMode} from 'react';
import {createHashRouter, createRoutesFromElements, Navigate, Route, RouterProvider} from 'react-router-dom';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {SelectUserProfilePage} from '../connect/pages/SelectUserProfilePage';
import {MarketplacePage} from '../connect/pages/MarketplacePage';
import {QueryClient, QueryClientProvider} from 'react-query';

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
            <Route path='/connect/marketplace/profile' element={<Navigate to='/connect/app-store/profile' replace />} />
            <Route path='/connect/app-store/profile' element={<SelectUserProfilePage />} />
            <Route path='/connect/marketplace' element={<Navigate to='/connect/app-store' replace />} />
            <Route path='/connect/app-store' element={<MarketplacePage />} />
        </>
    )
);

export const Marketplace = withDependencies(() => (
    <StrictMode>
        <QueryClientProvider client={client}>
            <AkeneoThemeProvider>
                <RouterProvider router={router} />
            </AkeneoThemeProvider>
        </QueryClientProvider>
    </StrictMode>
));
