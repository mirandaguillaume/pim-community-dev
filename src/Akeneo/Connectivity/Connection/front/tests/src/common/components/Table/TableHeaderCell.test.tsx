import React from 'react';
import '@testing-library/jest-dom';
import {TableHeaderCell} from '@src/common/components/Table';
import {renderWithProviders} from '../../../../test-utils';

const wrap = (children: React.ReactNode) => (
    <table>
        <thead>
            <tr>{children}</tr>
        </thead>
    </table>
);

describe('TableHeaderCell', () => {
    it('renders a th element', () => {
        const {container} = renderWithProviders(wrap(<TableHeaderCell>Name</TableHeaderCell>));

        expect(container.querySelector('th')).toBeInTheDocument();
    });

    it('renders children inside the header cell', () => {
        const {container} = renderWithProviders(wrap(<TableHeaderCell>Name</TableHeaderCell>));

        expect(container.querySelector('th')).toHaveTextContent('Name');
    });
});
