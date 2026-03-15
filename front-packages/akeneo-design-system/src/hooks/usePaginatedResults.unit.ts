import {renderHook, act, waitFor} from '@testing-library/react';
import {usePaginatedResults} from './usePaginatedResults';

const fetcher = jest.fn(
  (page: number) =>
    new Promise<string[]>(resolve => {
      if (2 === page) resolve([]);
      resolve([`nice_item_${page}`]);
    })
);

test('It can fetch paginated results', async () => {
  const {result} = renderHook(() => usePaginatedResults<string>(fetcher, []));

  expect(result.current[0]).toEqual([]);
  expect(fetcher).toHaveBeenCalledWith(0);

  await waitFor(() => {
    expect(result.current[0]).toEqual(['nice_item_0']);
  });

  act(() => {
    result.current[1]();
  });

  await waitFor(() => {
    expect(result.current[0]).toEqual(['nice_item_0', 'nice_item_1']);
  });

  act(() => {
    result.current[1]();
  });

  await waitFor(() => {
    // Page 2 returns empty, so results stay the same
    expect(result.current[0]).toEqual(['nice_item_0', 'nice_item_1']);
  });
});

test('It does not fetch if there is already a fetch running', async () => {
  let resolveFirstFetch: (value: string[]) => void;
  const slowFetcher = jest.fn(
    () =>
      new Promise<string[]>(resolve => {
        resolveFirstFetch = resolve;
      })
  );

  const {result} = renderHook(() => usePaginatedResults<string>(slowFetcher, []));

  // Call handleNextPage before the first fetch resolves
  act(() => {
    result.current[1]();
  });

  // Now resolve the first fetch
  await act(() => {
    resolveFirstFetch!(['nice_item_0']);
  });

  await waitFor(() => {
    expect(result.current[0]).toEqual(['nice_item_0']);
  });
});

test('It does not update results if unmounted', () => {
  const {result, unmount} = renderHook(() => usePaginatedResults<string>(fetcher, []));

  unmount();

  // After unmount, results should remain empty
  expect(result.current[0]).toEqual([]);
});

test('It does not update results if the shouldFetch param is set to false', async () => {
  fetcher.mockClear();
  const {result} = renderHook(() => usePaginatedResults<string>(fetcher, [], false));

  // Give it time to potentially fetch
  await act(async () => {
    await new Promise(resolve => setTimeout(resolve, 0));
  });

  expect(result.current[0]).toEqual([]);
  expect(fetcher).not.toBeCalled();
});

test('It goes back to first page when dependencies change', async () => {
  const {result, rerender} = renderHook(({searchValue}) => usePaginatedResults<string>(fetcher, [searchValue]), {
    initialProps: {searchValue: ''},
  });

  await waitFor(() => {
    expect(result.current[0]).toEqual(['nice_item_0']);
  });

  act(() => {
    result.current[1]();
  });

  await waitFor(() => {
    expect(result.current[0]).toEqual(['nice_item_0', 'nice_item_1']);
  });

  rerender({searchValue: 'nice'});

  await waitFor(() => {
    expect(result.current[0]).toEqual(['nice_item_0']);
  });
});
