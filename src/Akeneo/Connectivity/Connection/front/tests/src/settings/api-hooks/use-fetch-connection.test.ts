import {useFetchConnection} from '@src/settings/api-hooks/use-fetch-connection';
import {FlowType} from '@src/model/flow-type.enum';
import {renderHook} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {isOk, isErr} from '@src/shared/fetch-result/result';
import {mockFetchResponses} from '../../../test-utils';

const apiConnection = {
    code: 'magento',
    label: 'Magento',
    flow_type: FlowType.DATA_DESTINATION,
    image: null,
    client_id: 'client-abc',
    secret: 'secret-xyz',
    username: 'magento_user',
    password: null,
    user_role_id: '1',
    user_group_id: '3',
    auditable: true,
};

beforeEach(() => {
    fetchMock.resetMocks();
});

describe('useFetchConnection', () => {
    it('maps snake_case API fields to camelCase Connection model', async () => {
        mockFetchResponses({
            'akeneo_connectivity_connection_rest_get?code=magento': {json: apiConnection},
        });

        const {result} = renderHook(() => useFetchConnection('magento'));
        const response = await result.current();

        expect(isOk(response)).toBe(true);
        if (!isOk(response)) return;

        expect(response.value).toMatchObject({
            code: 'magento',
            label: 'Magento',
            flowType: FlowType.DATA_DESTINATION,
            clientId: 'client-abc',
            secret: 'secret-xyz',
            username: 'magento_user',
            password: null,
            userRoleId: '1',
            userGroupId: '3',
            auditable: true,
            image: null,
        });
    });

    it('propagates err result when fetch fails', async () => {
        mockFetchResponses({
            'akeneo_connectivity_connection_rest_get?code=missing': {
                json: {message: 'Not Found'},
                status: 404,
            },
        });

        const {result} = renderHook(() => useFetchConnection('missing'));
        const response = await result.current();

        expect(isErr(response)).toBe(true);
    });

    it('passes the connection code in the route', async () => {
        mockFetchResponses({
            'akeneo_connectivity_connection_rest_get?code=bynder': {json: {...apiConnection, code: 'bynder'}},
        });

        const {result} = renderHook(() => useFetchConnection('bynder'));
        await result.current();

        expect(fetchMock).toHaveBeenCalledWith(
            'akeneo_connectivity_connection_rest_get?code=bynder',
            expect.any(Object)
        );
    });
});
