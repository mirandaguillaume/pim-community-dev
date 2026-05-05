import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {ErrorBoundary} from '@src/error-management/pages/ErrorBoundary';
import {NotFoundError} from '@src/shared/fetch';
import {renderWithProviders} from '../../../test-utils';

const ThrowError = ({error}: {error: Error}): JSX.Element => {
    throw error;
};

describe('ErrorBoundary', () => {
    it('renders children when there is no error', () => {
        renderWithProviders(
            <ErrorBoundary>
                <div>Page content</div>
            </ErrorBoundary>
        );

        expect(screen.getByText('Page content')).toBeInTheDocument();
    });

    it('renders RuntimeError when a generic error is thrown', () => {
        const consoleSpy = jest.spyOn(console, 'error').mockImplementation(() => {});

        renderWithProviders(
            <ErrorBoundary>
                <ThrowError error={new Error('Generic failure')} />
            </ErrorBoundary>
        );

        expect(screen.getByText('akeneo_connectivity.connection.runtime_error.error_message')).toBeInTheDocument();

        consoleSpy.mockRestore();
    });

    it('renders RuntimeError when a NotFoundError is thrown', () => {
        const consoleSpy = jest.spyOn(console, 'error').mockImplementation(() => {});

        renderWithProviders(
            <ErrorBoundary>
                <ThrowError error={new NotFoundError()} />
            </ErrorBoundary>
        );

        expect(screen.getByText('akeneo_connectivity.connection.runtime_error.error_message')).toBeInTheDocument();

        consoleSpy.mockRestore();
    });
});
