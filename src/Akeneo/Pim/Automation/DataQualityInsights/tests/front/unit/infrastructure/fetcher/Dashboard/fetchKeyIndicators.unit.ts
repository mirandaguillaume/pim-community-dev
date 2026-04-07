import {fetchKeyIndicators} from '../../../../../../front/src/infrastructure/fetcher/Dashboard/fetchKeyIndicators';

declare const fetchMock: {
  resetMocks: () => void;
  mockResponseOnce: (body: string | (() => Promise<never>)) => void;
  mock: {calls: Array<[string, unknown]>};
};

const generateMock = jest.fn();

jest.mock('routing', () => ({
  generate: (...args: unknown[]) => generateMock(...args),
}));

describe('fetchKeyIndicators', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    generateMock.mockReset();
    generateMock.mockReturnValue('/fake/key-indicators');
  });

  test('returns the parsed payload on success', async () => {
    const payload = {has_image: {products: {totalGood: 10, totalToImprove: 3}}};
    fetchMock.mockResponseOnce(JSON.stringify(payload));

    const result = await fetchKeyIndicators('ecommerce', 'en_US', 'shoes', 'apparel');

    expect(result).toEqual(payload);
    expect(generateMock).toHaveBeenCalledWith('akeneo_data_quality_insights_dashboard_key_indicators', {
      channel: 'ecommerce',
      locale: 'en_US',
      family: 'shoes',
      category: 'apparel',
    });
  });

  test('returns null when fetch rejects (defensive catch-all)', async () => {
    fetchMock.mockResponseOnce(() => Promise.reject(new Error('network down')));

    const result = await fetchKeyIndicators('ecommerce', 'en_US', null, null);

    expect(result).toBeNull();
  });

  test('accepts null family and category', async () => {
    fetchMock.mockResponseOnce(JSON.stringify({}));

    await fetchKeyIndicators('mobile', 'fr_FR', null, null);

    expect(generateMock).toHaveBeenCalledWith('akeneo_data_quality_insights_dashboard_key_indicators', {
      channel: 'mobile',
      locale: 'fr_FR',
      family: null,
      category: null,
    });
  });
});
