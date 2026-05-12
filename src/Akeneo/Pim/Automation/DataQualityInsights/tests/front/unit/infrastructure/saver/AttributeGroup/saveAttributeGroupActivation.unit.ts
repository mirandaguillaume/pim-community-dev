import saveAttributeGroupActivation from '../../../../../../front/src/infrastructure/saver/AttributeGroup/saveAttributeGroupActivation';

declare const fetchMock: {
  resetMocks: () => void;
  mockResponseOnce: (body: string) => void;
  mock: {calls: Array<[string, RequestInit]>};
};

jest.mock('routing', () => ({
  generate: (routeName: string) => `/route/${routeName}`,
}));

describe('saveAttributeGroupActivation', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  it('sends a POST request to the correct route', async () => {
    fetchMock.mockResponseOnce('');

    await saveAttributeGroupActivation('marketing', true);

    const [url, options] = fetchMock.mock.calls[0];
    expect(url).toBe('/route/akeneo_data_quality_insights_update_attribute_group_activation');
    expect(options.method).toBe('POST');
  });

  it('sends attribute_group_code and activated in the body', async () => {
    fetchMock.mockResponseOnce('');

    await saveAttributeGroupActivation('marketing', true);

    const [, options] = fetchMock.mock.calls[0];
    expect(options.body).toContain('attribute_group_code=marketing');
    expect(options.body).toContain('activated=true');
  });

  it('sends activated=false when deactivating', async () => {
    fetchMock.mockResponseOnce('');

    await saveAttributeGroupActivation('tech', false);

    const [, options] = fetchMock.mock.calls[0];
    expect(options.body).toContain('activated=false');
  });

  it('URL-encodes special characters in the group code', async () => {
    fetchMock.mockResponseOnce('');

    await saveAttributeGroupActivation('group with spaces', true);

    const [, options] = fetchMock.mock.calls[0];
    expect(options.body).toContain('attribute_group_code=group%20with%20spaces');
  });

  it('sends the correct Content-Type and Accept headers', async () => {
    fetchMock.mockResponseOnce('');

    await saveAttributeGroupActivation('marketing', true);

    const [, options] = fetchMock.mock.calls[0];
    const headers = options.headers as Record<string, string>;
    expect(headers['Content-Type']).toBe('application/x-www-form-urlencoded');
    expect(headers['Accept']).toBe('application/json');
  });
});
