import {renderHook, act} from '@testing-library/react';
import {useFetchData} from '@src/shared/hooks/use-fetch-data';
import {mockFetchResponses} from '../../../test-utils';

test('it returns loading status and fetched data', async () => {
    mockFetchResponses({
        'some_route?someParam=someParamValue': {
            json: {some: 'content'},
        },
    });

    const {result} = renderHook(() =>
        useFetchData<{some: string}>('some_route', {
            someParam: 'someParamValue',
        })
    );

    expect(result.current).toStrictEqual({
        isLoading: true,
        data: undefined,
    });

    await act(async () => {
        await new Promise(r => setTimeout(r, 0));
    });

    expect(result.current).toStrictEqual({
        isLoading: false,
        data: {some: 'content'},
    });
});

test('it returns loading status and fetched data is undefined on fetch error', async () => {
    mockFetchResponses({
        'some_route?someParam=someParamValue': {
            reject: true,
            json: {},
        },
    });

    const {result} = renderHook(() =>
        useFetchData<{some: string}>('some_route', {
            someParam: 'someParamValue',
        })
    );

    expect(result.current).toStrictEqual({
        isLoading: true,
        data: undefined,
    });

    await act(async () => {
        await new Promise(r => setTimeout(r, 0));
    });

    expect(result.current).toStrictEqual({
        isLoading: false,
        data: undefined,
    });
});
