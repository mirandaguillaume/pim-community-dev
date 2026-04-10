import {fetchAllAttributeGroupsDqiStatus} from '../../../../../../front/src/infrastructure/fetcher/AttributeGroup/attributeGroupDqiStatusFetcher';

// jest-fetch-mock is installed globally via tests/front/unit/jest/fetchMock.ts,
// so `global.fetch` is already a mock and `global.fetchMock` is the same
// reference. We use the `fetchMock` global here (not `jest.mock`) because the
// source code calls the native `fetch()` directly, not a wrapper module.
declare const fetchMock: {
  resetMocks: () => void;
  mockResponseOnce: (body: string) => void;
  mock: {calls: Array<[string, unknown]>};
};

jest.mock('routing', () => ({
  generate: (routeName: string) => `/route/${routeName}`,
}));

describe('attributeGroupDqiStatusFetcher', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  describe('fetchAllAttributeGroupsDqiStatus', () => {
    test('calls the configured route and returns the parsed JSON payload', async () => {
      const fakePayload = {marketing: true, erp: false};
      fetchMock.mockResponseOnce(JSON.stringify(fakePayload));

      const result = await fetchAllAttributeGroupsDqiStatus();

      expect(result).toEqual(fakePayload);
      expect(fetchMock.mock.calls).toHaveLength(1);
      expect(fetchMock.mock.calls[0][0]).toBe(
        '/route/akeneo_data_quality_insights_get_all_attribute_groups_activation'
      );
    });

    test('returns an empty object when the backend responds with an empty payload', async () => {
      fetchMock.mockResponseOnce(JSON.stringify({}));

      await expect(fetchAllAttributeGroupsDqiStatus()).resolves.toEqual({});
    });

    test('propagates the rejection when fetch fails', async () => {
      const error = new Error('network unreachable');
      fetchMock.mockResponseOnce(() => Promise.reject(error) as never);

      await expect(fetchAllAttributeGroupsDqiStatus()).rejects.toThrow('network unreachable');
    });
  });
});
