import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {CustomAppList} from '@src/connect/components/CustomApps/CustomAppList';
import {CustomApps} from '@src/model/app';
import {renderWithProviders} from '../../../../test-utils';

jest.mock('@src/connect/hooks/use-custom-apps-limit-reached', () => ({
    useCustomAppsLimitReached: () => ({isLoading: false, isError: false, data: false, error: null}),
}));

const emptyApps: CustomApps = {total: 0, apps: []};

const oneApp: CustomApps = {
    total: 1,
    apps: [
        {
            id: 'app-1',
            name: 'Test App',
            logo: null,
            author: 'Author',
            url: null,
            activate_url: 'https://example.com/activate',
            callback_url: 'https://example.com/callback',
            connected: false,
        },
    ],
};

describe('CustomAppList', () => {
    it('renders the section title', () => {
        renderWithProviders(<CustomAppList customApps={emptyApps} isConnectLimitReached={false} />);

        expect(
            screen.getByText('akeneo_connectivity.connection.connect.marketplace.custom_apps.title')
        ).toBeInTheDocument();
    });

    it('renders the empty message when there are no apps', () => {
        renderWithProviders(<CustomAppList customApps={emptyApps} isConnectLimitReached={false} />);

        expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.apps.empty')).toBeInTheDocument();
    });

    it('renders the connection limit warning when isConnectLimitReached', () => {
        const {container} = renderWithProviders(<CustomAppList customApps={emptyApps} isConnectLimitReached={true} />);

        expect(container.textContent).toContain(
            'akeneo_connectivity.connection.connection.constraint.connections_number_limit_reached'
        );
    });

    it('renders app cards when apps are provided', () => {
        renderWithProviders(<CustomAppList customApps={oneApp} isConnectLimitReached={false} />);

        expect(screen.getByText('Test App')).toBeInTheDocument();
    });

    it('renders no warning when neither limit is reached', () => {
        const {container} = renderWithProviders(<CustomAppList customApps={emptyApps} isConnectLimitReached={false} />);

        expect(container.textContent).not.toContain(
            'akeneo_connectivity.connection.connection.constraint.connections_number_limit_reached'
        );
        expect(container.textContent).not.toContain(
            'akeneo_connectivity.connection.connect.custom_apps.creation_limit_reached'
        );
    });
});
