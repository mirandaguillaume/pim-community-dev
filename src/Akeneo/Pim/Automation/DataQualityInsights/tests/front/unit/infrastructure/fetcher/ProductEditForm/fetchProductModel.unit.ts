import fetchProductModel from '../../../../../../front/src/infrastructure/fetcher/ProductEditForm/fetchProductModel';

declare const fetchMock: {
  resetMocks: () => void;
  mockResponseOnce: (body: string) => void;
  mock: {calls: Array<[string, unknown]>};
};

const generateMock = jest.fn();

jest.mock('routing', () => ({
  generate: (...args: unknown[]) => generateMock(...args),
}));

describe('fetchProductModel', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    generateMock.mockReset();
    generateMock.mockReturnValue('/fake/product-model');
  });

  test('calls the product model REST route with the id and returns the parsed payload', async () => {
    const productModel = {code: 'shirt-model', family_variant: 'shirts_color'};
    fetchMock.mockResponseOnce(JSON.stringify(productModel));

    const result = await fetchProductModel('42');

    expect(result).toEqual(productModel);
    expect(generateMock).toHaveBeenCalledWith('pim_enrich_product_model_rest_get', {id: '42'});
  });
});
