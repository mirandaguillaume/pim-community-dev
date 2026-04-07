import fetchProductDataQualityEvaluation from '../../../../../../front/src/infrastructure/fetcher/ProductEditForm/fetchProductDataQualityEvaluation';

declare const fetchMock: {
  resetMocks: () => void;
  mockResponseOnce: (body: string) => void;
  mock: {calls: Array<[string, unknown]>};
};

const generateMock = jest.fn();

jest.mock('routing', () => ({
  generate: (...args: unknown[]) => generateMock(...args),
}));

describe('fetchProductDataQualityEvaluation', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    generateMock.mockReset();
    generateMock.mockReturnValue('/fake/product-evaluation');
  });

  test('calls the product evaluation route with the productUuid and returns the parsed payload', async () => {
    const evaluation = {consistency: {ecommerce: {en_US: {rate: {value: 80, rank: 2}, criteria: []}}}};
    fetchMock.mockResponseOnce(JSON.stringify(evaluation));

    const result = await fetchProductDataQualityEvaluation('d3b07384-d9a0-11ec-9d64-0242ac120002');

    expect(result).toEqual(evaluation);
    expect(generateMock).toHaveBeenCalledWith('akeneo_data_quality_insights_product_evaluation', {
      productUuid: 'd3b07384-d9a0-11ec-9d64-0242ac120002',
    });
  });
});
