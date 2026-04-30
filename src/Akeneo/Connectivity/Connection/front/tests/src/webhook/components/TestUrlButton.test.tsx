import React from 'react';
import '@testing-library/jest-dom';
import {screen, fireEvent} from '@testing-library/react';
import {TestUrlButton} from '@src/webhook/components/TestUrlButton';
import {renderWithProviders} from '../../../test-utils';

describe('TestUrlButton', () => {
    it('renders the button text', () => {
        renderWithProviders(<TestUrlButton onClick={jest.fn()} disabled={false} loading={false} />);

        expect(screen.getByText('akeneo_connectivity.connection.webhook.form.test')).toBeInTheDocument();
    });

    it('is disabled when the disabled prop is true', () => {
        renderWithProviders(<TestUrlButton onClick={jest.fn()} disabled={true} loading={false} />);

        expect(screen.getByRole('button')).toBeDisabled();
    });

    it('is disabled while loading', () => {
        renderWithProviders(<TestUrlButton onClick={jest.fn()} disabled={false} loading={true} />);

        expect(screen.getByRole('button')).toBeDisabled();
    });

    it('calls onClick when the button is enabled and clicked', () => {
        const onClick = jest.fn();
        renderWithProviders(<TestUrlButton onClick={onClick} disabled={false} loading={false} />);

        fireEvent.click(screen.getByRole('button'));

        expect(onClick).toHaveBeenCalledTimes(1);
    });
});
