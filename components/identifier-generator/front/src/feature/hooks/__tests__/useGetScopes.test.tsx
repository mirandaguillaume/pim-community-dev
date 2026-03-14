import {mockResponse} from '../../tests/test-utils';
import mockedScopes from '../../tests/fixtures/scopes';
import {renderHook, waitFor} from '@testing-library/react';
import {useGetScopes} from '../useGetScopes';
import {createWrapper} from '../../tests/hooks/config/createWrapper';

describe('useGetScopes', () => {
  it('retrieves scopes list', async () => {
    mockResponse('pim_enrich_channel_rest_index', 'GET', {ok: true, json: () => mockedScopes});

    const {result} = renderHook(() => useGetScopes(), {wrapper: createWrapper()});

    await waitFor(() => !!result.current.data);

    expect(result.current.data).toEqual(mockedScopes);
  });
});
