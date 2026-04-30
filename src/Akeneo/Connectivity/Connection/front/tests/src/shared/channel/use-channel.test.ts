import {useChannel} from '@src/shared/channel/use-channel';
import {useQuery} from '@src/shared/fetch';
import {renderHook} from '@testing-library/react';

jest.mock('@src/shared/fetch', () => ({
    useQuery: jest.fn(),
}));

const mockUseQuery = useQuery as jest.Mock;

describe('useChannel', () => {
    afterEach(() => {
        jest.clearAllMocks();
    });

    it('returns loading true and empty channels while fetching', () => {
        mockUseQuery.mockReturnValue({loading: true, data: undefined});

        const {result} = renderHook(() => useChannel('en_US'));

        expect(result.current.loading).toBe(true);
        expect(result.current.channels).toEqual([]);
    });

    it('maps data to channels using the given locale for labels', () => {
        mockUseQuery.mockReturnValue({
            loading: false,
            data: [{code: 'ecommerce', labels: {en_US: 'E-commerce', fr_FR: 'E-commerce FR'}}],
        });

        const {result} = renderHook(() => useChannel('en_US'));

        expect(result.current.channels).toStrictEqual([{code: 'ecommerce', label: 'E-commerce'}]);
    });

    it('falls back to [code] when label is missing for the given locale', () => {
        mockUseQuery.mockReturnValue({
            loading: false,
            data: [{code: 'print', labels: {fr_FR: 'Impression'}}],
        });

        const {result} = renderHook(() => useChannel('en_US'));

        expect(result.current.channels).toStrictEqual([{code: 'print', label: '[print]'}]);
    });

    it('returns a stable channels array reference when data and locale are unchanged', () => {
        mockUseQuery.mockReturnValue({
            loading: false,
            data: [{code: 'ecommerce', labels: {en_US: 'E-commerce'}}],
        });

        const {result, rerender} = renderHook(() => useChannel('en_US'));
        const first = result.current.channels;
        rerender();

        expect(result.current.channels).toBe(first);
    });
});
