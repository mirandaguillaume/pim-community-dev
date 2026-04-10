import {fetchQualityScore} from '../../../../../../front/src/infrastructure/fetcher/ProductEditForm/fetchQualityScore';

declare const fetchMock: {
  resetMocks: () => void;
  mockResponseOnce: (body: string) => void;
  mock: {calls: Array<[string, unknown]>};
};

const generateMock = jest.fn();

jest.mock('routing', () => ({
  generate: (...args: unknown[]) => generateMock(...args),
}));

describe('fetchQualityScore', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    generateMock.mockReset();
  });

  test('calls the product quality score route with productUuid when type is product', async () => {
    generateMock.mockReturnValue('/fake/product-score');
    fetchMock.mockResponseOnce(JSON.stringify({evaluations_available: true, scores: {ecommerce: {en_US: 'A'}}}));

    const result = await fetchQualityScore('product', 'd3b07384-d9a0-11ec-9d64-0242ac120002');

    expect(result).toEqual({evaluations_available: true, scores: {ecommerce: {en_US: 'A'}}});
    expect(generateMock).toHaveBeenCalledWith('akeneo_data_quality_insights_product_quality_score', {
      productUuid: 'd3b07384-d9a0-11ec-9d64-0242ac120002',
    });
  });

  test('calls the product model quality score route with productId when type is product_model', async () => {
    generateMock.mockReturnValue('/fake/product-model-score');
    fetchMock.mockResponseOnce(JSON.stringify({evaluations_available: false}));

    const result = await fetchQualityScore('product_model', '42');

    expect(result).toEqual({evaluations_available: false});
    expect(generateMock).toHaveBeenCalledWith('akeneo_data_quality_insights_product_model_quality_score', {
      productId: '42',
    });
  });

  test('returns the full payload shape when evaluations are available', async () => {
    generateMock.mockReturnValue('/fake/score');
    const payload = {
      evaluations_available: true as const,
      scores: {channel_web: {en_US: 'A' as const, fr_FR: 'E' as const}},
    };
    fetchMock.mockResponseOnce(JSON.stringify(payload));

    await expect(fetchQualityScore('product', 'some-uuid')).resolves.toEqual(payload);
  });
});
