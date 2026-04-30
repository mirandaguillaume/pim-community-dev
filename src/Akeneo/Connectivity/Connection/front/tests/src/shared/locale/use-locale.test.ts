import {useLocale} from '@src/shared/locale/use-locale';
import {useQuery} from '@src/shared/fetch';
import {renderHook} from '@testing-library/react';

jest.mock('@src/shared/fetch', () => ({
    useQuery: jest.fn(),
}));

const mockUseQuery = useQuery as jest.Mock;

describe('useLocale', () => {
    afterEach(() => {
        jest.clearAllMocks();
    });

    it('returns loading true and empty locales while fetching', () => {
        mockUseQuery.mockReturnValue({loading: true, data: undefined});

        const {result} = renderHook(() => useLocale());

        expect(result.current.loading).toBe(true);
        expect(result.current.locales).toEqual([]);
    });

    it('maps data to locales with only code and language', () => {
        mockUseQuery.mockReturnValue({
            loading: false,
            data: [{code: 'en_US', label: 'English (United States)', region: 'US', language: 'English'}],
        });

        const {result} = renderHook(() => useLocale());

        expect(result.current.locales).toStrictEqual([{code: 'en_US', language: 'English'}]);
    });

    it('returns empty locales when data is an empty array', () => {
        mockUseQuery.mockReturnValue({loading: false, data: []});

        const {result} = renderHook(() => useLocale());

        expect(result.current.locales).toEqual([]);
    });
});
