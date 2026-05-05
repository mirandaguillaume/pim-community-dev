import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {ErrorMessageCell} from '@src/error-management/components/ErrorList/ErrorMessageCell';
import {
    DocumentationStyleText,
    ErrorMessageDomainType,
    ErrorMessageViolationType,
} from '@src/error-management/model/ConnectionError';
import {renderWithProviders} from '../../../../test-utils';

const wrap = (cell: React.ReactElement) => (
    <table>
        <tbody>
            <tr>{cell}</tr>
        </tbody>
    </table>
);

describe('ErrorMessageCell', () => {
    it('renders the domain message for a domain-type error', () => {
        renderWithProviders(
            wrap(<ErrorMessageCell content={{message: 'Domain error.', type: ErrorMessageDomainType}} />)
        );

        expect(screen.getByText('Domain error.')).toBeInTheDocument();
    });

    it('renders the violation message for a violation-type error', () => {
        renderWithProviders(
            wrap(<ErrorMessageCell content={{message: 'Violation error.', type: ErrorMessageViolationType}} />)
        );

        expect(screen.getByText('Violation error.')).toBeInTheDocument();
    });

    it('renders product information when product is defined', () => {
        renderWithProviders(
            wrap(
                <ErrorMessageCell
                    content={{
                        message: 'Error.',
                        type: ErrorMessageDomainType,
                        product: {id: 1, identifier: 'sku-1', family: null, label: 'My Product'},
                    }}
                />
            )
        );

        expect(screen.getByText(/My Product/)).toBeInTheDocument();
    });

    it('renders the documentation list when documentation is provided', () => {
        renderWithProviders(
            wrap(
                <ErrorMessageCell
                    content={{
                        message: 'Error.',
                        type: ErrorMessageDomainType,
                        documentation: [{message: 'Check the docs.', parameters: {}, style: DocumentationStyleText}],
                    }}
                />
            )
        );

        expect(screen.getByText('Check the docs.')).toBeInTheDocument();
    });
});
