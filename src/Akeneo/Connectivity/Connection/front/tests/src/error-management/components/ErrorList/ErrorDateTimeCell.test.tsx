import React from 'react';
import '@testing-library/jest-dom';
import {ErrorDateTimeCell} from '@src/error-management/components/ErrorList/ErrorDateTimeCell';
import {renderWithProviders} from '../../../../test-utils';

const TIMESTAMP = 1714857600000; // 2024-05-05 00:00:00 UTC

describe('ErrorDateTimeCell', () => {
    it('renders without crashing', () => {
        const {container} = renderWithProviders(
            <table>
                <tbody>
                    <tr>
                        <ErrorDateTimeCell timestamp={TIMESTAMP} />
                    </tr>
                </tbody>
            </table>
        );

        expect(container.querySelector('td')).toBeInTheDocument();
    });

    it('renders two date-time rows', () => {
        const {container} = renderWithProviders(
            <table>
                <tbody>
                    <tr>
                        <ErrorDateTimeCell timestamp={TIMESTAMP} />
                    </tr>
                </tbody>
            </table>
        );

        const rows = container.querySelectorAll('td > div');
        expect(rows.length).toBe(2);
    });

    it('renders two SVG icons', () => {
        const {container} = renderWithProviders(
            <table>
                <tbody>
                    <tr>
                        <ErrorDateTimeCell timestamp={TIMESTAMP} />
                    </tr>
                </tbody>
            </table>
        );

        expect(container.querySelectorAll('svg').length).toBe(2);
    });
});
