import {act, renderHook} from '@testing-library/react';
import {useFetchUserGroups} from '@src/settings/api-hooks/use-fetch-user-groups';

describe('useFetchUserGroups', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
    });

    it('maps API response to UserGroup shape', async () => {
        fetchMock.mockResponseOnce(
            JSON.stringify([
                {name: 'Redactors', meta: {id: 2, default: false}},
                {name: 'All', meta: {id: 1, default: true}},
            ])
        );

        const {result} = renderHook(() => useFetchUserGroups());

        let groups;
        await act(async () => {
            groups = await result.current();
        });

        expect(groups).toEqual([
            {id: '2', label: 'Redactors', isDefault: false},
            {id: '1', label: 'All', isDefault: true},
        ]);
    });

    it('throws when the API returns an error', async () => {
        fetchMock.mockResponseOnce('', {status: 500});

        const {result} = renderHook(() => useFetchUserGroups());

        await expect(act(() => result.current())).rejects.toThrow();
    });
});
