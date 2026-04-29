import {renderHook} from '@testing-library/react';
import {useConnections} from '@src/audit/hooks/api/use-connections';
import {useQuery} from '@src/shared/fetch';
import {FlowType} from '@src/model/flow-type.enum';

jest.mock('@src/shared/fetch', () => ({
    useQuery: jest.fn(),
}));

const mockUseQuery = useQuery as jest.Mock;

describe('useConnections', () => {
    beforeEach(() => {
        mockUseQuery.mockReset();
    });

    it('returns loading state while fetching', () => {
        mockUseQuery.mockReturnValue({loading: true, data: undefined});

        const {result} = renderHook(() => useConnections());

        expect(result.current.loading).toBe(true);
        expect(result.current.connections).toBeUndefined();
    });

    it('queries the connections list endpoint filtering default and app types', () => {
        mockUseQuery.mockReturnValue({loading: false, data: []});

        renderHook(() => useConnections());

        expect(mockUseQuery).toHaveBeenCalledWith(
            'akeneo_connectivity_connection_rest_list',
            {search: JSON.stringify({types: ['default', 'app']})}
        );
    });

    it('returns connections from query data', () => {
        const connections = [
            {code: 'magento', label: 'Magento', flowType: FlowType.DATA_DESTINATION, image: null, auditable: true},
            {code: 'bynder', label: 'Bynder', flowType: FlowType.DATA_SOURCE, image: null, auditable: true},
        ];
        mockUseQuery.mockReturnValue({loading: false, data: connections});

        const {result} = renderHook(() => useConnections());

        expect(result.current.loading).toBe(false);
        expect(result.current.connections).toStrictEqual(connections);
    });
});
