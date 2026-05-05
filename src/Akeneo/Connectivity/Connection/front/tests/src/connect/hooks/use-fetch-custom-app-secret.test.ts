import {renderHook, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses, ReactQueryWrapper as wrapper} from '../../../test-utils';
import {useFetchCustomAppSecret} from '@src/connect/hooks/use-fetch-custom-app-secret';

beforeEach(() => {
    fetchMock.resetMocks();
});

test('it returns the custom app secret', async () => {
    mockFetchResponses({
        '/rest/custom-apps/my-app-id/secret': {
            json: 'super_secret_value',
        },
    });

    const {result} = renderHook(() => useFetchCustomAppSecret('my-app-id'), {wrapper});

    await waitFor(() => expect(result.current.isLoading).toBe(false));

    expect(result.current.data).toBe('super_secret_value');
    expect(result.current.isError).toBe(false);
});

test('it is loading initially', () => {
    mockFetchResponses({
        '/rest/custom-apps/loading-app/secret': {
            json: 'secret',
        },
    });

    const {result} = renderHook(() => useFetchCustomAppSecret('loading-app'), {wrapper});

    expect(result.current.isLoading).toBe(true);
});
