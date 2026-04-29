import {renderHook} from '@testing-library/react';
import {useBusinessErrorCountPerConnection} from '@src/audit/hooks/api/use-business-error-count-per-connection';
import {useQuery} from '@src/shared/fetch';

jest.mock('@src/shared/fetch', () => ({
    useQuery: jest.fn(),
}));

const mockUseQuery = useQuery as jest.Mock;

describe('useBusinessErrorCountPerConnection', () => {
    beforeEach(() => {
        mockUseQuery.mockReset();
    });

    it('returns loading state while fetching', () => {
        mockUseQuery.mockReturnValue({loading: true, data: undefined});

        const {result} = renderHook(() => useBusinessErrorCountPerConnection());

        expect(result.current.loading).toBe(true);
        expect(result.current.errorCountPerConnection).toEqual([]);
    });

    it('queries the business error count endpoint', () => {
        mockUseQuery.mockReturnValue({loading: false, data: {}});

        renderHook(() => useBusinessErrorCountPerConnection());

        expect(mockUseQuery).toHaveBeenCalledWith(
            'akeneo_connectivity_connection_audit_rest_error_count_per_connection',
            {error_type: 'business'}
        );
    });

    it('returns connections sorted by error count descending', () => {
        mockUseQuery.mockReturnValue({
            loading: false,
            data: {magento: 5, bynder: 20, akeneo: 1},
        });

        const {result} = renderHook(() => useBusinessErrorCountPerConnection());

        expect(result.current.loading).toBe(false);
        expect(result.current.errorCountPerConnection).toEqual([
            {connectionCode: 'bynder', errorCount: 20},
            {connectionCode: 'magento', errorCount: 5},
            {connectionCode: 'akeneo', errorCount: 1},
        ]);
    });

    it('returns empty array when data is undefined', () => {
        mockUseQuery.mockReturnValue({loading: false, data: undefined});

        const {result} = renderHook(() => useBusinessErrorCountPerConnection());

        expect(result.current.errorCountPerConnection).toEqual([]);
    });
});
