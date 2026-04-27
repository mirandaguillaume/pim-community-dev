import {renderHookWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import fetchMock from 'jest-fetch-mock';
import {act} from '@testing-library/react';
import {usePimVersion} from './usePimVersion';

const flushPromises = () => new Promise(setImmediate);

beforeEach(() => {
  sessionStorage.clear();
  fetchMock.resetMocks();
});

test('it returns empty version and lastPatch initially', () => {
  fetchMock.mockResponseOnce(
    JSON.stringify({version: '', is_last_patch_displayed: false, is_analytics_wanted: false, analytics_url: ''})
  );

  const {result} = renderHookWithProviders(() => usePimVersion());
  expect(result.current.version).toBe('');
  expect(result.current.lastPatch).toBe('');
});

test('it sets version from the API response', async () => {
  fetchMock.mockResponseOnce(
    JSON.stringify({
      version: 'Community Edition 6.0.1 build',
      is_last_patch_displayed: false,
      is_analytics_wanted: false,
      analytics_url: '',
    })
  );

  const {result} = renderHookWithProviders(() => usePimVersion());
  await act(async () => {
    await flushPromises();
  });

  expect(result.current.version).toBe('Community Edition 6.0.1 build');
  expect(result.current.lastPatch).toBe('');
});

test('it sets lastPatch from sessionStorage when current version is outdated', async () => {
  sessionStorage.setItem('last-patch-available', 'v6.0.10');
  fetchMock.mockResponseOnce(
    JSON.stringify({
      version: 'Community Edition 6.0.1 build',
      is_last_patch_displayed: true,
      is_analytics_wanted: false,
      analytics_url: '',
    })
  );

  const {result} = renderHookWithProviders(() => usePimVersion());
  await act(async () => {
    await flushPromises();
  });

  expect(result.current.lastPatch).toBe('v6.0.10');
});

test('it does not set lastPatch when current version is up to date', async () => {
  sessionStorage.setItem('last-patch-available', 'v6.0.1');
  fetchMock.mockResponseOnce(
    JSON.stringify({
      version: 'Community Edition 6.0.10 build',
      is_last_patch_displayed: true,
      is_analytics_wanted: false,
      analytics_url: '',
    })
  );

  const {result} = renderHookWithProviders(() => usePimVersion());
  await act(async () => {
    await flushPromises();
  });

  expect(result.current.lastPatch).toBe('');
});

test('it does not set lastPatch when is_last_patch_displayed is false', async () => {
  sessionStorage.setItem('last-patch-available', 'v6.0.99');
  fetchMock.mockResponseOnce(
    JSON.stringify({
      version: 'Community Edition 6.0.1 build',
      is_last_patch_displayed: false,
      is_analytics_wanted: false,
      analytics_url: '',
    })
  );

  const {result} = renderHookWithProviders(() => usePimVersion());
  await act(async () => {
    await flushPromises();
  });

  expect(result.current.lastPatch).toBe('');
});

test('it does not set lastPatch when sessionStorage has no stored patch', async () => {
  fetchMock.mockResponseOnce(
    JSON.stringify({
      version: 'Community Edition 6.0.1 build',
      is_last_patch_displayed: true,
      is_analytics_wanted: false,
      analytics_url: '',
    })
  );

  const {result} = renderHookWithProviders(() => usePimVersion());
  await act(async () => {
    await flushPromises();
  });

  expect(result.current.lastPatch).toBe('');
});
