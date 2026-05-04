import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {SearchInput} from '@src/error-management/components/ErrorList/SearchInput';
import {renderWithProviders} from '../../../../test-utils';

describe('SearchInput', () => {
    beforeEach(() => jest.useFakeTimers());
    afterEach(() => jest.useRealTimers());

    it('renders an input with the initial value', () => {
        renderWithProviders(<SearchInput value='foo' onSearch={jest.fn()} placeholder='Search' />);

        expect(screen.getByRole('textbox')).toHaveValue('foo');
    });

    it('renders with the given placeholder', () => {
        renderWithProviders(<SearchInput value='' onSearch={jest.fn()} placeholder='Filter errors' />);

        expect(screen.getByPlaceholderText('Filter errors')).toBeInTheDocument();
    });

    it('updates the input value when the user types', () => {
        renderWithProviders(<SearchInput value='' onSearch={jest.fn()} placeholder='Search' />);

        userEvent.type(screen.getByRole('textbox'), 'hello');

        expect(screen.getByRole('textbox')).toHaveValue('hello');
    });

    it('calls onSearch after the debounce delay', () => {
        const onSearch = jest.fn();
        renderWithProviders(<SearchInput value='' onSearch={onSearch} placeholder='Search' />);

        userEvent.type(screen.getByRole('textbox'), 'q');
        expect(onSearch).not.toHaveBeenCalled();

        jest.advanceTimersByTime(300);

        expect(onSearch).toHaveBeenCalledWith('q');
    });
});
