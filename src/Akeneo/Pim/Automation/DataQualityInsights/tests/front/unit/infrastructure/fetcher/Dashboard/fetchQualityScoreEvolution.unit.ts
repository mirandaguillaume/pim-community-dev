import {fetchQualityScoreEvolution} from '../../../../../../front/src/infrastructure/fetcher/Dashboard/fetchQualityScoreEvolution';

declare const fetchMock: {
  resetMocks: () => void;
  mockResponseOnce: (body: string) => void;
  mock: {calls: Array<[string, unknown]>};
};

const generateMock = jest.fn();

jest.mock('routing', () => ({
  generate: (...args: unknown[]) => generateMock(...args),
}));

describe('fetchQualityScoreEvolution', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    generateMock.mockReset();
    generateMock.mockReturnValue('/fake/quality-score-evolution');
  });

  test('calls the evolution route with channel, locale, family, and category', async () => {
    fetchMock.mockResponseOnce(JSON.stringify({rank_1: {'2026-01-01': {x: '01', y: 42}}}));

    const result = await fetchQualityScoreEvolution('ecommerce', 'en_US', 'shoes', 'apparel');

    expect(result).toEqual({rank_1: {'2026-01-01': {x: '01', y: 42}}});
    expect(generateMock).toHaveBeenCalledWith('akeneo_data_quality_insights_dashboard_quality_score_evolution', {
      channel: 'ecommerce',
      locale: 'en_US',
      family: 'shoes',
      category: 'apparel',
    });
  });

  test('accepts null family and category', async () => {
    fetchMock.mockResponseOnce(JSON.stringify({}));

    await fetchQualityScoreEvolution('print', 'de_DE', null, null);

    expect(generateMock).toHaveBeenCalledWith('akeneo_data_quality_insights_dashboard_quality_score_evolution', {
      channel: 'print',
      locale: 'de_DE',
      family: null,
      category: null,
    });
  });
});
