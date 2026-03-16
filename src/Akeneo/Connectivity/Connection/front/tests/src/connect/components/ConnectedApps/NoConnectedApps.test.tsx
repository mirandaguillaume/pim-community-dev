import React from 'react';
import '@testing-library/jest-dom';
import {screen, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, MockFetchResponses, mockFetchResponses} from '../../../../test-utils';
import {NoConnectedApps} from '@src/connect/components/ConnectedApps/NoConnectedApps';

beforeEach(() => {
    fetchMock.resetMocks();
});

test('No connected apps section renders', async () => {
    renderWithProviders(<NoConnectedApps />);
    await waitFor(() => screen.getByText('akeneo_connectivity.connection.connect.connected_apps.list.apps.empty'));

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.apps.empty')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.apps.check_marketplace', {
            exact: false,
        })
    ).toBeInTheDocument();
});
