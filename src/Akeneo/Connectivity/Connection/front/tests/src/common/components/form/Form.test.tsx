import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {Form} from '@src/common/components/form/Form';
import {renderWithProviders} from '../../../../test-utils';

describe('Form', () => {
    it('renders a form element', () => {
        const {container} = renderWithProviders(<Form />);

        expect(container.querySelector('form')).toBeInTheDocument();
    });

    it('has the AknFormContainer class', () => {
        const {container} = renderWithProviders(<Form />);

        expect(container.querySelector('form')).toHaveClass('AknFormContainer');
    });

    it('renders children inside the form', () => {
        renderWithProviders(
            <Form>
                <input type='text' placeholder='Name' />
            </Form>
        );

        expect(screen.getByPlaceholderText('Name')).toBeInTheDocument();
    });

    it('passes onSubmit through to the form element', () => {
        const onSubmit = jest.fn(e => e.preventDefault());
        const {container} = renderWithProviders(<Form onSubmit={onSubmit} />);

        container.querySelector('form')!.dispatchEvent(new Event('submit', {bubbles: true}));

        expect(onSubmit).toHaveBeenCalledTimes(1);
    });
});
