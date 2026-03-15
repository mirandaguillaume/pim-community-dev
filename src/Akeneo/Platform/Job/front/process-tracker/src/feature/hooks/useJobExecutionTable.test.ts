import {useJobExecutionTable} from './useJobExecutionTable';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {JobExecutionTable, getDefaultJobExecutionFilter} from '../models';
import {act, waitFor} from '@testing-library/react';

const expectedFetchedJobExecutionTable: JobExecutionTable = {
  rows: [],
  matches_count: 0,
};

let mockedDocumentVisibility = true;
jest.mock('@akeneo-pim-community/shared/lib/hooks/useDocumentVisibility', () => ({
  useDocumentVisibility: (): boolean => mockedDocumentVisibility,
}));

beforeEach(() => {
  mockedDocumentVisibility = true;
  global.fetch = jest.fn().mockImplementation(async () => ({
    json: async () => expectedFetchedJobExecutionTable,
  }));
});

afterEach(() => {
  jest.useRealTimers();
});

test('It fetches job execution table', async () => {
  const defaultFilter = getDefaultJobExecutionFilter();
  const {result} = renderHookWithProviders(() => useJobExecutionTable(defaultFilter));

  await waitFor(() => {
    expect(result.current[0]).toEqual(expectedFetchedJobExecutionTable);
  });
});

test('It can refresh job execution table', async () => {
  const defaultFilter = getDefaultJobExecutionFilter();
  const {result} = renderHookWithProviders(() => useJobExecutionTable(defaultFilter));

  await waitFor(() => {
    expect(result.current[0]).toEqual(expectedFetchedJobExecutionTable);
  });

  expect(global.fetch).toBeCalledTimes(1);

  await act(async () => {
    const [, refreshJobExecutionTable] = result.current;
    await refreshJobExecutionTable();
  });

  expect(global.fetch).toBeCalledTimes(2);
});

test('It returns job execution table only if hook is mounted', async () => {
  const defaultFilter = getDefaultJobExecutionFilter();
  const {result, unmount} = renderHookWithProviders(() => useJobExecutionTable(defaultFilter));

  unmount();

  const [jobExecutionTable] = result.current;
  expect(jobExecutionTable).toEqual(null);
});

test('It does not fetch a job execution table while the previous fetch is not finished', async () => {
  let resolveFirst: (value: any) => void;
  global.fetch = jest.fn().mockImplementation(
    () =>
      new Promise(resolve => {
        resolveFirst = resolve;
      })
  );

  const filter = getDefaultJobExecutionFilter();
  const {result} = renderHookWithProviders(() => useJobExecutionTable(filter));

  expect(global.fetch).toHaveBeenCalledTimes(1);

  const [jobExecutionTable] = result.current;
  expect(jobExecutionTable).toBeNull();

  // Trying to refresh while fetch is pending should not trigger another fetch
  await act(async () => {
    const [, refreshJobExecutionTable] = result.current;
    await refreshJobExecutionTable();
  });
  expect(global.fetch).toHaveBeenCalledTimes(1);

  // Resolve pending fetch
  await act(async () => {
    resolveFirst!({json: async () => expectedFetchedJobExecutionTable});
  });
});

test('It automatically refreshes the job execution table', async () => {
  jest.useFakeTimers();

  const filter = getDefaultJobExecutionFilter();
  const {result} = renderHookWithProviders(() => useJobExecutionTable(filter));

  // Initial fetch resolves immediately (mocked)
  await act(async () => {
    await Promise.resolve();
  });

  expect(global.fetch).toHaveBeenCalledTimes(1);
  expect(result.current[0]).toBe(expectedFetchedJobExecutionTable);

  // Advance past the auto-refresh interval (5000ms)
  await act(async () => {
    jest.advanceTimersByTime(5000);
  });

  await act(async () => {
    await Promise.resolve();
  });

  expect(global.fetch).toHaveBeenCalledTimes(2);
  expect(result.current[0]).toBe(expectedFetchedJobExecutionTable);
});

test('It does not automatically refresh the job execution table when told', async () => {
  jest.useFakeTimers();

  const filter = getDefaultJobExecutionFilter();
  const {result} = renderHookWithProviders(() => useJobExecutionTable(filter, false));

  await act(async () => {
    await Promise.resolve();
  });

  expect(global.fetch).toHaveBeenCalledTimes(1);
  expect(result.current[0]).toBe(expectedFetchedJobExecutionTable);

  await act(async () => {
    jest.advanceTimersByTime(5000);
  });

  expect(global.fetch).toHaveBeenCalledTimes(1);
  expect(result.current[0]).toBe(expectedFetchedJobExecutionTable);
});

test('It does not refresh the job execution table when document is not visible', async () => {
  jest.useFakeTimers();
  mockedDocumentVisibility = false;

  const filter = getDefaultJobExecutionFilter();
  const {result} = renderHookWithProviders(() => useJobExecutionTable(filter));

  await act(async () => {
    await Promise.resolve();
  });

  expect(global.fetch).toHaveBeenCalledTimes(1);

  await waitFor(() => {
    expect(result.current[0]).toBe(expectedFetchedJobExecutionTable);
  });

  await act(async () => {
    jest.advanceTimersByTime(5000);
  });

  expect(global.fetch).toHaveBeenCalledTimes(1);
});
