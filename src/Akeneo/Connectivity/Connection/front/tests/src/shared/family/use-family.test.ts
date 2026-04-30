import {useFamily} from '@src/shared/family/use-family';
import {useQuery} from '@src/shared/fetch';
import {renderHook} from '@testing-library/react';

jest.mock('@src/shared/fetch', () => ({
    useQuery: jest.fn(),
}));

const mockUseQuery = useQuery as jest.Mock;

describe('useFamily', () => {
    afterEach(() => {
        jest.clearAllMocks();
    });

    it('returns loading true and empty families while fetching', () => {
        mockUseQuery.mockReturnValue({loading: true, data: undefined});

        const {result} = renderHook(() => useFamily('en_US'));

        expect(result.current.loading).toBe(true);
        expect(result.current.families).toEqual([]);
    });

    it('maps dict values to families using the given locale for labels', () => {
        mockUseQuery.mockReturnValue({
            loading: false,
            data: {
                camcorders: {code: 'camcorders', labels: {en_US: 'Camcorders', fr_FR: 'Caméscopes'}},
            },
        });

        const {result} = renderHook(() => useFamily('en_US'));

        expect(result.current.families).toStrictEqual([{code: 'camcorders', label: 'Camcorders'}]);
    });

    it('falls back to [code] when label is missing for the given locale', () => {
        mockUseQuery.mockReturnValue({
            loading: false,
            data: {clothing: {code: 'clothing', labels: {fr_FR: 'Vêtements'}}},
        });

        const {result} = renderHook(() => useFamily('en_US'));

        expect(result.current.families).toStrictEqual([{code: 'clothing', label: '[clothing]'}]);
    });
});
