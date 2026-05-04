import React from 'react';
import '@testing-library/jest-dom';
import {Table} from '@src/common/components/Table';
import {renderWithProviders} from '../../../../test-utils';

describe('Table', () => {
    it('renders a table element', () => {
        const {container} = renderWithProviders(<Table />);

        expect(container.querySelector('table')).toBeInTheDocument();
    });

    it('renders children inside the table', () => {
        const {container} = renderWithProviders(
            <Table>
                <tbody>
                    <tr>
                        <td>Cell</td>
                    </tr>
                </tbody>
            </Table>
        );

        expect(container.querySelector('td')).toHaveTextContent('Cell');
    });
});
