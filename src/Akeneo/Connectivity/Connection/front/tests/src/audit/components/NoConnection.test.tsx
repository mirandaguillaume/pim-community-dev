import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {NoConnection} from '@src/audit/components/NoConnection';
import {renderWithProviders} from '../../../test-utils';

describe('NoConnection', () => {
    it('renders the default flow type heading', () => {
        renderWithProviders(<NoConnection />);

        expect(
            screen.getByText('akeneo_connectivity.connection.dashboard.no_connection.title.default')
        ).toBeInTheDocument();
    });

    it('renders the data_source flow type heading', () => {
        renderWithProviders(<NoConnection flowType='data_source' />);

        expect(
            screen.getByText('akeneo_connectivity.connection.dashboard.no_connection.title.data_source')
        ).toBeInTheDocument();
    });

    it('renders the with-permission message when security grants access', () => {
        renderWithProviders(<NoConnection />);

        expect(
            screen.getByText('akeneo_connectivity.connection.dashboard.no_connection.message_with_permission.message')
        ).toBeInTheDocument();
    });

    it('renders the settings link', () => {
        renderWithProviders(<NoConnection />);

        expect(
            screen.getByText('akeneo_connectivity.connection.dashboard.no_connection.message_with_permission.link')
        ).toBeInTheDocument();
    });
});
