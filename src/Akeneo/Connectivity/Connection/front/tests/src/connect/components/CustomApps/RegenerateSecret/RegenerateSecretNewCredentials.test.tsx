import React from 'react';
import '@testing-library/jest-dom';
import {screen, fireEvent} from '@testing-library/react';
import {RegenerateSecretNewCredentials} from '@src/connect/components/CustomApps/RegenerateSecret/RegenerateSecretNewCredentials';
import {renderWithProviders} from '../../../../../test-utils';

describe('RegenerateSecretNewCredentials', () => {
    it('renders the section subtitle', () => {
        renderWithProviders(
            <RegenerateSecretNewCredentials handleRedirect={jest.fn()} clientId='id-123' clientSecret='secret-abc' />
        );

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.new_credentials.subtitle'
            )
        ).toBeInTheDocument();
    });

    it('renders the modal title', () => {
        renderWithProviders(
            <RegenerateSecretNewCredentials handleRedirect={jest.fn()} clientId='id-123' clientSecret='secret-abc' />
        );

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.new_credentials.title'
            )
        ).toBeInTheDocument();
    });

    it('renders the client id value', () => {
        renderWithProviders(
            <RegenerateSecretNewCredentials handleRedirect={jest.fn()} clientId='id-123' clientSecret='secret-abc' />
        );

        expect(screen.getByText('id-123')).toBeInTheDocument();
    });

    it('renders the client secret value', () => {
        renderWithProviders(
            <RegenerateSecretNewCredentials handleRedirect={jest.fn()} clientId='id-123' clientSecret='secret-abc' />
        );

        expect(screen.getByText('secret-abc')).toBeInTheDocument();
    });

    it('renders an empty string when clientSecret is null', () => {
        const {container} = renderWithProviders(
            <RegenerateSecretNewCredentials handleRedirect={jest.fn()} clientId='id-123' clientSecret={null} />
        );

        expect(container.textContent).toContain('id-123');
    });

    it('calls handleRedirect when the done button is clicked', () => {
        const handleRedirect = jest.fn();
        renderWithProviders(
            <RegenerateSecretNewCredentials
                handleRedirect={handleRedirect}
                clientId='id-123'
                clientSecret='secret-abc'
            />
        );

        fireEvent.click(
            screen.getByText(
                'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.new_credentials.done_button'
            )
        );

        expect(handleRedirect).toHaveBeenCalledTimes(1);
    });
});
