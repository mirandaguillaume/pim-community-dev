import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {SortButton} from '@src/error-management/components/ErrorList/SortButton';
import {renderWithProviders} from '../../../../test-utils';

describe('SortButton', () => {
    it('renders children', () => {
        renderWithProviders(
            <SortButton order='asc' onSort={jest.fn()}>
                Date
            </SortButton>
        );

        expect(screen.getByText('Date')).toBeInTheDocument();
    });

    it('calls onSort with desc when current order is asc', () => {
        const onSort = jest.fn();
        renderWithProviders(
            <SortButton order='asc' onSort={onSort}>
                Date
            </SortButton>
        );

        userEvent.click(screen.getByRole('button'));

        expect(onSort).toHaveBeenCalledWith('desc');
    });

    it('calls onSort with asc when current order is desc', () => {
        const onSort = jest.fn();
        renderWithProviders(
            <SortButton order='desc' onSort={onSort}>
                Date
            </SortButton>
        );

        userEvent.click(screen.getByRole('button'));

        expect(onSort).toHaveBeenCalledWith('asc');
    });
});
