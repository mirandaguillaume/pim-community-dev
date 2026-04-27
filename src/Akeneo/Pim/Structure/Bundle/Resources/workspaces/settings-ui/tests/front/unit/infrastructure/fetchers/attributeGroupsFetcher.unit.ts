import {fetchAllAttributeGroups} from '@akeneo-pim-community/settings-ui/src/infrastructure/fetchers/attributeGroupsFetcher';
import {anAttributeGroup} from '../../../utils/provideAttributeGroupHelper';

const FetcherRegistry = require('pim/fetcher-registry');

jest.mock('pim/fetcher-registry');

beforeEach(() => {
  jest.clearAllMocks();
});

test('fetchAllAttributeGroups returns all attribute groups from the fetcher', async () => {
  const groups = {
    other: anAttributeGroup('other', 1, {en_US: 'Other'}, 0),
    marketing: anAttributeGroup('marketing', 2, {en_US: 'Marketing'}, 1),
  };
  FetcherRegistry.getFetcher = jest.fn().mockReturnValue({fetchAll: jest.fn().mockResolvedValue(groups)});

  const result = await fetchAllAttributeGroups();

  expect(FetcherRegistry.getFetcher).toHaveBeenCalledWith('attribute-group');
  expect(result).toEqual(groups);
});

test('fetchAllAttributeGroups returns an empty object when the fetcher throws', async () => {
  FetcherRegistry.getFetcher = jest.fn().mockReturnValue({
    fetchAll: jest.fn().mockRejectedValue(new Error('Fetcher error')),
  });

  const result = await fetchAllAttributeGroups();

  expect(result).toEqual({});
});
