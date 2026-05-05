import React from 'react';
import '@testing-library/jest-dom';
import {screen, fireEvent} from '@testing-library/react';
import {CreateCustomAppCredentials} from '@src/connect/components/CustomApps/CreateCustomAppCredentials';
import {renderWithProviders} from '../../../../test-utils';

const credentials = {clientId: 'client-abc', clientSecret: 'secret-xyz'};

describe('CreateCustomAppCredentials', () => {
    it('renders the title', () => {
        renderWithProviders(<CreateCustomAppCredentials onClose={jest.fn()} credentials={credentials} />);

        expect(
            screen.getByText('akeneo_connectivity.connection.connect.custom_apps.create_modal.credentials.title')
        ).toBeInTheDocument();
    });

    it('renders the client id value', () => {
        renderWithProviders(<CreateCustomAppCredentials onClose={jest.fn()} credentials={credentials} />);

        expect(screen.getByText('client-abc')).toBeInTheDocument();
    });

    it('renders the client secret value', () => {
        renderWithProviders(<CreateCustomAppCredentials onClose={jest.fn()} credentials={credentials} />);

        expect(screen.getByText('secret-xyz')).toBeInTheDocument();
    });

    it('renders the done button', () => {
        renderWithProviders(<CreateCustomAppCredentials onClose={jest.fn()} credentials={credentials} />);

        expect(screen.getByText('pim_common.done')).toBeInTheDocument();
    });

    it('calls onClose when the done button is clicked', () => {
        const onClose = jest.fn();
        renderWithProviders(<CreateCustomAppCredentials onClose={onClose} credentials={credentials} />);

        fireEvent.click(screen.getByText('pim_common.done'));

        expect(onClose).toHaveBeenCalledTimes(1);
    });
});
