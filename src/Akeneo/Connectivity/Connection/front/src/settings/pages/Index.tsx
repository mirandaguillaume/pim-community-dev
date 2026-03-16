import React from 'react';
import {Route, Routes} from 'react-router-dom';
import {ConnectionsProvider} from '../connections-context';
import {CreateConnection} from './CreateConnection';
import {DeleteConnection} from './DeleteConnection';
import {EditConnection} from './EditConnection';
import {ListConnections} from './ListConnections';
import {RegenerateConnectionPassword} from './RegenerateConnectionPassword';
import {RegenerateConnectionSecret} from './RegenerateConnectionSecret';
import {SettingsErrorBoundary} from './SettingsErrorBoundary';
import {WrongCredentialsCombinationsProvider} from '../wrong-credentials-combinations-context';

export const Index = () => (
    <SettingsErrorBoundary>
        <WrongCredentialsCombinationsProvider>
            <ConnectionsProvider>
                <Routes>
                    <Route path='/connect/connection-settings/:code/edit' element={<EditConnection />} />
                    <Route
                        path='/connect/connection-settings/:code/regenerate-secret'
                        element={<RegenerateConnectionSecret />}
                    />
                    <Route
                        path='/connect/connection-settings/:code/regenerate-password'
                        element={<RegenerateConnectionPassword />}
                    />
                    <Route path='/connect/connection-settings/:code/delete' element={<DeleteConnection />} />
                    <Route path='/connect/connection-settings/create' element={<CreateConnection />} />
                    <Route path='/connect/connection-settings' element={<ListConnections />} />
                </Routes>
            </ConnectionsProvider>
        </WrongCredentialsCombinationsProvider>
    </SettingsErrorBoundary>
);
