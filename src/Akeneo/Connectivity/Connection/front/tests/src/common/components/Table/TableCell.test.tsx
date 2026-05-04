import React from 'react';
import '@testing-library/jest-dom';
import {TableCell} from '@src/common/components/Table';
import {renderWithProviders} from '../../../../test-utils';

const wrap = (children: React.ReactNode) => (
    <table>
        <tbody>
            <tr>{children}</tr>
        </tbody>
    </table>
);

describe('TableCell', () => {
    it('renders a td element', () => {
        const {container} = renderWithProviders(wrap(<TableCell>Value</TableCell>));

        expect(container.querySelector('td')).toBeInTheDocument();
    });

    it('renders children inside the cell', () => {
        const {container} = renderWithProviders(wrap(<TableCell>Content</TableCell>));

        expect(container.querySelector('td')).toHaveTextContent('Content');
    });
});
