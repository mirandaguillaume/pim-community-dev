import {renderHook, waitFor} from '@testing-library/react';
import React from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';
import {DependenciesContext, mockedDependencies} from '@akeneo-pim-community/shared';
import {useCatalogLocales} from './useCatalogLocales';

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

describe('useCatalogLocales', () => {
  beforeEach(() => jest.clearAllMocks());

  test('it returns undefined initially', () => {
    mockedApiFetch.mockResolvedValue([]);
    const {result} = renderHook(() => useCatalogLocales(), {wrapper: createWrapper()});
    expect(result.current).toBeUndefined();
  });

  test('it fetches and returns catalog locales', async () => {
    const mockLocales = [
      {id: 1, code: 'en_US', label: 'English (United States)', region: 'United States', language: 'English'},
      {id: 2, code: 'fr_FR', label: 'French (France)', region: 'France', language: 'French'},
    ];
    mockedApiFetch.mockResolvedValue(mockLocales);

    const {result} = renderHook(() => useCatalogLocales(), {wrapper: createWrapper()});

    await waitFor(() => expect(result.current).toBeDefined());
    expect(result.current).toEqual(mockLocales);
  });
});
