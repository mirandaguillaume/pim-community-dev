import fetchCategoryChildren from '../../../../../../front/src/infrastructure/fetcher/Dashboard/fetchCategoryChildren';

declare const fetchMock: {
  resetMocks: () => void;
  mockResponseOnce: (body: string) => void;
  mock: {calls: Array<[string, unknown]>};
};

const generateMock = jest.fn();

jest.mock('routing', () => ({
  generate: (...args: unknown[]) => generateMock(...args),
}));

describe('fetchCategoryChildren', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    generateMock.mockReset();
    generateMock.mockReturnValue('/fake/url');
  });

  test('calls the category tree children route with the expected parameters', async () => {
    fetchMock.mockResponseOnce(JSON.stringify([{id: 1}, {id: 2}]));

    const result = await fetchCategoryChildren('en_US', '42');

    expect(result).toEqual([{id: 1}, {id: 2}]);
    expect(generateMock).toHaveBeenCalledTimes(1);
    expect(generateMock).toHaveBeenCalledWith('pim_enrich_categorytree_children', {
      _format: 'json',
      dataLocale: 'en_US',
      context: 'associate',
      id: '42',
      include_parent: false,
    });
  });

  test('passes the resolved URL to fetch', async () => {
    generateMock.mockReturnValue('/resolved/category/children/url');
    fetchMock.mockResponseOnce(JSON.stringify([]));

    await fetchCategoryChildren('fr_FR', '7');

    expect(fetchMock.mock.calls[0][0]).toBe('/resolved/category/children/url');
  });
});
