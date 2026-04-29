import {useTriggerConnectedAppRefresh} from '@src/connect/hooks/use-trigger-connected-app-refresh';
import {renderHook} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses} from '../../../test-utils';

beforeEach(() => {
    fetchMock.resetMocks();
});

test('it returns true when refresh succeeds', async () => {
    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_refresh?connectionCode=magento': {
            json: {},
            status: 200,
        },
    });

    const {result} = renderHook(() => useTriggerConnectedAppRefresh());
    const refreshed = await result.current('magento');

    expect(fetchMock).toBeCalledWith('akeneo_connectivity_connection_apps_rest_refresh?connectionCode=magento', {
        method: 'POST',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
    });
    expect(refreshed).toBe(true);
});

test('it returns false when refresh returns a non-ok response', async () => {
    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_refresh?connectionCode=broken': {
            json: {},
            status: 500,
        },
    });

    const {result} = renderHook(() => useTriggerConnectedAppRefresh());
    const refreshed = await result.current('broken');

    expect(refreshed).toBe(false);
});
