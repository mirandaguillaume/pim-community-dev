import React from 'react';
import '@testing-library/jest-dom';
import {TableRow} from '@src/common/components/Table';
import {renderWithProviders} from '../../../../test-utils';

const wrap = (children: React.ReactNode) => (
    <table>
        <tbody>{children}</tbody>
    </table>
);

describe('TableRow', () => {
    it('renders a tr element', () => {
        const {container} = renderWithProviders(wrap(<TableRow />));

        expect(container.querySelector('tr')).toBeInTheDocument();
    });

    it('renders children inside the row', () => {
        const {container} = renderWithProviders(
            wrap(
                <TableRow>
                    <td>Cell</td>
                </TableRow>
            )
        );

        expect(container.querySelector('td')).toHaveTextContent('Cell');
    });
});
