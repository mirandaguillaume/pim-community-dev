import {renderHook, waitFor} from '@testing-library/react';
import React from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';
import {DependenciesContext, mockedDependencies} from '@akeneo-pim-community/shared';
import {useUiLocales} from './useUiLocales';

jest.mock('../tools/apiFetch');

import {apiFetch} from '../tools/apiFetch';

const mockedApiFetch = apiFetch as jest.Mock;

const createWrapper = () => {
  const queryClient = new QueryClient({defaultOptions: {queries: {retry: false}}});
  return ({children}: {children: React.ReactNode}) =>
    React.createElement(
      DependenciesContext.Provider,
      {value: mockedDependencies},
      React.createElement(QueryClientProvider, {client: queryClient}, children)
    );
};

describe('useUiLocales', () => {
  beforeEach(() => jest.clearAllMocks());

  test('it returns undefined initially', () => {
    mockedApiFetch.mockResolvedValue([]);
    const {result} = renderHook(() => useUiLocales(), {wrapper: createWrapper()});
    expect(result.current).toBeUndefined();
  });

  test('it fetches and returns UI locales', async () => {
    const mockLocales = [
      {id: 1, code: 'en_US', label: 'English (United States)', region: 'United States', language: 'English'},
    ];
    mockedApiFetch.mockResolvedValue(mockLocales);

    const {result} = renderHook(() => useUiLocales(), {wrapper: createWrapper()});

    await waitFor(() => expect(result.current).toBeDefined());
    expect(result.current).toEqual(mockLocales);
  });
});
