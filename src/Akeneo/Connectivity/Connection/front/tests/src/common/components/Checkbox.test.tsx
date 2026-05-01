import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {Checkbox} from '@src/common/components/Checkbox';
import {renderWithProviders} from '../../../test-utils';

describe('Checkbox', () => {
    it('renders a checkbox input', () => {
        renderWithProviders(<Checkbox />);

        expect(screen.getByRole('checkbox')).toBeInTheDocument();
    });

    it('renders children as label text', () => {
        renderWithProviders(<Checkbox>Accept terms</Checkbox>);

        expect(screen.getByText('Accept terms')).toBeInTheDocument();
    });

    it('renders as checked when defaultChecked is true', () => {
        renderWithProviders(<Checkbox defaultChecked />);

        expect(screen.getByRole('checkbox')).toBeChecked();
    });

    it('is disabled when the disabled prop is set', () => {
        renderWithProviders(<Checkbox disabled />);

        expect(screen.getByRole('checkbox')).toBeDisabled();
    });
});
