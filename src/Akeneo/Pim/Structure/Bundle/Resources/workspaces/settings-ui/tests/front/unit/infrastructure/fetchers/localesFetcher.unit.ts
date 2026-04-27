import {
  fetchAllLocales,
  fetchActivatedLocales,
} from '@akeneo-pim-community/settings-ui/src/infrastructure/fetchers/localesFetcher';

const FetcherRegistry = require('pim/fetcher-registry');

jest.mock('pim/fetcher-registry');

const locales = [
  {code: 'en_US', label: 'English (United States)'},
  {code: 'fr_FR', label: 'French (France)'},
];

beforeEach(() => {
  jest.clearAllMocks();
});

test('fetchAllLocales returns all locales from the fetcher', async () => {
  FetcherRegistry.getFetcher = jest.fn().mockReturnValue({fetchAll: jest.fn().mockResolvedValue(locales)});

  const result = await fetchAllLocales();

  expect(FetcherRegistry.getFetcher).toHaveBeenCalledWith('locale');
  expect(result).toEqual(locales);
});

test('fetchAllLocales returns an empty array when the fetcher throws', async () => {
  FetcherRegistry.getFetcher = jest.fn().mockReturnValue({
    fetchAll: jest.fn().mockRejectedValue(new Error('Fetcher error')),
  });

  const result = await fetchAllLocales();

  expect(result).toEqual([]);
});

test('fetchActivatedLocales returns activated locales from the fetcher', async () => {
  FetcherRegistry.getFetcher = jest.fn().mockReturnValue({
    fetchActivated: jest.fn().mockResolvedValue(locales),
  });

  const result = await fetchActivatedLocales();

  expect(FetcherRegistry.getFetcher).toHaveBeenCalledWith('locale');
  expect(result).toEqual(locales);
});

test('fetchActivatedLocales returns an empty array when the fetcher throws', async () => {
  FetcherRegistry.getFetcher = jest.fn().mockReturnValue({
    fetchActivated: jest.fn().mockRejectedValue(new Error('Fetcher error')),
  });

  const result = await fetchActivatedLocales();

  expect(result).toEqual([]);
});
