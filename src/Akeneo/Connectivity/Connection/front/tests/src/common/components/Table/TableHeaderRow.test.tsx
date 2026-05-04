import React from 'react';
import '@testing-library/jest-dom';
import {TableHeaderRow} from '@src/common/components/Table';
import {renderWithProviders} from '../../../../test-utils';

const wrap = (children: React.ReactNode) => (
    <table>
        <thead>{children}</thead>
    </table>
);

describe('TableHeaderRow', () => {
    it('renders a tr element', () => {
        const {container} = renderWithProviders(wrap(<TableHeaderRow />));

        expect(container.querySelector('tr')).toBeInTheDocument();
    });

    it('renders children inside the header row', () => {
        const {container} = renderWithProviders(
            wrap(
                <TableHeaderRow>
                    <th>Name</th>
                </TableHeaderRow>
            )
        );

        expect(container.querySelector('th')).toHaveTextContent('Name');
    });
});
