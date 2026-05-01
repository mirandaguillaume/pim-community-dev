import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {RuntimeError} from '@src/common/components/RuntimeError';
import {renderWithProviders} from '../../../test-utils';

describe('RuntimeError', () => {
    it('renders the error message translation key', () => {
        renderWithProviders(<RuntimeError />);

        expect(screen.getByText('akeneo_connectivity.connection.runtime_error.error_message')).toBeInTheDocument();
    });

    it('renders the reload helper translation key', () => {
        renderWithProviders(<RuntimeError />);

        expect(screen.getByText('akeneo_connectivity.connection.runtime_error.reload_helper')).toBeInTheDocument();
    });

    it('renders the reload button translation key', () => {
        renderWithProviders(<RuntimeError />);

        expect(screen.getByText('akeneo_connectivity.connection.runtime_error.reload_button')).toBeInTheDocument();
    });

    it('reloads the page when the button is clicked', () => {
        const reloadMock = jest.fn();
        Object.defineProperty(window, 'location', {
            value: {reload: reloadMock},
            writable: true,
        });

        renderWithProviders(<RuntimeError />);
        userEvent.click(screen.getByText('akeneo_connectivity.connection.runtime_error.reload_button'));

        expect(reloadMock).toHaveBeenCalledTimes(1);
    });
});
