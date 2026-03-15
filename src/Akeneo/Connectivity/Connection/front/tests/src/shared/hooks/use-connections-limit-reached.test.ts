import {renderHook, act} from '@testing-library/react';
import {mockFetchResponses} from '../../../test-utils';
import {useConnectionsLimitReached} from '@src/shared/hooks/use-connections-limit-reached';

test('it returns true when max connections limit is true ', async () => {
    mockFetchResponses({
        akeneo_connectivity_connection_rest_connections_max_limit_reached: {
            json: {limitReached: true},
        },
    });

    const {result} = renderHook(() => useConnectionsLimitReached());

    expect(result.current).toStrictEqual(false);

    await act(async () => {
        await new Promise(r => setTimeout(r, 0));
    });

    expect(result.current).toStrictEqual(true);
});

test('it returns false when max connections limit is false ', async () => {
    mockFetchResponses({
        akeneo_connectivity_connection_rest_connections_max_limit_reached: {
            json: {limitReached: false},
        },
    });

    const {result} = renderHook(() => useConnectionsLimitReached());

    expect(result.current).toStrictEqual(false);

    await new Promise(setImmediate);

    expect(result.current).toStrictEqual(false);
});

test('it returns true on fetch error', async () => {
    mockFetchResponses({
        akeneo_connectivity_connection_rest_connections_max_limit_reached: {
            reject: true,
            json: {},
        },
    });

    const {result} = renderHook(() => useConnectionsLimitReached());

    expect(result.current).toStrictEqual(false);

    await act(async () => {
        await new Promise(r => setTimeout(r, 0));
    });

    expect(result.current).toStrictEqual(true);
});
