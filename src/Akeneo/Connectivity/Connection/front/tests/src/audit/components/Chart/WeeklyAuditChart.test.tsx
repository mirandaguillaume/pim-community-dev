import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {WeeklyAuditChart} from '@src/audit/components/Chart/WeeklyAuditChart';
import {renderWithProviders} from '../../../../test-utils';

jest.mock('@src/audit/components/Chart/WeeklyChart', () => ({
    WeeklyChart: () => <div data-testid='weekly-chart' />,
}));

const weeklyAuditData = {
    daily: {
        '2024-01-01': 10,
        '2024-01-02': 20,
        '2024-01-03': 30,
        '2024-01-04': 40,
        '2024-01-05': 50,
        '2024-01-06': 60,
        '2024-01-07': 70,
    },
    weekly_total: 280,
};

describe('WeeklyAuditChart', () => {
    it('renders the title', () => {
        renderWithProviders(<WeeklyAuditChart title='My Chart Title' theme='blue' weeklyAuditData={weeklyAuditData} />);

        expect(screen.getByText('My Chart Title')).toBeInTheDocument();
    });

    it('renders the during_the_last_seven_days legend key', () => {
        const {container} = renderWithProviders(
            <WeeklyAuditChart title='Title' theme='red' weeklyAuditData={weeklyAuditData} />
        );

        expect(container.textContent).toContain(
            'akeneo_connectivity.connection.dashboard.charts.legend.during_the_last_seven_days'
        );
    });

    it('renders the weekly total count', () => {
        renderWithProviders(<WeeklyAuditChart title='Title' theme='green' weeklyAuditData={weeklyAuditData} />);

        expect(screen.getByText('280')).toBeInTheDocument();
    });

    it('renders the chart component', () => {
        renderWithProviders(<WeeklyAuditChart title='Title' theme='purple' weeklyAuditData={weeklyAuditData} />);

        expect(screen.getByTestId('weekly-chart')).toBeInTheDocument();
    });
});
