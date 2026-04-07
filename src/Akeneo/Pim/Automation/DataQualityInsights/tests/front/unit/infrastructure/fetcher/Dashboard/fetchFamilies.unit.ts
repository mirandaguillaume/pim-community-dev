import fetchFamilies from '../../../../../../front/src/infrastructure/fetcher/Dashboard/fetchFamilies';

const fetchAllMock = jest.fn();
const getFetcherMock = jest.fn(() => ({fetchAll: fetchAllMock}));

jest.mock('pim/fetcher-registry', () => ({
  getFetcher: (...args: unknown[]) => getFetcherMock(...args),
}));

describe('fetchFamilies', () => {
  beforeEach(() => {
    fetchAllMock.mockReset();
    getFetcherMock.mockClear();
  });

  test('asks the family fetcher for all families with expanded option disabled', async () => {
    const families = [{code: 'shoes'}, {code: 'tshirts'}];
    fetchAllMock.mockResolvedValueOnce(families);

    const result = await fetchFamilies();

    expect(result).toEqual(families);
    expect(getFetcherMock).toHaveBeenCalledWith('family');
    expect(fetchAllMock).toHaveBeenCalledWith({options: {expanded: 0}});
  });
});
