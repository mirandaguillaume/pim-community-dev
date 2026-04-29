import {useFetchUserProfiles} from '@src/connect/hooks/use-fetch-user-profiles';
import {renderHook} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses} from '../../../test-utils';

beforeEach(() => {
    fetchMock.resetMocks();
});

test('it fetches user profiles and returns the data', async () => {
    const expectedProfiles = [{code: 'IT support', label: 'IT support'}];
    mockFetchResponses({
        pim_user_rest_find_all_profiles: {
            json: expectedProfiles,
        },
    });

    const {result} = renderHook(() => useFetchUserProfiles());
    const data = await result.current();

    expect(fetchMock).toBeCalledWith('pim_user_rest_find_all_profiles', {
        method: 'GET',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
    });
    expect(data).toStrictEqual(expectedProfiles);
});
