import fetchCategoryTrees from '../../../../../../front/src/infrastructure/fetcher/Dashboard/fetchCategoryTrees';

const fetchAllMock = jest.fn();
const getFetcherMock = jest.fn(() => ({fetchAll: fetchAllMock}));

jest.mock('pim/fetcher-registry', () => ({
  getFetcher: (...args: unknown[]) => getFetcherMock(...args),
}));

describe('fetchCategoryTrees', () => {
  beforeEach(() => {
    fetchAllMock.mockReset();
    getFetcherMock.mockClear();
  });

  test('asks the category fetcher for all trees and returns the result verbatim', async () => {
    const trees = [{code: 'master'}, {code: 'web'}];
    fetchAllMock.mockResolvedValueOnce(trees);

    const result = await fetchCategoryTrees();

    expect(result).toEqual(trees);
    expect(getFetcherMock).toHaveBeenCalledWith('category');
    expect(fetchAllMock).toHaveBeenCalledTimes(1);
    expect(fetchAllMock).toHaveBeenCalledWith();
  });
});
