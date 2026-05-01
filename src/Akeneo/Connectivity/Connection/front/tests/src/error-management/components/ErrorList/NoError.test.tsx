import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {NoError} from '@src/error-management/components/ErrorList/NoError';
import {renderWithProviders} from '../../../../test-utils';

describe('NoError', () => {
    it('renders the no-error title translation key', () => {
        renderWithProviders(<NoError />);

        expect(
            screen.getByText('akeneo_connectivity.connection.error_management.connection_monitoring.no_error.title')
        ).toBeInTheDocument();
    });
});
