import React from 'react';
import {renderHook, waitFor} from '@testing-library/react';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {DependenciesContext, mockedDependencies} from '@akeneo-pim-community/shared';
import {useCategoryTree} from './useCategoryTree';
import {convertToCategoryTree} from '../models';

jest.mock('../models', () => ({
  ...jest.requireActual('../models'),
  convertToCategoryTree: jest.fn(tree => ({...tree, converted: true})),
}));

const createWrapper = () => {
  const queryClient = new QueryClient({defaultOptions: {queries: {retry: false}}});
  return ({children}: {children: React.ReactNode}) =>
    React.createElement(
      DependenciesContext.Provider,
      {value: mockedDependencies},
      React.createElement(QueryClientProvider, {client: queryClient}, children)
    );
};

const mockTree = {id: 1, code: 'master', label: 'Master', children: []};

describe('useCategoryTree', () => {
  let fetchSpy: jest.SpyInstance;

  beforeEach(() => {
    fetchSpy = jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      status: 200,
      json: () => Promise.resolve(mockTree),
    } as any);
  });

  it('fetches the category tree using the correct route', async () => {
    const {result} = renderHook(() => useCategoryTree('42'), {wrapper: createWrapper()});

    await waitFor(() => expect(result.current.status).toBe('success'));

    expect(fetchSpy).toHaveBeenCalledWith('pim_enrich_categorytree_children', expect.any(Object));
  });

  it('transforms the response with convertToCategoryTree', async () => {
    const {result} = renderHook(() => useCategoryTree('42'), {wrapper: createWrapper()});

    await waitFor(() => expect(result.current.status).toBe('success'));

    expect(convertToCategoryTree).toHaveBeenCalledWith(mockTree);
    expect(result.current.data).toEqual({...mockTree, converted: true});
  });

  it('sets error status when fetch fails', async () => {
    fetchSpy.mockResolvedValue({ok: false, status: 500, statusText: 'Server Error'} as any);

    const {result} = renderHook(() => useCategoryTree('42'), {wrapper: createWrapper()});

    await waitFor(() => expect(result.current.status).toBe('error'));
  });
});
