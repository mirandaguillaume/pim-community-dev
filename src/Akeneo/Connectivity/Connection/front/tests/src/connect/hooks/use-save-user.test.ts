import {useSaveUserProfile} from '@src/connect/hooks/use-save-user';
import {renderHook} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses} from '../../../test-utils';

beforeEach(() => {
    fetchMock.resetMocks();
});

test('it saves a user profile and returns the response', async () => {
    const expectedResponse = {success: true};
    mockFetchResponses({
        'pim_user_user_rest_profile?identifier=john': {
            json: expectedResponse,
        },
    });

    const {result} = renderHook(() => useSaveUserProfile('john'));
    const data = await result.current({profile: 'manager'});

    expect(fetchMock).toBeCalledWith('pim_user_user_rest_profile?identifier=john', {
        method: 'POST',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
        body: JSON.stringify({profile: 'manager'}),
    });
    expect(data).toStrictEqual(expectedResponse);
});
