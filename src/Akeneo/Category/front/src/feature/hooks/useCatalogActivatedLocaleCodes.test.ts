import {renderHook, waitFor} from '@testing-library/react';
import React from 'react';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {DependenciesContext, mockedDependencies} from '@akeneo-pim-community/shared';
import {useCatalogActivatedLocaleCodes} from './useCatalogActivatedLocaleCodes';

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

describe('useCatalogActivatedLocaleCodes', () => {
  beforeEach(() => jest.clearAllMocks());

  test('it returns undefined initially', () => {
    mockedApiFetch.mockResolvedValue([]);
    const {result} = renderHook(() => useCatalogActivatedLocaleCodes(), {wrapper: createWrapper()});
    expect(result.current).toBeUndefined();
  });

  test('it fetches and returns activated locale codes', async () => {
    const mockCodes = ['en_US', 'fr_FR', 'de_DE'];
    mockedApiFetch.mockResolvedValue(mockCodes);

    const {result} = renderHook(() => useCatalogActivatedLocaleCodes(), {wrapper: createWrapper()});

    await waitFor(() => expect(result.current).toBeDefined());
    expect(result.current).toEqual(mockCodes);
  });
});
