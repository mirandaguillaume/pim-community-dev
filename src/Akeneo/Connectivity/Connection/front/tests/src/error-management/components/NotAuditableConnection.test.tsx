import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {NotAuditableConnection} from '@src/error-management/components/NotAuditableConnection';
import {renderWithProviders} from '../../../test-utils';

describe('NotAuditableConnection', () => {
    it('renders the not-auditable title translation key', () => {
        renderWithProviders(<NotAuditableConnection />);

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.error_management.connection_monitoring.not_auditable.title'
            )
        ).toBeInTheDocument();
    });

    it('renders the documentation link with correct attributes', () => {
        renderWithProviders(<NotAuditableConnection />);

        const link = screen.getByRole('link');
        expect(link).toHaveAttribute(
            'href',
            'https://help.akeneo.com/pim/serenity/articles/manage-your-connections.html#enable-the-tracking'
        );
        expect(link).toHaveAttribute('target', '_blank');
        expect(link).toHaveAttribute('rel', 'noopener noreferrer');
    });
});
