import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {FormLabel} from '@src/common/components/form/FormLabel';
import {renderWithProviders} from '../../../../test-utils';

describe('FormLabel', () => {
    it('renders the label translation key', () => {
        renderWithProviders(<FormLabel label='pim_common.label' />);

        expect(screen.getByText('pim_common.label')).toBeInTheDocument();
    });

    it('renders the htmlFor attribute when id is provided', () => {
        renderWithProviders(<FormLabel label='pim_common.label' id='my-input' />);

        expect(screen.getByText('pim_common.label').closest('label')).toHaveAttribute('for', 'my-input');
    });

    it('renders the required label when required is true', () => {
        renderWithProviders(<FormLabel label='pim_common.label' required />);

        expect(screen.getByText('pim_common.required_label')).toBeInTheDocument();
    });

    it('does not render the required label when required is false', () => {
        renderWithProviders(<FormLabel label='pim_common.label' required={false} />);

        expect(screen.queryByText('pim_common.required_label')).not.toBeInTheDocument();
    });
});
