import React from 'react';
import {renderHook, act} from '@testing-library/react';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {DependenciesContext, mockedDependencies} from '@akeneo-pim-community/shared';
import {useCreateTemplate} from './useCreateTemplate';

const createWrapper = () => {
  const queryClient = new QueryClient({defaultOptions: {mutations: {retry: false}}});
  return ({children}: {children: React.ReactNode}) =>
    React.createElement(
      DependenciesContext.Provider,
      {value: mockedDependencies},
      React.createElement(QueryClientProvider, {client: queryClient}, children)
    );
};

describe('useCreateTemplate', () => {
  let fetchSpy: jest.SpyInstance;

  beforeEach(() => {
    fetchSpy = jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      status: 200,
      json: () => Promise.resolve({template_uuid: 'new-uuid'}),
    } as any);
  });

  it('calls apiFetch with POST and code + labels payload when mutated', async () => {
    const {result} = renderHook(() => useCreateTemplate(), {wrapper: createWrapper()});

    await act(async () => {
      await result.current.mutateAsync({
        categoryTreeId: 1,
        code: 'my_template',
        locale: 'en_US',
        label: 'My Template',
      });
    });

    expect(fetchSpy).toHaveBeenCalledWith(
      'pim_category_template_rest_create',
      expect.objectContaining({
        method: 'POST',
        body: JSON.stringify({code: 'my_template', labels: {en_US: 'My Template'}}),
      })
    );
  });

  it('returns the template_uuid from the API response', async () => {
    const {result} = renderHook(() => useCreateTemplate(), {wrapper: createWrapper()});

    let data: {template_uuid: string} | undefined;
    await act(async () => {
      data = await result.current.mutateAsync({
        categoryTreeId: 1,
        code: 'my_template',
        locale: 'en_US',
        label: null,
      });
    });

    expect(data).toEqual({template_uuid: 'new-uuid'});
  });
});
