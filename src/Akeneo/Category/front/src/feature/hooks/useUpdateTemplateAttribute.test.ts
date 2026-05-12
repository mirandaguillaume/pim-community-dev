import React from 'react';
import {renderHook, act} from '@testing-library/react';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {DependenciesContext, mockedDependencies} from '@akeneo-pim-community/shared';
import {useUpdateTemplateAttribute} from './useUpdateTemplateAttribute';

const createWrapper = () => {
  const queryClient = new QueryClient({defaultOptions: {mutations: {retry: false}}});
  return ({children}: {children: React.ReactNode}) =>
    React.createElement(
      DependenciesContext.Provider,
      {value: mockedDependencies},
      React.createElement(QueryClientProvider, {client: queryClient}, children)
    );
};

describe('useUpdateTemplateAttribute', () => {
  let fetchSpy: jest.SpyInstance;

  beforeEach(() => {
    fetchSpy = jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      status: 204,
      json: () => Promise.resolve(undefined),
    } as any);
  });

  it('generates the correct route with templateUuid and attributeUuid', async () => {
    renderHook(() => useUpdateTemplateAttribute('tmpl-1', 'attr-1'), {wrapper: createWrapper()});
    expect(fetchSpy).not.toHaveBeenCalled();
  });

  it('calls apiFetch with POST method and JSON-serialised data when mutated', async () => {
    const {result} = renderHook(() => useUpdateTemplateAttribute('tmpl-1', 'attr-1'), {wrapper: createWrapper()});

    await act(async () => {
      await result.current.mutateAsync({isRichTextArea: true, labels: {en_US: 'Color'}});
    });

    expect(fetchSpy).toHaveBeenCalledWith(
      'pim_category_template_rest_update_attribute',
      expect.objectContaining({
        method: 'POST',
        body: JSON.stringify({isRichTextArea: true, labels: {en_US: 'Color'}}),
      })
    );
  });
});
