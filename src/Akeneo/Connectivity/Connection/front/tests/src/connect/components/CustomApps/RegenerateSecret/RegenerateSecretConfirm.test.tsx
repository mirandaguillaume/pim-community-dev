import React from 'react';
import '@testing-library/jest-dom';
import {screen, fireEvent} from '@testing-library/react';
import {RegenerateSecretConfirm} from '@src/connect/components/CustomApps/RegenerateSecret/RegenerateSecretConfirm';
import {renderWithProviders} from '../../../../../test-utils';

describe('RegenerateSecretConfirm', () => {
    it('renders the modal title', () => {
        renderWithProviders(
            <RegenerateSecretConfirm handleRedirect={jest.fn()} handleRegenerate={jest.fn()} buttonDisabled={false} />
        );

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.confirm.title'
            )
        ).toBeInTheDocument();
    });

    it('renders the description', () => {
        renderWithProviders(
            <RegenerateSecretConfirm handleRedirect={jest.fn()} handleRegenerate={jest.fn()} buttonDisabled={false} />
        );

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.confirm.description'
            )
        ).toBeInTheDocument();
    });

    it('renders the cancel button', () => {
        renderWithProviders(
            <RegenerateSecretConfirm handleRedirect={jest.fn()} handleRegenerate={jest.fn()} buttonDisabled={false} />
        );

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.confirm.cancel_button'
            )
        ).toBeInTheDocument();
    });

    it('renders the regenerate button enabled when buttonDisabled is false', () => {
        renderWithProviders(
            <RegenerateSecretConfirm handleRedirect={jest.fn()} handleRegenerate={jest.fn()} buttonDisabled={false} />
        );

        const regenerateBtn = screen.getByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.confirm.regenerate_button'
        );
        expect(regenerateBtn.closest('button')).not.toBeDisabled();
    });

    it('renders the regenerate button disabled when buttonDisabled is true', () => {
        renderWithProviders(
            <RegenerateSecretConfirm handleRedirect={jest.fn()} handleRegenerate={jest.fn()} buttonDisabled={true} />
        );

        const regenerateBtn = screen.getByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.confirm.regenerate_button'
        );
        expect(regenerateBtn.closest('button')).toBeDisabled();
    });

    it('calls handleRedirect when cancel is clicked', () => {
        const handleRedirect = jest.fn();
        renderWithProviders(
            <RegenerateSecretConfirm
                handleRedirect={handleRedirect}
                handleRegenerate={jest.fn()}
                buttonDisabled={false}
            />
        );

        fireEvent.click(
            screen.getByText(
                'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.confirm.cancel_button'
            )
        );

        expect(handleRedirect).toHaveBeenCalledTimes(1);
    });

    it('calls handleRegenerate when regenerate is clicked', () => {
        const handleRegenerate = jest.fn();
        renderWithProviders(
            <RegenerateSecretConfirm
                handleRedirect={jest.fn()}
                handleRegenerate={handleRegenerate}
                buttonDisabled={false}
            />
        );

        fireEvent.click(
            screen.getByText(
                'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.confirm.regenerate_button'
            )
        );

        expect(handleRegenerate).toHaveBeenCalledTimes(1);
    });
});
