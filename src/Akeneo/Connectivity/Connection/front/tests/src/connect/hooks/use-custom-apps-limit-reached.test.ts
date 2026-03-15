import {renderHook, act} from '@testing-library/react';
import {mockFetchResponses, ReactQueryWrapper} from '../../../test-utils';
import {useCustomAppsLimitReached} from '@src/connect/hooks/use-custom-apps-limit-reached';
import fetchMock from 'jest-fetch-mock';

test('it returns true when max connections limit is true', async () => {
    mockFetchResponses({
        akeneo_connectivity_connection_custom_apps_rest_max_limit_reached: {
            json: true,
        },
    });

    const {result} = renderHook(() => useCustomAppsLimitReached(), {wrapper: ReactQueryWrapper});

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await act(async () => {
        await new Promise(r => setTimeout(r, 0));
    });

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: true,
        error: null,
    });
});

test('it returns false when max connections limit is false', async () => {
    mockFetchResponses({
        akeneo_connectivity_connection_custom_apps_rest_max_limit_reached: {
            json: false,
        },
    });

    const {result} = renderHook(() => useCustomAppsLimitReached(), {wrapper: ReactQueryWrapper});

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await act(async () => {
        await new Promise(r => setTimeout(r, 0));
    });

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: false,
        error: null,
    });
});
