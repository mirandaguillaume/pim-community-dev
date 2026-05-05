import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {FormGroup} from '@src/common/components/form/FormGroup';
import {FormInput} from '@src/common/components/form/FormInput';
import {renderWithProviders} from '../../../../test-utils';

describe('FormGroup', () => {
    it('renders the input control', () => {
        renderWithProviders(
            <FormGroup>
                <FormInput type='text' />
            </FormGroup>
        );

        expect(screen.getByRole('textbox')).toBeInTheDocument();
    });

    it('renders the label when provided', () => {
        renderWithProviders(
            <FormGroup label='pim_common.name' controlId='name-field'>
                <FormInput type='text' />
            </FormGroup>
        );

        expect(screen.getByText('pim_common.name')).toBeInTheDocument();
    });

    it('does not render a label when label prop is absent', () => {
        const {container} = renderWithProviders(
            <FormGroup>
                <FormInput type='text' />
            </FormGroup>
        );

        expect(container.querySelector('label')).not.toBeInTheDocument();
    });

    it('renders helper messages when provided', () => {
        renderWithProviders(
            <FormGroup helpers={[<span key='h'>Invalid value</span>]}>
                <FormInput type='text' />
            </FormGroup>
        );

        expect(screen.getByText('Invalid value')).toBeInTheDocument();
    });
});
