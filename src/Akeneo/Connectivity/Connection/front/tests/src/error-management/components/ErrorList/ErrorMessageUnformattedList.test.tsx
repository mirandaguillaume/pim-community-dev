import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {ErrorMessageUnformattedList} from '@src/error-management/components/ErrorList/ErrorMessageUnformattedList';
import {ErrorMessageDomainType} from '@src/error-management/model/ConnectionError';
import {renderWithProviders} from '../../../../test-utils';

describe('ErrorMessageUnformattedList', () => {
    it('renders nothing when content has only hidden fields', () => {
        const {container} = renderWithProviders(
            <ErrorMessageUnformattedList content={{message: 'Error!', type: ErrorMessageDomainType}} />
        );

        expect(container.textContent).toBe('');
    });

    it('renders a table row for each non-hidden field', () => {
        renderWithProviders(
            <ErrorMessageUnformattedList
                content={{message: 'Error!', type: ErrorMessageDomainType, attribute: 'sku'} as any}
            />
        );

        expect(screen.getByText('attribute:')).toBeInTheDocument();
        expect(screen.getByText('"sku"')).toBeInTheDocument();
    });

    it('renders multiple extra fields as separate rows', () => {
        renderWithProviders(
            <ErrorMessageUnformattedList
                content={
                    {
                        message: 'Error!',
                        type: ErrorMessageDomainType,
                        field1: 'val1',
                        field2: 42,
                    } as any
                }
            />
        );

        expect(screen.getByText('field1:')).toBeInTheDocument();
        expect(screen.getByText('field2:')).toBeInTheDocument();
    });
});
