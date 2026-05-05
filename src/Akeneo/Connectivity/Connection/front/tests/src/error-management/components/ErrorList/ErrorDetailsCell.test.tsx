import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {ErrorDetailsCell} from '@src/error-management/components/ErrorList/ErrorDetailsCell';
import {ErrorMessageDomainType} from '@src/error-management/model/ConnectionError';
import {renderWithProviders} from '../../../../test-utils';

const wrap = (cell: React.ReactElement) => (
    <table>
        <tbody>
            <tr>{cell}</tr>
        </tbody>
    </table>
);

describe('ErrorDetailsCell', () => {
    it('renders the locale translation key when locale is set', () => {
        const {container} = renderWithProviders(
            wrap(<ErrorDetailsCell content={{message: 'Error.', type: ErrorMessageDomainType, locale: 'en_US'}} />)
        );

        expect(container.textContent).toContain(
            'akeneo_connectivity.connection.error_management.connection_monitoring.error_list.details_column.locale'
        );
    });

    it('renders the channel translation key when scope is set', () => {
        const {container} = renderWithProviders(
            wrap(<ErrorDetailsCell content={{message: 'Error.', type: ErrorMessageDomainType, scope: 'ecommerce'}} />)
        );

        expect(container.textContent).toContain(
            'akeneo_connectivity.connection.error_management.connection_monitoring.error_list.details_column.channel'
        );
        expect(container.textContent).toContain('ecommerce');
    });

    it('renders the family translation key when product family is set', () => {
        const {container} = renderWithProviders(
            wrap(
                <ErrorDetailsCell
                    content={{
                        message: 'Error.',
                        type: ErrorMessageDomainType,
                        product: {id: 1, identifier: 'sku', family: 'clothing', label: 'Product'},
                    }}
                />
            )
        );

        expect(container.textContent).toContain(
            'akeneo_connectivity.connection.error_management.connection_monitoring.error_list.details_column.family'
        );
        expect(container.textContent).toContain('clothing');
    });

    it('renders nothing when locale, scope, and family are all absent', () => {
        const {container} = renderWithProviders(
            wrap(<ErrorDetailsCell content={{message: 'Error.', type: ErrorMessageDomainType}} />)
        );

        expect(container.textContent).toBe('');
    });
});
