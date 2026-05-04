import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {IconButton} from '@src/common/components/button/IconButton';
import {renderWithProviders} from '../../../../test-utils';

describe('IconButton', () => {
    it('renders as a button element', () => {
        renderWithProviders(<IconButton aria-label='Close' />);

        expect(screen.getByRole('button', {name: 'Close'})).toBeInTheDocument();
    });

    it('renders children', () => {
        renderWithProviders(<IconButton>X</IconButton>);

        expect(screen.getByRole('button')).toHaveTextContent('X');
    });

    it('calls onClick when clicked', () => {
        const onClick = jest.fn();
        renderWithProviders(<IconButton onClick={onClick} aria-label='Action' />);

        userEvent.click(screen.getByRole('button'));

        expect(onClick).toHaveBeenCalledTimes(1);
    });

    it('is disabled when the disabled prop is set', () => {
        renderWithProviders(<IconButton disabled aria-label='Disabled' />);

        expect(screen.getByRole('button')).toBeDisabled();
    });
});
