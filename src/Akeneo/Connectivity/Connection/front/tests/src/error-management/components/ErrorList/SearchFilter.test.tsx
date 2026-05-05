import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {SearchFilter} from '@src/error-management/components/ErrorList/SearchFilter';
import {renderWithProviders} from '../../../../test-utils';

describe('SearchFilter', () => {
    it('renders the result count translation key', () => {
        const {container} = renderWithProviders(<SearchFilter value='' onSearch={jest.fn()} resultCount={5} />);

        expect(container.textContent).toContain(
            'akeneo_connectivity.connection.error_management.connection_monitoring.search_filter.result_count'
        );
    });

    it('renders an input element', () => {
        renderWithProviders(<SearchFilter value='' onSearch={jest.fn()} resultCount={0} />);

        expect(screen.getByRole('textbox')).toBeInTheDocument();
    });

    it('renders the search input with the placeholder translation key', () => {
        renderWithProviders(<SearchFilter value='' onSearch={jest.fn()} resultCount={0} />);

        expect(
            screen.getByPlaceholderText(
                'akeneo_connectivity.connection.error_management.connection_monitoring.search_filter.placeholder'
            )
        ).toBeInTheDocument();
    });

    it('renders with a pre-filled value', () => {
        renderWithProviders(<SearchFilter value='my search' onSearch={jest.fn()} resultCount={3} />);

        expect(screen.getByDisplayValue('my search')).toBeInTheDocument();
    });
});
