import React from 'react';
import '@testing-library/jest-dom';
import {screen, fireEvent} from '@testing-library/react';
import SearchInput from '@src/webhook/components/SearchInput';
import {renderWithProviders} from '../../../test-utils';

describe('SearchInput', () => {
    it('renders the input with the given value', () => {
        renderWithProviders(<SearchInput value='hello' onSearch={jest.fn()} placeholder='Search...' />);

        expect((screen.getByTestId('event-logs-list-search-text-filter') as HTMLInputElement).value).toBe('hello');
    });

    it('calls onSearch with the typed value on input change', () => {
        const onSearch = jest.fn();
        renderWithProviders(<SearchInput value='' onSearch={onSearch} placeholder='Search...' />);

        fireEvent.change(screen.getByTestId('event-logs-list-search-text-filter'), {
            target: {value: 'akeneo'},
        });

        expect(onSearch).toHaveBeenCalledWith('akeneo');
    });

    it('renders with the given placeholder', () => {
        renderWithProviders(<SearchInput value='' onSearch={jest.fn()} placeholder='Filter events...' />);

        expect(screen.getByPlaceholderText('Filter events...')).toBeInTheDocument();
    });
});
