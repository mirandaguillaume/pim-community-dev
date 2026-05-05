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

    it('renders all three description parts', () => {
        const {container} = renderWithProviders(<NotAuditableConnectedApp />);

        expect(container.textContent).toContain(
            'akeneo_connectivity.connection.connect.connected_apps.edit.error_monitoring.not_auditable.description.1'
        );
        expect(container.textContent).toContain(
            'akeneo_connectivity.connection.connect.connected_apps.edit.error_monitoring.not_auditable.description.2'
        );
        expect(container.textContent).toContain(
            'akeneo_connectivity.connection.connect.connected_apps.edit.error_monitoring.not_auditable.description.3'
        );
    });

    it('renders the help link pointing to the documentation', () => {
        renderWithProviders(<NotAuditableConnectedApp />);

        const link = screen.getByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.error_monitoring.not_auditable.description.2'
        );
        expect(link.closest('a')).toHaveAttribute('target', '_blank');
    });
});
