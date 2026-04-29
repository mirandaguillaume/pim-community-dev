import {useFetchUserGroups} from '@src/settings/api-hooks/use-fetch-user-groups';
import {renderHook} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses} from '../../../test-utils';

beforeEach(() => {
    fetchMock.resetMocks();
});

describe('useFetchUserGroups', () => {
    it('maps API response to UserGroup shape', async () => {
        mockFetchResponses({
            pim_user_user_group_rest_index: {
                json: [
                    {name: 'All', meta: {id: 1, default: true}},
                    {name: 'Editors', meta: {id: 42, default: false}},
                ],
            },
        });

        const {result} = renderHook(() => useFetchUserGroups());
        const groups = await result.current();

        expect(groups).toStrictEqual([
            {id: '1', label: 'All', isDefault: true},
            {id: '42', label: 'Editors', isDefault: false},
        ]);
    });

    it('converts numeric meta.id to string', async () => {
        mockFetchResponses({
            pim_user_user_group_rest_index: {
                json: [{name: 'Managers', meta: {id: 999, default: false}}],
            },
        });

        const {result} = renderHook(() => useFetchUserGroups());
        const groups = await result.current();

        expect(typeof groups[0].id).toBe('string');
        expect(groups[0].id).toBe('999');
    });

    it('throws when the response is not ok', async () => {
        mockFetchResponses({
            pim_user_user_group_rest_index: {json: {}, status: 500},
        });

        const {result} = renderHook(() => useFetchUserGroups());

        await expect(result.current()).rejects.toThrow();
    });
});
