import fetchProduct from '../../../../../../front/src/infrastructure/fetcher/ProductEditForm/fetchProduct';

declare const fetchMock: {
  resetMocks: () => void;
  mockResponseOnce: (body: string) => void;
  mock: {calls: Array<[string, unknown]>};
};

const generateMock = jest.fn();

jest.mock('routing', () => ({
  generate: (...args: unknown[]) => generateMock(...args),
}));

describe('fetchProduct', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    generateMock.mockReset();
    generateMock.mockReturnValue('/fake/product');
  });

  test('calls the product REST route with the uuid and returns the parsed payload', async () => {
    const product = {identifier: 'sku-42', enabled: true};
    fetchMock.mockResponseOnce(JSON.stringify(product));

    const result = await fetchProduct('d3b07384-d9a0-11ec-9d64-0242ac120002');

    expect(result).toEqual(product);
    expect(generateMock).toHaveBeenCalledWith('pim_enrich_product_rest_get', {
      uuid: 'd3b07384-d9a0-11ec-9d64-0242ac120002',
    });
  });
});
