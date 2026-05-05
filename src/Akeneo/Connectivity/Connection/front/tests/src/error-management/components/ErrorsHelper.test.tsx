import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {ErrorsHelper} from '@src/error-management/components/ErrorsHelper';
import {renderWithProviders} from '../../../test-utils';

describe('ErrorsHelper', () => {
    it('renders the description translation key', () => {
        renderWithProviders(
            <ErrorsHelper
                errorCount={5}
                description='akeneo_connectivity.connection.error_management.connection_monitoring.helper.description'
            />
        );

        expect(
            screen.getByText('akeneo_connectivity.connection.error_management.connection_monitoring.helper.description')
        ).toBeInTheDocument();
    });

    it('renders the documentation link', () => {
        renderWithProviders(<ErrorsHelper errorCount={0} description='some.description.key' />);

        expect(
            screen.getByRole('link', {
                name: 'akeneo_connectivity.connection.error_management.connection_monitoring.helper.link',
            })
        ).toBeInTheDocument();
    });

    it('renders the link with the API documentation href', () => {
        renderWithProviders(<ErrorsHelper errorCount={0} description='some.description.key' />);

        expect(screen.getByRole('link')).toHaveAttribute(
            'href',
            'https://api.akeneo.com/documentation/responses.html#422-error'
        );
    });

    it('renders the illustration container', () => {
        const {container} = renderWithProviders(<ErrorsHelper errorCount={0} description='some.description.key' />);

        expect(container.querySelector('.AknDescriptionHeader-icon')).toBeInTheDocument();
    });
});
