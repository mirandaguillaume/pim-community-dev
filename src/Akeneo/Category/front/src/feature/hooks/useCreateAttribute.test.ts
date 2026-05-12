import React from 'react';
import {renderHook, act} from '@testing-library/react';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {DependenciesContext, mockedDependencies} from '@akeneo-pim-community/shared';
import {useCreateAttribute} from './useCreateAttribute';
import {BadRequestError} from '../tools/apiFetch';

const createWrapper = () => {
  const queryClient = new QueryClient({defaultOptions: {mutations: {retry: false}}});
  return ({children}: {children: React.ReactNode}) =>
    React.createElement(
      DependenciesContext.Provider,
      {value: mockedDependencies},
      React.createElement(QueryClientProvider, {client: queryClient}, children)
    );
};

const baseForm = {
  templateId: 'tmpl-uuid',
  code: 'my_attr',
  locale: 'en_US',
  label: 'My Attribute',
  type: 'text',
  isLocalizable: true,
  isScopable: false,
};

describe('useCreateAttribute', () => {
  it('calls POST with snake_case payload when mutated', async () => {
    const fetchSpy = jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      status: 204,
      json: () => Promise.resolve(undefined),
    } as any);

    const {result} = renderHook(() => useCreateAttribute(), {wrapper: createWrapper()});

    await act(async () => {
      await result.current.mutateAsync(baseForm);
    });

    expect(fetchSpy).toHaveBeenCalledWith(
      'pim_category_template_rest_add_attribute',
      expect.objectContaining({
        method: 'POST',
        body: JSON.stringify({
          code: 'my_attr',
          locale: 'en_US',
          label: 'My Attribute',
          type: 'text',
          is_localizable: true,
          is_scopable: false,
        }),
      })
    );
  });

  it('transforms API error array into property-keyed error object', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: false,
      status: 400,
      json: () =>
        Promise.resolve([
          {error: {property: 'code', message: 'Code is required'}},
          {error: {property: 'code', message: 'Code must be unique'}},
          {error: {property: 'type', message: 'Type is invalid'}},
        ]),
    } as any);

    const {result} = renderHook(() => useCreateAttribute(), {wrapper: createWrapper()});

    let caughtError: BadRequestError<{[key: string]: string[]}> | undefined;
    await act(async () => {
      try {
        await result.current.mutateAsync(baseForm);
      } catch (e) {
        caughtError = e as BadRequestError<{[key: string]: string[]}>;
      }
    });

    expect(caughtError).toBeInstanceOf(BadRequestError);
    expect(caughtError?.data).toEqual({
      code: ['Code is required', 'Code must be unique'],
      type: ['Type is invalid'],
    });
  });

  it('merges multiple errors for the same property into an array', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: false,
      status: 422,
      json: () =>
        Promise.resolve([
          {error: {property: 'code', message: 'First error'}},
          {error: {property: 'code', message: 'Second error'}},
          {error: {property: 'code', message: 'Third error'}},
        ]),
    } as any);

    const {result} = renderHook(() => useCreateAttribute(), {wrapper: createWrapper()});

    let caughtError: BadRequestError<{[key: string]: string[]}> | undefined;
    await act(async () => {
      try {
        await result.current.mutateAsync(baseForm);
      } catch (e) {
        caughtError = e as BadRequestError<{[key: string]: string[]}>;
      }
    });

    expect(caughtError?.data).toEqual({
      code: ['First error', 'Second error', 'Third error'],
    });
  });
});
