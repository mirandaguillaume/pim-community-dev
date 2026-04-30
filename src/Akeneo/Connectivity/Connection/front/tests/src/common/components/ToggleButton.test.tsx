import React from 'react';
import '@testing-library/jest-dom';
import {screen, fireEvent} from '@testing-library/react';
import {ToggleButton} from '@src/common/components/ToggleButton';
import {renderWithProviders} from '../../../test-utils';

describe('ToggleButton', () => {
    it('renders a checkbox input', () => {
        renderWithProviders(<ToggleButton name='my-toggle' />);

        expect(screen.getByRole('checkbox')).toBeInTheDocument();
    });

    it('starts unchecked by default', () => {
        renderWithProviders(<ToggleButton name='my-toggle' />);

        expect(screen.getByRole('checkbox')).not.toBeChecked();
    });

    it('starts checked when defaultChecked is true', () => {
        renderWithProviders(<ToggleButton name='my-toggle' defaultChecked />);

        expect(screen.getByRole('checkbox')).toBeChecked();
    });

    it('toggles state when clicked', () => {
        renderWithProviders(<ToggleButton name='my-toggle' />);

        const checkbox = screen.getByRole('checkbox');
        fireEvent.click(checkbox);

        expect(checkbox).toBeChecked();
    });
});
