import fetchAttributeGroupStatus from '../../../../../../front/src/infrastructure/fetcher/AttributeGroup/fetchAttributeGroupStatus';

declare const fetchMock: {
  resetMocks: () => void;
  mockResponseOnce: (body: string) => void;
  mock: {calls: Array<[string, unknown]>};
};

jest.mock('routing', () => ({
  generate: (routeName: string, params: Record<string, unknown>) =>
    `/route/${routeName}?${new URLSearchParams(params as Record<string, string>).toString()}`,
}));

describe('fetchAttributeGroupStatus', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  test('calls the backend with the attribute group code and returns the parsed payload', async () => {
    fetchMock.mockResponseOnce(JSON.stringify({active: true}));

    const result = await fetchAttributeGroupStatus('marketing');

    expect(result).toEqual({active: true});
    expect(fetchMock.mock.calls[0][0]).toBe(
      '/route/akeneo_data_quality_insights_get_attribute_group_activation?attributeGroupCode=marketing'
    );
  });

  test('passes the attribute group code verbatim (no transformation)', async () => {
    fetchMock.mockResponseOnce(JSON.stringify({active: false}));

    await fetchAttributeGroupStatus('erp_values_with_underscores');

    expect(fetchMock.mock.calls[0][0]).toContain('attributeGroupCode=erp_values_with_underscores');
  });
});
