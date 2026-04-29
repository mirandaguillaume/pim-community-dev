import {renderHook} from '@testing-library/react';
import {useWeeklyErrorAudit} from '@src/audit/hooks/api/use-weekly-error-audit';
import {useQuery} from '@src/shared/fetch';

jest.mock('@src/shared/fetch', () => ({
    useQuery: jest.fn(),
}));

const mockUseQuery = useQuery as jest.Mock;

describe('useWeeklyErrorAudit', () => {
    beforeEach(() => {
        mockUseQuery.mockReset();
    });

    it('returns loading state with empty data while fetching', () => {
        mockUseQuery.mockReturnValue({loading: true, data: undefined});

        const {result} = renderHook(() => useWeeklyErrorAudit());

        expect(result.current.loading).toBe(true);
        expect(result.current.weeklyErrorAuditData).toEqual({});
    });

    it('merges previous_week and current_week into daily with current_week taking precedence', () => {
        mockUseQuery.mockReturnValue({
            loading: false,
            data: {
                magento: {
                    previous_week: {'2024-01-01': 3, '2024-01-02': 5},
                    current_week: {'2024-01-03': 7, '2024-01-04': 2},
                    current_week_total: 9,
                },
            },
        });

        const {result} = renderHook(() => useWeeklyErrorAudit());

        expect(result.current.loading).toBe(false);
        expect(result.current.weeklyErrorAuditData).toEqual({
            magento: {
                daily: {
                    '2024-01-01': 3,
                    '2024-01-02': 5,
                    '2024-01-03': 7,
                    '2024-01-04': 2,
                },
                weekly_total: 9,
            },
        });
    });

    it('current_week overwrites previous_week on date collision', () => {
        mockUseQuery.mockReturnValue({
            loading: false,
            data: {
                bynder: {
                    previous_week: {'2024-01-07': 10},
                    current_week: {'2024-01-07': 99},
                    current_week_total: 99,
                },
            },
        });

        const {result} = renderHook(() => useWeeklyErrorAudit());

        expect(result.current.weeklyErrorAuditData.bynder.daily['2024-01-07']).toBe(99);
    });

    it('handles multiple connections independently', () => {
        mockUseQuery.mockReturnValue({
            loading: false,
            data: {
                magento: {previous_week: {}, current_week: {'2024-01-01': 1}, current_week_total: 1},
                bynder: {previous_week: {}, current_week: {'2024-01-01': 2}, current_week_total: 2},
            },
        });

        const {result} = renderHook(() => useWeeklyErrorAudit());

        expect(Object.keys(result.current.weeklyErrorAuditData)).toEqual(['magento', 'bynder']);
        expect(result.current.weeklyErrorAuditData.magento.weekly_total).toBe(1);
        expect(result.current.weeklyErrorAuditData.bynder.weekly_total).toBe(2);
    });
});
