import {useJobExecution} from './useJobExecution';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {act, waitFor} from '@testing-library/react';

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

afterEach(() => {
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
  jest.useRealTimers();
});

const successResponse = {
  jobInstance: {
    code: 'csv_product_export',
    label: 'Demo CSV product export',
    type: 'export',
  },
  isStoppable: false,
  tracking: {
    error: false,
    warning: false,
    status: 'IN_PROGRESS',
    currentStep: 1,
    totalSteps: 1,
    steps: [
      {
        jobName: 'csv_product_export',
        stepName: 'export',
        status: 'IN_PROGRESS',
        isTrackable: true,
        hasWarning: false,
        hasError: false,
        duration: 14,
        processedItems: 30,
        totalItems: 135,
      },
    ],
  },
  meta: {
    logExists: false,
    archives: {
      output: {
        label: 'pim_enrich.entity.job_execution.module.download.output',
        files: {
          'export_Demo_CSV_product_export_2021-01-05_10-33-34.csv':
            'export/csv_product_export/24/output/export_Demo_CSV_product_export_2021-01-05_10-33-34.csv',
        },
      },
      archive: {
        label: 'pim_enrich.entity.job_execution.module.download.archive',
        files: {
          'export_Demo_CSV_product_export_2021-01-05_10-33-34.zip':
            'export/csv_product_export/24/archive/export_Demo_CSV_product_export_2021-01-05_10-33-34.zip',
        },
      },
      generateZipArchive: false,
    },
  },
};

test('It returns the fetched job execution', async () => {
  jest.useFakeTimers();
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => successResponse,
  }));

  const {result} = renderHookWithProviders(() => useJobExecution('1'));

  await act(async () => {
    jest.advanceTimersByTime(1000);
  });

  await waitFor(() => {
    const [jobExecution] = result.current;
    expect(jobExecution).toEqual(successResponse);
  });

  const [, error, reloadJobExecution] = result.current;
  expect(error).toBeNull();
  expect(reloadJobExecution).not.toBeNull();
});

test('It returns error when fetch return an error', async () => {
  jest.useFakeTimers();
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: false,
    statusText: 'Not found',
    status: 404,
  }));

  const {result} = renderHookWithProviders(() => useJobExecution('1'));

  await act(async () => {
    jest.advanceTimersByTime(1000);
  });

  await waitFor(() => {
    const [, error] = result.current;
    expect(error).not.toBeNull();
  });

  const [jobExecution, error, reloadJobExecution] = result.current;
  expect(jobExecution).toBeNull();
  expect(reloadJobExecution).not.toBeNull();
  expect(error).toEqual({
    statusMessage: 'Not found',
    statusCode: 404,
  });
});

test('It returns callback to reload job execution information', async () => {
  jest.useFakeTimers();
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => successResponse,
  }));

  const {result} = renderHookWithProviders(() => useJobExecution('1'));

  await act(async () => {
    jest.advanceTimersByTime(1000);
  });

  await waitFor(() => {
    const [jobExecution] = result.current;
    expect(jobExecution).toEqual(successResponse);
  });

  expect(global.fetch).toHaveBeenCalled();

  const reloadedResponse = {
    ...successResponse,
    ...{
      tracking: {
        error: false,
        warning: false,
        status: 'COMPLETED',
        currentStep: 1,
        totalSteps: 1,
        steps: [
          {
            jobName: 'csv_product_export',
            stepName: 'export',
            status: 'COMPLETED',
            isTrackable: true,
            hasWarning: false,
            hasError: false,
            duration: 19,
            processedItems: 115,
            totalItems: 135,
          },
        ],
      },
    },
  };

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => reloadedResponse,
  }));

  jest.useRealTimers();

  await act(async () => {
    const [, , reloadJobExecution] = result.current;
    await reloadJobExecution();
  });
  expect(global.fetch).toHaveBeenCalled();

  const [newJobExecution, newError] = result.current;
  expect(newError).toBeNull();
  expect(newJobExecution).toEqual(reloadedResponse);
});

test('It does not fetch a job execution while the previous fetch is not finished', async () => {
  jest.useFakeTimers();
  let resolveFirst: (value: any) => void;
  global.fetch = jest.fn().mockImplementation(
    () =>
      new Promise(resolve => {
        resolveFirst = resolve;
      })
  );

  const {result} = renderHookWithProviders(() => useJobExecution('1'));

  await act(async () => {
    jest.advanceTimersByTime(1000);
  });

  // First fetch is in progress but not resolved
  expect(global.fetch).toHaveBeenCalledTimes(1);

  const [jobExecution, error] = result.current;
  expect(jobExecution).toBeNull();
  expect(error).toBeNull();

  // Resolve the first fetch
  await act(async () => {
    resolveFirst!({
      ok: true,
      json: async () => successResponse,
    });
  });

  // The reloadJobExecution should not trigger a duplicate fetch
  // since isFetching guard prevents it
  expect(global.fetch).toHaveBeenCalledTimes(1);
});
