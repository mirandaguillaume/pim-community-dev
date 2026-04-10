import fetchProductModelEvaluation from '../../../../../../front/src/infrastructure/fetcher/ProductEditForm/fetchProductModelEvaluation';

declare const fetchMock: {
  resetMocks: () => void;
  mockResponseOnce: (body: string) => void;
  mock: {calls: Array<[string, unknown]>};
};

const generateMock = jest.fn();

jest.mock('routing', () => ({
  generate: (...args: unknown[]) => generateMock(...args),
}));

describe('fetchProductModelEvaluation', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    generateMock.mockReset();
    generateMock.mockReturnValue('/fake/product-model-evaluation');
  });

  test('calls the product model evaluation route with the productModelId and returns the parsed payload', async () => {
    const evaluation = {enrichment: {ecommerce: {en_US: {rate: {value: 90, rank: 1}, criteria: []}}}};
    fetchMock.mockResponseOnce(JSON.stringify(evaluation));

    const result = await fetchProductModelEvaluation('42');

    expect(result).toEqual(evaluation);
    expect(generateMock).toHaveBeenCalledWith('akeneo_data_quality_insights_product_model_evaluation', {
      productModelId: '42',
    });
  });
});
