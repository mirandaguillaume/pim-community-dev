import {act, renderHook} from '@testing-library/react';
import useLocales from 'akeneopimstructure/js/attribute-option/hooks/useLocales';
import {DefaultProviders} from '@akeneo-pim-community/shared';

jest.mock('akeneopimstructure/js/attribute-option/fetchers/baseFetcher', () => jest.fn());

import baseFetcher from 'akeneopimstructure/js/attribute-option/fetchers/baseFetcher';

const flushPromises = () => new Promise(setImmediate);

test('it returns an empty array initially', () => {
  (baseFetcher as jest.Mock).mockResolvedValue([]);
  const {result} = renderHook(() => useLocales(), {wrapper: DefaultProviders});
  expect(result.current).toEqual([]);
});

test('it returns locales after the fetch resolves', async () => {
  const locales = [
    {code: 'en_US', label: 'English (United States)'},
    {code: 'fr_FR', label: 'French (France)'},
  ];
  (baseFetcher as jest.Mock).mockResolvedValue(locales);

  const {result} = renderHook(() => useLocales(), {wrapper: DefaultProviders});
  await act(async () => {
    await flushPromises();
  });

  expect(result.current).toEqual(locales);
});
