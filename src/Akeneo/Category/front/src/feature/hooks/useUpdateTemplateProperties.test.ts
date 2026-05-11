import React from 'react';
import {renderHook, act} from '@testing-library/react';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {DependenciesContext, mockedDependencies} from '@akeneo-pim-community/shared';
import {useUpdateTemplateProperties} from './useUpdateTemplateProperties';

const createWrapper = () => {
  const queryClient = new QueryClient({defaultOptions: {mutations: {retry: false}}});
  return ({children}: {children: React.ReactNode}) =>
    React.createElement(
      DependenciesContext.Provider,
      {value: mockedDependencies},
      React.createElement(QueryClientProvider, {client: queryClient}, children)
    );
};

describe('useUpdateTemplateProperties', () => {
  let fetchSpy: jest.SpyInstance;

  beforeEach(() => {
    fetchSpy = jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      status: 204,
      json: () => Promise.resolve(undefined),
    } as any);
  });

  it('calls apiFetch with PATCH method and labels payload when mutated', async () => {
    const {result} = renderHook(() => useUpdateTemplateProperties('tmpl-uuid'), {wrapper: createWrapper()});

    await act(async () => {
      await result.current.mutateAsync({labels: {en_US: 'My Template', fr_FR: 'Mon Template'}});
    });

    expect(fetchSpy).toHaveBeenCalledWith(
      'pim_category_template_rest_update',
      expect.objectContaining({
        method: 'PATCH',
        body: JSON.stringify({labels: {en_US: 'My Template', fr_FR: 'Mon Template'}}),
      })
    );
  });
});
