import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {NotDataSourceConnectedApp} from '@src/connect/components/ConnectedApp/ErrorMonitoring/NotDataSourceConnectedApp';
import {renderWithProviders} from '../../../../../test-utils';

describe('NotDataSourceConnectedApp', () => {
    it('renders the not_data_source heading', () => {
        renderWithProviders(<NotDataSourceConnectedApp />);

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.connect.connected_apps.edit.error_monitoring.not_data_source'
            )
        ).toBeInTheDocument();
    });

    it('renders without crashing', () => {
        const {container} = renderWithProviders(<NotDataSourceConnectedApp />);

        expect(container).toBeInTheDocument();
    });
});
