import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {ActivateAppButton} from '@src/connect/components/ActivateAppButton';
import {renderWithProviders} from '../../../test-utils';

describe('ActivateAppButton', () => {
    it('renders the pending button when isPending is true', () => {
        renderWithProviders(<ActivateAppButton id='app-1' isConnected={false} isDisabled={false} isPending={true} />);

        expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.card.pending')).toBeInTheDocument();
        expect(screen.getByRole('button')).toBeDisabled();
    });

    it('renders the connected button when isConnected is true', () => {
        renderWithProviders(<ActivateAppButton id='app-1' isConnected={true} isDisabled={false} isPending={false} />);

        expect(
            screen.getByText('akeneo_connectivity.connection.connect.marketplace.card.connected')
        ).toBeInTheDocument();
        expect(screen.getByRole('button')).toBeDisabled();
    });

    it('renders the connect link when not connected and not pending', () => {
        renderWithProviders(<ActivateAppButton id='app-1' isConnected={false} isDisabled={false} isPending={false} />);

        expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.card.connect')).toBeInTheDocument();
    });

    it('renders the connect link with the generated url', () => {
        renderWithProviders(<ActivateAppButton id='my-app' isConnected={false} isDisabled={false} isPending={false} />);

        expect(screen.getByRole('link')).toHaveAttribute(
            'href',
            '#akeneo_connectivity_connection_connect_apps_activate?id=my-app'
        );
    });
});
