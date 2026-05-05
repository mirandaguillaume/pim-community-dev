import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import ConnectedAppCardDescription from '@src/connect/components/ConnectedApps/ConnectedAppCardDescription';
import {ConnectedApp} from '@src/model/Apps/connected-app';
import {renderWithProviders} from '../../../../test-utils';

const baseApp: ConnectedApp = {
    id: 'app-id',
    name: 'My App',
    scopes: [],
    connection_code: 'my_app',
    logo: null,
    author: 'Acme Corp',
    user_group_name: 'app_my_app',
    connection_username: 'app_my_app',
    categories: ['ecommerce'],
    certified: false,
    partner: null,
    is_custom_app: false,
    is_pending: false,
    has_outdated_scopes: false,
    is_loaded: true,
    is_listed_on_the_appstore: true,
};

describe('ConnectedAppCardDescription', () => {
    it('renders nothing when is_loaded is false', () => {
        const {container} = renderWithProviders(
            <ConnectedAppCardDescription connectedApp={{...baseApp, is_loaded: false}} />
        );

        expect(container.textContent).toBe('');
    });

    it('renders the not-listed error when app is not on the appstore and not custom', () => {
        renderWithProviders(
            <ConnectedAppCardDescription
                connectedApp={{...baseApp, is_listed_on_the_appstore: false, is_custom_app: false}}
            />
        );

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.connect.connected_apps.list.card.not_listed_on_the_appstore'
            )
        ).toBeInTheDocument();
    });

    it('renders the pending message when is_pending is true', () => {
        renderWithProviders(<ConnectedAppCardDescription connectedApp={{...baseApp, is_pending: true}} />);

        expect(
            screen.getByText('akeneo_connectivity.connection.connect.connected_apps.list.card.pending')
        ).toBeInTheDocument();
    });

    it('renders the outdated scopes warning when has_outdated_scopes is true', () => {
        renderWithProviders(<ConnectedAppCardDescription connectedApp={{...baseApp, has_outdated_scopes: true}} />);

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.connect.connected_apps.list.card.new_access_authorization_required'
            )
        ).toBeInTheDocument();
    });

    it('renders the category tag when has_outdated_scopes and categories are set', () => {
        renderWithProviders(
            <ConnectedAppCardDescription
                connectedApp={{...baseApp, has_outdated_scopes: true, categories: ['ecommerce']}}
            />
        );

        expect(screen.getByText('ecommerce')).toBeInTheDocument();
    });

    it('renders the author and category in the default state', () => {
        const {container} = renderWithProviders(<ConnectedAppCardDescription connectedApp={baseApp} />);

        expect(container.textContent).toContain(
            'akeneo_connectivity.connection.connect.connected_apps.list.card.developed_by'
        );
        expect(screen.getByText('ecommerce')).toBeInTheDocument();
    });
});
