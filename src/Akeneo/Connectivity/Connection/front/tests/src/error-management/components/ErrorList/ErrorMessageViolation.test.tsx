import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {ErrorMessageViolation} from '@src/error-management/components/ErrorList/ErrorMessageViolation';
import {ErrorMessageViolationType} from '@src/error-management/model/ConnectionError';
import {renderWithProviders} from '../../../../test-utils';

describe('ErrorMessageViolation', () => {
    it('renders the message when there is no template', () => {
        renderWithProviders(
            <ErrorMessageViolation content={{message: 'Value is required.', type: ErrorMessageViolationType}} />
        );

        expect(screen.getByText('Value is required.')).toBeInTheDocument();
    });

    it('renders the plain message when the template does not contain the parameter key', () => {
        renderWithProviders(
            <ErrorMessageViolation
                content={{
                    message: 'Fallback message.',
                    message_template: 'No placeholders here.',
                    message_parameters: {attr: 'name'},
                    type: ErrorMessageViolationType,
                }}
            />
        );

        expect(screen.getByText('Fallback message.')).toBeInTheDocument();
    });

    it('renders the template with colored parameters when the template contains the key', () => {
        const {container} = renderWithProviders(
            <ErrorMessageViolation
                content={{
                    message: 'Fallback.',
                    message_template: 'Attribute name is invalid.',
                    message_parameters: {name: 'sku'},
                    type: ErrorMessageViolationType,
                }}
            />
        );

        expect(container.textContent).toContain('Attribute');
        expect(container.textContent).toContain('sku');
        expect(container.textContent).toContain('is invalid.');
    });

    it('renders extra fields in the unformatted list when there is no documentation', () => {
        renderWithProviders(
            <ErrorMessageViolation
                content={
                    {
                        message: 'Error!',
                        type: ErrorMessageViolationType,
                        extraField: 'extraValue',
                    } as any
                }
            />
        );

        expect(screen.getByText('extraField:')).toBeInTheDocument();
    });

    it('does not render the unformatted list when documentation is defined', () => {
        const {container} = renderWithProviders(
            <ErrorMessageViolation
                content={
                    {
                        message: 'Error!',
                        type: ErrorMessageViolationType,
                        documentation: [],
                        extraField: 'extraValue',
                    } as any
                }
            />
        );

        expect(container.querySelector('table')).not.toBeInTheDocument();
    });
});
