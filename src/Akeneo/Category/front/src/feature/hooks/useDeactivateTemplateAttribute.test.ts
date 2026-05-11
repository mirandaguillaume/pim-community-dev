import React from 'react';
import {renderHook, act} from '@testing-library/react';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {useDeactivateTemplateAttribute} from './useDeactivateTemplateAttribute';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useNotify: jest.fn(),
  useTranslate: jest.fn(),
  useRoute: jest.fn(),
}));

const mockedUseNotify = useNotify as jest.Mock;
const mockedUseTranslate = useTranslate as jest.Mock;
const mockedUseRoute = useRoute as jest.Mock;

const createWrapper = () => {
  const queryClient = new QueryClient({defaultOptions: {mutations: {retry: false}}});
  return ({children}: {children: React.ReactNode}) =>
    React.createElement(QueryClientProvider, {client: queryClient}, children);
};

describe('useDeactivateTemplateAttribute', () => {
  let mockNotify: jest.Mock;
  let mockTranslate: jest.Mock;
  let fetchSpy: jest.SpyInstance;

  beforeEach(() => {
    mockNotify = jest.fn();
    mockTranslate = jest.fn((key: string) => key);
    mockedUseNotify.mockReturnValue(mockNotify);
    mockedUseTranslate.mockReturnValue(mockTranslate);
    mockedUseRoute.mockReturnValue('/delete-attribute-url');
    fetchSpy = jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      status: 204,
      json: () => Promise.resolve(undefined),
    } as any);
  });

  it('returns an async function', () => {
    const {result} = renderHook(
      () => useDeactivateTemplateAttribute('tmpl-uuid', {uuid: 'attr-uuid', label: 'Color'}),
      {wrapper: createWrapper()}
    );
    expect(typeof result.current).toBe('function');
  });

  it('calls DELETE on the attribute route when invoked', async () => {
    const {result} = renderHook(
      () => useDeactivateTemplateAttribute('tmpl-uuid', {uuid: 'attr-uuid', label: 'Color'}),
      {wrapper: createWrapper()}
    );

    await act(async () => {
      await result.current();
    });

    expect(fetchSpy).toHaveBeenCalledWith('/delete-attribute-url', expect.objectContaining({method: 'DELETE'}));
  });

  it('notifies success with the attribute label after deletion', async () => {
    const {result} = renderHook(
      () => useDeactivateTemplateAttribute('tmpl-uuid', {uuid: 'attr-uuid', label: 'Color'}),
      {wrapper: createWrapper()}
    );

    await act(async () => {
      await result.current();
    });

    expect(mockNotify).toHaveBeenCalledWith(
      NotificationLevel.SUCCESS,
      'akeneo.category.template.delete_attribute.notification_success.title'
    );
    expect(mockTranslate).toHaveBeenCalledWith('akeneo.category.template.delete_attribute.notification_success.title', {
      attribute: 'Color',
    });
  });

  it('generates the route with templateUuid and attributeUuid', () => {
    renderHook(() => useDeactivateTemplateAttribute('tmpl-uuid', {uuid: 'attr-uuid', label: 'Color'}), {
      wrapper: createWrapper(),
    });

    expect(mockedUseRoute).toHaveBeenCalledWith('pim_category_template_rest_delete_attribute', {
      templateUuid: 'tmpl-uuid',
      attributeUuid: 'attr-uuid',
    });
  });
});
