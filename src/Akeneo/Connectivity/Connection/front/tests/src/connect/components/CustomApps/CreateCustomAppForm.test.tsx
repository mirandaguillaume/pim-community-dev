import React from 'react';
import '@testing-library/jest-dom';
import {screen, fireEvent} from '@testing-library/react';
import {CreateCustomAppForm} from '@src/connect/components/CustomApps/CreateCustomAppForm';
import {renderWithProviders} from '../../../../test-utils';

jest.mock('@src/connect/hooks/use-create-custom-app', () => ({
    useCreateCustomApp: () => ({mutate: jest.fn(), isLoading: false, error: null}),
}));

describe('CreateCustomAppForm', () => {
    it('renders the title', () => {
        renderWithProviders(<CreateCustomAppForm onCancel={jest.fn()} setCredentials={jest.fn()} />);

        expect(
            screen.getByText('akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.title')
        ).toBeInTheDocument();
    });

    it('renders the name field label', () => {
        renderWithProviders(<CreateCustomAppForm onCancel={jest.fn()} setCredentials={jest.fn()} />);

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.fields.name'
            )
        ).toBeInTheDocument();
    });

    it('renders the activate_url field label', () => {
        renderWithProviders(<CreateCustomAppForm onCancel={jest.fn()} setCredentials={jest.fn()} />);

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.fields.activate_url'
            )
        ).toBeInTheDocument();
    });

    it('renders the cancel button', () => {
        renderWithProviders(<CreateCustomAppForm onCancel={jest.fn()} setCredentials={jest.fn()} />);

        expect(screen.getByText('pim_common.cancel')).toBeInTheDocument();
    });

    it('renders the create button', () => {
        renderWithProviders(<CreateCustomAppForm onCancel={jest.fn()} setCredentials={jest.fn()} />);

        expect(screen.getByText('pim_common.create')).toBeInTheDocument();
    });

    it('calls onCancel when the cancel button is clicked', () => {
        const onCancel = jest.fn();
        renderWithProviders(<CreateCustomAppForm onCancel={onCancel} setCredentials={jest.fn()} />);

        fireEvent.click(screen.getByText('pim_common.cancel'));

        expect(onCancel).toHaveBeenCalledTimes(1);
    });
});
