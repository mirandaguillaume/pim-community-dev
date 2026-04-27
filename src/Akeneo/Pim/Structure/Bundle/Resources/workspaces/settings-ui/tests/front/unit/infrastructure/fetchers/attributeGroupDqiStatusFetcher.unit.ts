import fetchMock from 'jest-fetch-mock';
import {fetchAllAttributeGroupsDqiStatus} from '@akeneo-pim-community/settings-ui/src/infrastructure/fetchers/attributeGroupDqiStatusFetcher';

beforeEach(() => fetchMock.resetMocks());

test('it fetches and returns all attribute groups DQI status', async () => {
  const dqiStatus = {marketing: true, other: false, technical: true};
  fetchMock.mockResponseOnce(JSON.stringify(dqiStatus));

  const result = await fetchAllAttributeGroupsDqiStatus();

  expect(result).toEqual(dqiStatus);
  expect(fetchMock).toHaveBeenCalledTimes(1);
});

test('it throws when the fetch fails', async () => {
  fetchMock.mockRejectOnce(new Error('Network error'));

  await expect(fetchAllAttributeGroupsDqiStatus()).rejects.toThrow('Network error');
});
