import fetchFamilyInformation from '../../../../../../front/src/infrastructure/fetcher/ProductEditForm/fetchFamilyInformation';

declare const fetchMock: {
  resetMocks: () => void;
  mockResponseOnce: (body: string) => void;
  mock: {calls: Array<[string, unknown]>};
};

const generateMock = jest.fn();

jest.mock('routing', () => ({
  generate: (...args: unknown[]) => generateMock(...args),
}));

describe('fetchFamilyInformation', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    generateMock.mockReset();
    generateMock.mockReturnValue('/fake/family');
  });

  test('calls the family REST route with the identifier and returns the parsed payload', async () => {
    const family = {code: 'shoes', attributes: [], labels: {en_US: 'Shoes'}};
    fetchMock.mockResponseOnce(JSON.stringify(family));

    const result = await fetchFamilyInformation('shoes');

    expect(result).toEqual(family);
    expect(generateMock).toHaveBeenCalledWith('pim_enrich_family_rest_get', {identifier: 'shoes'});
  });

  test('passes the family identifier verbatim', async () => {
    fetchMock.mockResponseOnce(JSON.stringify({}));

    await fetchFamilyInformation('tshirt_family_variant');

    expect(generateMock).toHaveBeenCalledWith('pim_enrich_family_rest_get', {identifier: 'tshirt_family_variant'});
  });
});
