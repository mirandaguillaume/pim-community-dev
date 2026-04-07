import fetchDqiDashboardData from '../../../../../../front/src/infrastructure/fetcher/Dashboard/fetchDqiDashboardData';

declare const fetchMock: {
  resetMocks: () => void;
  mockResponseOnce: (body: string) => void;
  mock: {calls: Array<[string, unknown]>};
};

const generateMock = jest.fn();

jest.mock('routing', () => ({
  generate: (...args: unknown[]) => generateMock(...args),
}));

describe('fetchDqiDashboardData', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    generateMock.mockReset();
    generateMock.mockReturnValue('/fake/dashboard-overview');
  });

  test('calls the dashboard overview route with channel, locale, period, family, and category', async () => {
    fetchMock.mockResponseOnce(JSON.stringify({products: {totalGood: 5, totalToImprove: 2}}));

    const result = await fetchDqiDashboardData('ecommerce', 'en_US', 'weekly', 'shoes', 'apparel');

    expect(result).toEqual({products: {totalGood: 5, totalToImprove: 2}});
    expect(generateMock).toHaveBeenCalledWith('akeneo_data_quality_insights_dashboard_overview', {
      channel: 'ecommerce',
      locale: 'en_US',
      timePeriod: 'weekly',
      family: 'shoes',
      category: 'apparel',
    });
  });

  test('propagates null family and category unchanged (no coercion)', async () => {
    fetchMock.mockResponseOnce(JSON.stringify({}));

    await fetchDqiDashboardData('print', 'de_DE', 'daily', null, null);

    expect(generateMock).toHaveBeenCalledWith('akeneo_data_quality_insights_dashboard_overview', {
      channel: 'print',
      locale: 'de_DE',
      timePeriod: 'daily',
      family: null,
      category: null,
    });
  });
});
