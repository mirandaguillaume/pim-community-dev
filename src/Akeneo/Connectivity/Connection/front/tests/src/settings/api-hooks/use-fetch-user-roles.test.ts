import {act, renderHook} from '@testing-library/react';
import {useFetchUserRoles} from '@src/settings/api-hooks/use-fetch-user-roles';

describe('useFetchUserRoles', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
    });

    it('maps API response to UserRole shape and marks ROLE_USER as default', async () => {
        fetchMock.mockResponseOnce(
            JSON.stringify([
                {id: 1, role: 'ROLE_USER', label: 'User'},
                {id: 2, role: 'ROLE_ADMINISTRATOR', label: 'Administrator'},
            ])
        );

        const {result} = renderHook(() => useFetchUserRoles());

        let roles;
        await act(async () => {
            roles = await result.current();
        });

        expect(roles).toEqual([
            {id: '1', label: 'User', isDefault: true},
            {id: '2', label: 'Administrator', isDefault: false},
        ]);
    });

    it('throws when the API returns an error', async () => {
        fetchMock.mockResponseOnce('', {status: 500});

        const {result} = renderHook(() => useFetchUserRoles());

        await expect(act(() => result.current())).rejects.toThrow();
    });
});
