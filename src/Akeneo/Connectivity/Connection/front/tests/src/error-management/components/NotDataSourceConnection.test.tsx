import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {NotDataSourceConnection} from '@src/error-management/components/NotDataSourceConnection';
import {renderWithProviders} from '../../../test-utils';

describe('NotDataSourceConnection', () => {
    it('renders the not-data-source title translation key', () => {
        renderWithProviders(<NotDataSourceConnection />);

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.error_management.connection_monitoring.not_data_source.title'
            )
        ).toBeInTheDocument();
    });
});
