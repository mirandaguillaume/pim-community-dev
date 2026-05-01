import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {FormInput} from '@src/common/components/form/FormInput';
import {renderWithProviders} from '../../../../test-utils';

describe('FormInput', () => {
    it('renders an input element', () => {
        renderWithProviders(<FormInput type='text' />);

        expect(screen.getByRole('textbox')).toBeInTheDocument();
    });

    it('has the AknTextField class', () => {
        renderWithProviders(<FormInput type='text' />);

        expect(screen.getByRole('textbox')).toHaveClass('AknTextField');
    });

    it('renders with the correct type', () => {
        renderWithProviders(<FormInput type='url' />);

        expect(screen.getByRole('textbox')).toHaveAttribute('type', 'url');
    });

    it('renders with the given id', () => {
        renderWithProviders(<FormInput type='text' id='my-input' />);

        expect(screen.getByRole('textbox')).toHaveAttribute('id', 'my-input');
    });

    it('renders with a placeholder', () => {
        renderWithProviders(<FormInput type='text' placeholder='Enter value' />);

        expect(screen.getByPlaceholderText('Enter value')).toBeInTheDocument();
    });
});
