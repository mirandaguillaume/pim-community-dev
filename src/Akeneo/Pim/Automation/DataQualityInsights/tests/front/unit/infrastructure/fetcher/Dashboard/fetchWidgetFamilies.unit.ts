import fetchWidgetFamilies from '../../../../../../front/src/infrastructure/fetcher/Dashboard/fetchWidgetFamilies';

declare const fetchMock: {
  resetMocks: () => void;
  mockResponseOnce: (body: string) => void;
  mock: {calls: Array<[string, unknown]>};
};

const generateMock = jest.fn();

jest.mock('routing', () => ({
  generate: (...args: unknown[]) => generateMock(...args),
}));

describe('fetchWidgetFamilies', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    generateMock.mockReset();
    generateMock.mockReturnValue('/fake/widget-families');
  });

  test('calls the widget families route with channel, locale, and family codes', async () => {
    fetchMock.mockResponseOnce(JSON.stringify({shoes: {labels: {en_US: 'Shoes'}}}));

    const result = await fetchWidgetFamilies('ecommerce', 'en_US', ['shoes', 'boots']);

    expect(result).toEqual({shoes: {labels: {en_US: 'Shoes'}}});
    expect(generateMock).toHaveBeenCalledWith('akeneo_data_quality_insights_dashboard_widget_families', {
      channel: 'ecommerce',
      locale: 'en_US',
      families: ['shoes', 'boots'],
    });
  });

  test('passes an empty array when no families are requested', async () => {
    fetchMock.mockResponseOnce(JSON.stringify({}));

    await fetchWidgetFamilies('mobile', 'fr_FR', []);

    expect(generateMock).toHaveBeenCalledWith('akeneo_data_quality_insights_dashboard_widget_families', {
      channel: 'mobile',
      locale: 'fr_FR',
      families: [],
    });
  });
});
