import {fetchAttributeGroupsByCode} from '../../../../../../front/src/infrastructure/fetcher/AttributeGroup/attributeGroupsFetcher';

// `pim/fetcher-registry` is the Akeneo legacy Backbone-era fetcher registry.
// It exposes `getFetcher(code)` which returns a fetcher with domain-specific
// methods (search, fetchAll, ...). We mock it per test so that assertions can
// verify which fetcher is requested and with which parameters.
const searchMock = jest.fn();
const getFetcherMock = jest.fn(() => ({search: searchMock}));

jest.mock('pim/fetcher-registry', () => ({
  getFetcher: (...args: unknown[]) => getFetcherMock(...args),
}));

describe('fetchAttributeGroupsByCode', () => {
  let consoleErrorSpy: jest.SpyInstance;

  beforeEach(() => {
    searchMock.mockReset();
    getFetcherMock.mockClear();
    consoleErrorSpy = jest.spyOn(console, 'error').mockImplementation(() => {});
  });

  afterEach(() => {
    consoleErrorSpy.mockRestore();
  });

  test('calls the attribute-group fetcher with joined codes and apply_filters disabled', async () => {
    const groups = {marketing: {code: 'marketing'}, erp: {code: 'erp'}};
    searchMock.mockResolvedValueOnce(groups);

    const result = await fetchAttributeGroupsByCode(['marketing', 'erp']);

    expect(result).toEqual(groups);
    expect(getFetcherMock).toHaveBeenCalledWith('attribute-group');
    expect(searchMock).toHaveBeenCalledWith({
      identifiers: 'marketing,erp',
      apply_filters: false,
    });
  });

  test('joins an empty code list as an empty identifiers string', async () => {
    searchMock.mockResolvedValueOnce({});

    await fetchAttributeGroupsByCode([]);

    expect(searchMock).toHaveBeenCalledWith({identifiers: '', apply_filters: false});
  });

  test('returns an empty object and logs the error when the registry throws', async () => {
    const error = new Error('registry failure');
    getFetcherMock.mockImplementationOnce(() => {
      throw error;
    });

    const result = await fetchAttributeGroupsByCode(['marketing']);

    expect(result).toEqual({});
    expect(consoleErrorSpy).toHaveBeenCalledWith(error);
  });
});
