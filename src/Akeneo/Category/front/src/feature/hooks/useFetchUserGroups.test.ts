import {renderHook, waitFor} from '@testing-library/react';
import React from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';
import {DependenciesContext, mockedDependencies} from '@akeneo-pim-community/shared';
import {useFetchUserGroups} from './useFetchUserGroups';

const createWrapper = () => {
  const queryClient = new QueryClient({defaultOptions: {queries: {retry: false}}});
  return ({children}: {children: React.ReactNode}) =>
    React.createElement(
      DependenciesContext.Provider,
      {value: mockedDependencies},
      React.createElement(QueryClientProvider, {client: queryClient}, children)
    );
};

describe('useFetchUserGroups', () => {
  beforeEach(() => jest.clearAllMocks());

  test('it returns loading status initially', () => {
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      json: () => Promise.resolve([]),
    } as any);

    const {result} = renderHook(() => useFetchUserGroups(), {wrapper: createWrapper()});
    expect(result.current.status).toBe('loading');
    expect(result.current.data).toBeUndefined();
  });

  test('it fetches and returns user groups', async () => {
    const mockGroups = [
      {id: '1', label: 'IT support', isDefault: false},
      {id: '2', label: 'All', isDefault: true},
    ];
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve(mockGroups),
    } as any);

    const {result} = renderHook(() => useFetchUserGroups(), {wrapper: createWrapper()});

    await waitFor(() => expect(result.current.status).toBe('success'));
    expect(result.current.data).toEqual(mockGroups);
  });

  test('it sets error status when fetch fails', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      ok: false,
      status: 500,
    } as any);

    const {result} = renderHook(() => useFetchUserGroups(), {wrapper: createWrapper()});

    await waitFor(() => expect(result.current.status).toBe('error'));
  });
});
