import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {NotAuditableConnectedApp} from '@src/connect/components/ConnectedApp/ErrorMonitoring/NotAuditableConnectedApp';
import {renderWithProviders} from '../../../../../test-utils';

describe('NotAuditableConnectedApp', () => {
    it('renders the not_auditable title', () => {
        renderWithProviders(<NotAuditableConnectedApp />);

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.connect.connected_apps.edit.error_monitoring.not_auditable.title'
            )
        ).toBeInTheDocument();
    });

    it('renders the first description sentence', () => {
        renderWithProviders(<NotAuditableConnectedApp />);

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.connect.connected_apps.edit.error_monitoring.not_auditable.description.1'
            )
        ).toBeInTheDocument();
    });

    it('renders the help link text', () => {
        renderWithProviders(<NotAuditableConnectedApp />);

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.connect.connected_apps.edit.error_monitoring.not_auditable.description.2'
            )
        ).toBeInTheDocument();
    });

    it('renders the third description sentence', () => {
        renderWithProviders(<NotAuditableConnectedApp />);

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.connect.connected_apps.edit.error_monitoring.not_auditable.description.3'
            )
        ).toBeInTheDocument();
    });
});
