import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {ErrorMessageDomain} from '@src/error-management/components/ErrorList/ErrorMessageDomain';
import {ErrorMessageDomainType} from '@src/error-management/model/ConnectionError';
import {renderWithProviders} from '../../../../test-utils';

describe('ErrorMessageDomain', () => {
    it('renders the message when there is no message_template', () => {
        renderWithProviders(
            <ErrorMessageDomain content={{message: 'An error occurred.', type: ErrorMessageDomainType}} />
        );

        expect(screen.getByText('An error occurred.')).toBeInTheDocument();
    });

    it('renders the message_template when both template and parameters are defined', () => {
        renderWithProviders(
            <ErrorMessageDomain
                content={{
                    message: 'ignored',
                    message_template: 'Invalid value.',
                    message_parameters: {},
                    type: ErrorMessageDomainType,
                }}
            />
        );

        expect(screen.getByText('Invalid value.')).toBeInTheDocument();
    });

    it('renders colored parameter from template', () => {
        const {container} = renderWithProviders(
            <ErrorMessageDomain
                content={{
                    message: 'ignored',
                    message_template: 'Attribute {attr} is invalid.',
                    message_parameters: {attr: 'name'},
                    type: ErrorMessageDomainType,
                }}
            />
        );

        expect(container.textContent).toContain('Attribute');
        expect(container.textContent).toContain('name');
        expect(container.textContent).toContain('is invalid.');
    });

    it('renders extra fields in unformatted list when there is no documentation', () => {
        renderWithProviders(
            <ErrorMessageDomain
                content={{message: 'Error!', type: ErrorMessageDomainType, extraField: 'extraValue'} as any}
            />
        );

        expect(screen.getByText('extraField:')).toBeInTheDocument();
    });

    it('does not render unformatted list when documentation is defined', () => {
        const {container} = renderWithProviders(
            <ErrorMessageDomain
                content={
                    {
                        message: 'Error!',
                        type: ErrorMessageDomainType,
                        documentation: [],
                        extraField: 'extraValue',
                    } as any
                }
            />
        );

        expect(container.querySelector('table')).not.toBeInTheDocument();
    });
});
