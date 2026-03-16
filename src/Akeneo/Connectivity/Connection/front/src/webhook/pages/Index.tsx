import React from 'react';
import {Route, Routes} from 'react-router-dom';
import {EditConnectionWebhook} from './EditConnectionWebhook';
import {ErrorBoundary} from './ErrorBoundary';
import {EventLogs} from './EventLogs';
import {RegenerateWebhookSecret} from './RegenerateWebhookSecret';

const Index = () => (
    <ErrorBoundary>
        <Routes>
            <Route
                path='/connect/connection-settings/:connectionCode/event-subscription/regenerate-secret'
                element={<RegenerateWebhookSecret />}
            />
            <Route
                path='/connect/connection-settings/:connectionCode/event-subscription'
                element={<EditConnectionWebhook />}
            />
            <Route path='/connect/connection-settings/:connectionCode/event-logs' element={<EventLogs />} />
        </Routes>
    </ErrorBoundary>
);

export {Index};
