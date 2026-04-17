import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useUiLocales} from './use-ui-locales';
import {baseFetcher} from '../fetcher/base-fetcher';
import {act} from '@testing-library/react';

jest.mock('../fetcher/base-fetcher');

const mockedBaseFetcher = baseFetcher as jest.MockedFunction<typeof baseFetcher>;

// NOTE: useUiLocales caches the promise at module scope (uiLocalesPromise).
// Because jest.resetModules() + require() causes dual-React issues, we rely on
// test ordering: the first test that triggers baseFetcher caches the result for all
// subsequent tests. We structure assertions accordingly.

test('It returns null before the fetch resolves', () => {
  // This test must run before any resolved-fetch test so the module cache is still null.
  mockedBaseFetcher.mockReturnValue(new Promise(() => {})); // never resolves

  const {result} = renderHookWithProviders(() => useUiLocales());

  expect(result.current).toBeNull();
});

test('It fetches locales via baseFetcher with the correct route and returns them', async () => {
  const locales = [
    {code: 'en_US', label: 'English (United States)', region: 'US', language: 'en'},
    {code: 'fr_FR', label: 'French (France)', region: 'FR', language: 'fr'},
  ];

  mockedBaseFetcher.mockReset();
  mockedBaseFetcher.mockResolvedValue(locales);

  let hookResult: ReturnType<typeof renderHookWithProviders>;
  await act(async () => {
    hookResult = renderHookWithProviders(() => useUiLocales());
  });

  // Verify the fetched locales are returned
  expect(hookResult!.result.current).toEqual(locales);

  // Verify baseFetcher was called with the correct route name
  // (router.generate in test mock returns the route name as-is)
  expect(mockedBaseFetcher).toHaveBeenCalledWith('pim_localization_locale_index');

  // Verify it was called exactly once
  expect(mockedBaseFetcher).toHaveBeenCalledTimes(1);
});
