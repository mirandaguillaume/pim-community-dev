import {useConfirmAuthorization} from '@src/connect/hooks/use-confirm-authorization';
import {renderHook} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses} from '../../../test-utils';

beforeEach(() => {
    fetchMock.resetMocks();
});

test('it confirms the authorization and returns app data', async () => {
    const expectedData = {appId: 'app_id', userGroup: 'group', redirectUrl: '/redirect'};
    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_confirm_authorization?clientId=my_client': {
            json: expectedData,
        },
    });

    const {result} = renderHook(() => useConfirmAuthorization('my_client'));
    const data = await result.current();

    expect(fetchMock).toBeCalledWith(
        'akeneo_connectivity_connection_apps_rest_confirm_authorization?clientId=my_client',
        {method: 'POST', headers: [['X-Requested-With', 'XMLHttpRequest']]}
    );
    expect(data).toStrictEqual(expectedData);
});

test('it rejects with status, statusText and errors on non-ok response', async () => {
    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_confirm_authorization?clientId=bad_client': {
            json: {errors: [{message: 'Forbidden', property_path: ''}]},
            status: 403,
        },
    });

    const {result} = renderHook(() => useConfirmAuthorization('bad_client'));

    await expect(result.current()).rejects.toMatchObject({
        status: 403,
        errors: [{message: 'Forbidden', property_path: ''}],
    });
});

test('it rejects with empty errors array when response body has no errors field', async () => {
    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_confirm_authorization?clientId=err_client': {
            json: {},
            status: 400,
        },
    });

    const {result} = renderHook(() => useConfirmAuthorization('err_client'));

    await expect(result.current()).rejects.toMatchObject({status: 400, errors: []});
});
