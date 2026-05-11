import React from 'react';
import {renderHook, act} from '@testing-library/react';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {DependenciesContext, mockedDependencies} from '@akeneo-pim-community/shared';
import {useReorderAttributes} from './useReorderAttributes';

const createWrapper = () => {
  const queryClient = new QueryClient({defaultOptions: {mutations: {retry: false}}});
  return ({children}: {children: React.ReactNode}) =>
    React.createElement(
      DependenciesContext.Provider,
      {value: mockedDependencies},
      React.createElement(QueryClientProvider, {client: queryClient}, children)
    );
};

describe('useReorderAttributes', () => {
  let fetchSpy: jest.SpyInstance;

  beforeEach(() => {
    fetchSpy = jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      status: 204,
      json: () => Promise.resolve(undefined),
    } as any);
  });

  it('calls apiFetch with POST and JSON-serialised uuid array when mutated', async () => {
    const {result} = renderHook(() => useReorderAttributes(), {wrapper: createWrapper()});

    await act(async () => {
      await result.current.mutateAsync({
        templateUuid: 'tmpl-uuid',
        uuids: ['uuid-a', 'uuid-b', 'uuid-c'],
      });
    });

    expect(fetchSpy).toHaveBeenCalledWith(
      'pim_category_template_rest_reorder_attributes',
      expect.objectContaining({
        method: 'POST',
        body: JSON.stringify(['uuid-a', 'uuid-b', 'uuid-c']),
      })
    );
  });
});
