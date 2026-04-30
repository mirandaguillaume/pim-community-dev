import {useFetchUserRoles} from '@src/settings/api-hooks/use-fetch-user-roles';
import {renderHook} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses} from '../../../test-utils';

beforeEach(() => {
    fetchMock.resetMocks();
});

describe('useFetchUserRoles', () => {
    it('maps API response to UserRole shape', async () => {
        mockFetchResponses({
            pim_user_user_role_rest_index: {
                json: [
                    {id: 1, role: 'ROLE_USER', label: 'User'},
                    {id: 7, role: 'ROLE_ADMIN', label: 'Administrator'},
                ],
            },
        });

        const {result} = renderHook(() => useFetchUserRoles());
        const roles = await result.current();

        expect(roles).toStrictEqual([
            {id: '1', label: 'User', isDefault: true},
            {id: '7', label: 'Administrator', isDefault: false},
        ]);
    });

    it('marks ROLE_USER as isDefault and all other roles as not default', async () => {
        mockFetchResponses({
            pim_user_user_role_rest_index: {
                json: [
                    {id: 1, role: 'ROLE_USER', label: 'User'},
                    {id: 2, role: 'ROLE_CATALOG_MANAGER', label: 'Catalog Manager'},
                    {id: 3, role: 'ROLE_ADMIN', label: 'Admin'},
                ],
            },
        });

        const {result} = renderHook(() => useFetchUserRoles());
        const roles = await result.current();

        const defaults = roles.filter(r => r.isDefault);
        expect(defaults).toHaveLength(1);
        expect(defaults[0].id).toBe('1');
    });

    it('converts numeric id to string', async () => {
        mockFetchResponses({
            pim_user_user_role_rest_index: {
                json: [{id: 42, role: 'ROLE_ADMIN', label: 'Admin'}],
            },
        });

        const {result} = renderHook(() => useFetchUserRoles());
        const roles = await result.current();

        expect(typeof roles[0].id).toBe('string');
        expect(roles[0].id).toBe('42');
    });

    it('throws when the response is not ok', async () => {
        mockFetchResponses({
            pim_user_user_role_rest_index: {json: {}, status: 403},
        });

        const {result} = renderHook(() => useFetchUserRoles());

        await expect(result.current()).rejects.toThrow();
    });
});
