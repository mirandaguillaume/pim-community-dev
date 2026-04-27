import {renderHookWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import fetchMock from 'jest-fetch-mock';
import {act} from '@testing-library/react';
import {useDashboardCompleteness} from './useDashboardCompleteness';

const flushPromises = () => new Promise(setImmediate);

beforeEach(() => {
  fetchMock.resetMocks();
});

test('it returns null initially', () => {
  fetchMock.mockResponseOnce(JSON.stringify({}), {status: 200});
  const {result} = renderHookWithProviders(() => useDashboardCompleteness('en_US'));
  expect(result.current).toBeNull();
});

test('it fetches and returns converted completeness data', async () => {
  const backendData = {
    ecommerce: {
      labels: {en_US: 'Ecommerce', fr_FR: 'Ecommerce FR'},
      total: 100,
      complete: 75,
      locales: {'English (United States)': 75},
    },
  };
  fetchMock.mockResponseOnce(JSON.stringify(backendData), {status: 200});

  const {result} = renderHookWithProviders(() => useDashboardCompleteness('en_US'));
  await act(async () => {
    await flushPromises();
  });

  expect(result.current).toEqual({
    Ecommerce: {
      channelRatio: 75,
      localesRatios: {'English (United States)': 75},
    },
  });
});

test('it returns null when the fetch throws an error', async () => {
  fetchMock.mockRejectOnce(new Error('Network error'));

  const {result} = renderHookWithProviders(() => useDashboardCompleteness('en_US'));
  await act(async () => {
    await flushPromises();
  });

  expect(result.current).toBeNull();
});

test('it re-fetches when catalogLocale changes', async () => {
  const backendDataEn = {
    ecommerce: {labels: {en_US: 'Ecommerce', fr_FR: 'Ecommerce FR'}, total: 100, complete: 50, locales: {}},
  };
  const backendDataFr = {
    ecommerce: {labels: {en_US: 'Ecommerce', fr_FR: 'Ecommerce FR'}, total: 100, complete: 80, locales: {}},
  };
  fetchMock.mockResponseOnce(JSON.stringify(backendDataEn), {status: 200});
  fetchMock.mockResponseOnce(JSON.stringify(backendDataFr), {status: 200});

  let locale = 'en_US';
  const {rerender} = renderHookWithProviders(() => useDashboardCompleteness(locale));
  await act(async () => {
    await flushPromises();
  });

  locale = 'fr_FR';
  rerender();
  await act(async () => {
    await flushPromises();
  });

  expect(fetchMock).toHaveBeenCalledTimes(2);
});
