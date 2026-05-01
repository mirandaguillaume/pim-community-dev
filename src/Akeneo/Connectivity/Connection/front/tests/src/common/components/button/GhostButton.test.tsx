import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {GhostButton} from '@src/common/components/button/GhostButton';
import {renderWithProviders} from '../../../../test-utils';

describe('GhostButton', () => {
    it('renders as a button element', () => {
        renderWithProviders(<GhostButton>Cancel</GhostButton>);

        expect(screen.getByRole('button', {name: 'Cancel'})).toBeInTheDocument();
    });

    it('calls onClick when clicked', () => {
        const onClick = jest.fn();
        renderWithProviders(<GhostButton onClick={onClick}>Cancel</GhostButton>);

        userEvent.click(screen.getByRole('button'));

        expect(onClick).toHaveBeenCalledTimes(1);
    });

    it('is disabled when the disabled prop is set', () => {
        renderWithProviders(<GhostButton disabled>Cancel</GhostButton>);

        expect(screen.getByRole('button')).toBeDisabled();
    });
});
