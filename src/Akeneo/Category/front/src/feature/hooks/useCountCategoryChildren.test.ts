import {renderHook, waitFor} from '@testing-library/react';
import React from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';
import {DependenciesContext, mockedDependencies} from '@akeneo-pim-community/shared';
import {useCountCategoryChildren} from './useCountCategoryChildren';

const createWrapper = () => {
  const queryClient = new QueryClient({defaultOptions: {queries: {retry: false}}});
  return ({children}: {children: React.ReactNode}) =>
    React.createElement(
      DependenciesContext.Provider,
      {value: mockedDependencies},
      React.createElement(QueryClientProvider, {client: queryClient}, children)
    );
};

describe('useCountCategoryChildren', () => {
  beforeEach(() => jest.clearAllMocks());

  test('it returns isLoading true and undefined data initially', () => {
    jest.spyOn(global, 'fetch').mockResolvedValue({
      json: () => Promise.resolve([]),
    } as any);

    const {result} = renderHook(() => useCountCategoryChildren(1), {wrapper: createWrapper()});
    expect(result.current.isLoading).toBe(true);
    expect(result.current.data).toBeUndefined();
  });

  test('it fetches the count of children by listing category ids', async () => {
    const categoryIds = [10, 11, 12, 13, 14];
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      json: () => Promise.resolve(categoryIds),
    } as any);

    const {result} = renderHook(() => useCountCategoryChildren(1), {wrapper: createWrapper()});

    await waitFor(() => expect(result.current.isLoading).toBe(false));
    expect(result.current.data).toBe(5);
  });
});
