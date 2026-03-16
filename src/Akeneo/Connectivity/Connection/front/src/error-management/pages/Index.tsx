import React from 'react';
import {Route, Routes} from 'react-router-dom';
import {ConnectionMonitoring} from './ConnectionMonitoring';
import {ErrorBoundary} from './ErrorBoundary';

const Index = () => (
    <ErrorBoundary>
        <Routes>
            <Route path='/connect/connection-settings/:connectionCode/monitoring' element={<ConnectionMonitoring />} />
        </Routes>
    </ErrorBoundary>
);

export {Index};
