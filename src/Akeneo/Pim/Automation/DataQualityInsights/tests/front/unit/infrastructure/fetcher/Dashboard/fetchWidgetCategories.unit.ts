import fetchWidgetCategories from '../../../../../../front/src/infrastructure/fetcher/Dashboard/fetchWidgetCategories';

declare const fetchMock: {
  resetMocks: () => void;
  mockResponseOnce: (body: string) => void;
  mock: {calls: Array<[string, unknown]>};
};

const generateMock = jest.fn();

jest.mock('routing', () => ({
  generate: (...args: unknown[]) => generateMock(...args),
}));

describe('fetchWidgetCategories', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    generateMock.mockReset();
    generateMock.mockReturnValue('/fake/widget-categories');
  });

  test('calls the widget categories route with channel, locale, and category codes', async () => {
    fetchMock.mockResponseOnce(JSON.stringify({apparel: {labels: {en_US: 'Apparel'}}}));

    const result = await fetchWidgetCategories('ecommerce', 'en_US', ['apparel', 'shoes']);

    expect(result).toEqual({apparel: {labels: {en_US: 'Apparel'}}});
    expect(generateMock).toHaveBeenCalledWith('akeneo_data_quality_insights_dashboard_widget_categories', {
      channel: 'ecommerce',
      locale: 'en_US',
      categories: ['apparel', 'shoes'],
    });
  });

  test('passes an empty array when no categories are requested', async () => {
    fetchMock.mockResponseOnce(JSON.stringify({}));

    await fetchWidgetCategories('mobile', 'fr_FR', []);

    expect(generateMock).toHaveBeenCalledWith('akeneo_data_quality_insights_dashboard_widget_categories', {
      channel: 'mobile',
      locale: 'fr_FR',
      categories: [],
    });
  });
});
