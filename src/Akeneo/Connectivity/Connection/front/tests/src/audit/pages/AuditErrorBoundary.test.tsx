import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {AuditErrorBoundary} from '@src/audit/pages/AuditErrorBoundary';
import {renderWithProviders} from '../../../test-utils';

const BrokenComponent = (): JSX.Element => {
    throw new Error('Test error');
};

describe('AuditErrorBoundary', () => {
    it('renders children when there is no error', () => {
        renderWithProviders(
            <AuditErrorBoundary>
                <div>Audit content</div>
            </AuditErrorBoundary>
        );

        expect(screen.getByText('Audit content')).toBeInTheDocument();
    });

    it('renders the RuntimeError when a child throws', () => {
        const consoleSpy = jest.spyOn(console, 'error').mockImplementation(() => {});

        renderWithProviders(
            <AuditErrorBoundary>
                <BrokenComponent />
            </AuditErrorBoundary>
        );

        expect(screen.getByText('akeneo_connectivity.connection.runtime_error.error_message')).toBeInTheDocument();

        consoleSpy.mockRestore();
    });
});
