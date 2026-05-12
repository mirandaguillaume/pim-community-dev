import React from 'react';
import {renderHook, act} from '@testing-library/react';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {DependenciesContext, mockedDependencies} from '@akeneo-pim-community/shared';
import {useTrackUsageOfLoadPredefinedAttributes} from './useTrackUsageOfLoadPredefinedAttributes';

const createWrapper = () => {
  const queryClient = new QueryClient({defaultOptions: {mutations: {retry: false}}});
  return ({children}: {children: React.ReactNode}) =>
    React.createElement(
      DependenciesContext.Provider,
      {value: mockedDependencies},
      React.createElement(QueryClientProvider, {client: queryClient}, children)
    );
};

describe('useTrackUsageOfLoadPredefinedAttributes', () => {
  let fetchSpy: jest.SpyInstance;

  beforeEach(() => {
    fetchSpy = jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      status: 204,
      json: () => Promise.resolve(undefined),
    } as any);
  });

  it('returns a function (mutation.mutate)', () => {
    const {result} = renderHook(() => useTrackUsageOfLoadPredefinedAttributes('tmpl-uuid'), {
      wrapper: createWrapper(),
    });
    expect(typeof result.current).toBe('function');
  });

  it('calls POST with action in body when invoked with load_predefined_attributes', async () => {
    const {result} = renderHook(() => useTrackUsageOfLoadPredefinedAttributes('tmpl-uuid'), {
      wrapper: createWrapper(),
    });

    await act(async () => {
      result.current('load_predefined_attributes');
      await new Promise(resolve => setTimeout(resolve, 0));
    });

    expect(fetchSpy).toHaveBeenCalledWith(
      'pim_category_template_track_usage_of_load_predefined_attributes',
      expect.objectContaining({
        method: 'POST',
        body: JSON.stringify({action: 'load_predefined_attributes'}),
      })
    );
  });
});
