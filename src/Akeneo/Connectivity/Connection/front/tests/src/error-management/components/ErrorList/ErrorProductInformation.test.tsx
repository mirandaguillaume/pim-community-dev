import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {ErrorProductInformation} from '@src/error-management/components/ErrorList/ErrorProductInformation';
import {renderWithProviders} from '../../../../test-utils';

describe('ErrorProductInformation', () => {
    it('renders nothing when the product label is empty', () => {
        const {container} = renderWithProviders(
            <ErrorProductInformation product={{id: 1, identifier: 'my-sku', family: null, label: ''}} />
        );

        expect(container.textContent).toBe('');
    });

    it('renders the product label and identifier when label differs from identifier', () => {
        renderWithProviders(
            <ErrorProductInformation product={{id: 1, identifier: 'my-sku', family: null, label: 'My Product'}} />
        );

        expect(screen.getByText(/My Product/)).toBeInTheDocument();
        expect(screen.getByText(/my-sku/)).toBeInTheDocument();
    });

    it('renders the bracketed identifier when label equals identifier', () => {
        const {container} = renderWithProviders(
            <ErrorProductInformation product={{id: 1, identifier: 'my-sku', family: null, label: 'my-sku'}} />
        );

        expect(container.textContent).toContain('[my-sku]');
    });

    it('renders product name and identifier translation keys', () => {
        renderWithProviders(
            <ErrorProductInformation product={{id: 1, identifier: 'my-sku', family: null, label: 'My Product'}} />
        );

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.error_management.connection_monitoring.error_list.content_column.product_name'
            )
        ).toBeInTheDocument();
        expect(
            screen.getByText(
                'akeneo_connectivity.connection.error_management.connection_monitoring.error_list.content_column.with_id'
            )
        ).toBeInTheDocument();
    });
});
