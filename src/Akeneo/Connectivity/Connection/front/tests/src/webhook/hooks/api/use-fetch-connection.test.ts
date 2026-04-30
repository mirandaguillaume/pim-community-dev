import {useFetchConnection} from '@src/webhook/hooks/api/use-fetch-connection';
import {useQuery} from '@src/shared/fetch';
import {renderHook} from '@testing-library/react';

jest.mock('@src/shared/fetch', () => ({
    useQuery: jest.fn(),
}));

const mockUseQuery = useQuery as jest.Mock;

describe('useFetchConnection', () => {
    afterEach(() => {
        jest.clearAllMocks();
    });

    it('returns loading:true and connection:undefined while loading', () => {
        mockUseQuery.mockReturnValue({loading: true, data: undefined});

        const {result} = renderHook(() => useFetchConnection('magento'));

        expect(result.current.loading).toBe(true);
        expect(result.current.connection).toBeUndefined();
    });

    it('returns connection data when loaded', () => {
        const connection = {code: 'magento', label: 'Magento', image: null};
        mockUseQuery.mockReturnValue({loading: false, data: connection});

        const {result} = renderHook(() => useFetchConnection('magento'));

        expect(result.current.loading).toBe(false);
        expect(result.current.connection).toStrictEqual(connection);
    });

    it('passes the connection code as route param', () => {
        mockUseQuery.mockReturnValue({loading: false, data: undefined});

        renderHook(() => useFetchConnection('bynder'));

        expect(mockUseQuery).toHaveBeenCalledWith('akeneo_connectivity_connection_rest_get', {code: 'bynder'});
    });
});
