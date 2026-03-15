import {act, renderHook, waitFor} from '@testing-library/react';
import {usePaginatedOptions} from '../useGetSelectOptions';
import {mockResponse} from '../../tests/test-utils';
import {createWrapper} from '../../tests/hooks/config/createWrapper';

describe('useGetSelectOptions', () => {
  test('it paginates select options', async () => {
    const page1 = [...Array(20)].map((_, i) => ({code: `Option${i}`, labels: {}}));

    const expectCall = mockResponse('akeneo_identifier_generator_get_attribute_options', 'GET', {
      ok: true,
      json: page1,
    });
    const {result} = renderHook(() => usePaginatedOptions('brand'), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current?.options?.length).toBeGreaterThan(0);
    });

    expectCall();
    expect(result.current.options).toBeDefined();
    expect(result.current.options).toEqual(page1);

    const page2 = [...Array(10)].map((_, i) => ({code: `Option${i + 20}`, labels: {}}));
    const expectCall2 = mockResponse('akeneo_identifier_generator_get_attribute_options', 'GET', {
      ok: true,
      json: page2,
    });
    act(() => {
      result.current.handleNextPage();
    });
    await waitFor(() => {
      expect(result.current.options?.length).toBeGreaterThan(20);
    });
    expectCall2();
    expect(result.current.options).toBeDefined();
    expect(result.current.options).toEqual([...page1, ...page2]);
  });

  test('it searches options', async () => {
    const page1 = [...Array(20)].map((_, i) => ({code: `Option${i}`, labels: {}}));

    const expectCall = mockResponse('akeneo_identifier_generator_get_attribute_options', 'GET', {
      ok: true,
      json: page1,
    });
    const {result} = renderHook(() => usePaginatedOptions('brand'), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current.isLoading).toBe(false);
    });

    expectCall();
    expect(result.current.options).toBeDefined();
    expect(result.current.options).toEqual(page1);

    const pageSearch = [...Array(3)].map((_, i) => ({code: `Option${i * 2}`, labels: {}}));
    const expectCall2 = mockResponse('akeneo_identifier_generator_get_attribute_options', 'GET', {
      ok: true,
      json: pageSearch,
    });
    act(() => {
      result.current.handleSearchChange('searchedOption');
    });
    await waitFor(() => {
      expect(result.current.options).toHaveLength(3);
    });
    expectCall2();
    expect(result.current.options).toBeDefined();
    expect(result.current.options).toEqual(pageSearch);
  });
});
