import {renderHook, waitFor} from '@testing-library/react';
import {useFetchQualityScoreEvolution} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks/Dashboard/useFetchQualityScoreEvolution';
import {fetchQualityScoreEvolution} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher';

jest.mock('@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher');

const mockedFetchQualityScoreEvolution = fetchQualityScoreEvolution as jest.Mock;

describe('useFetchQualityScoreEvolution', () => {
  beforeEach(() => jest.clearAllMocks());

  it('returns null initially before fetch completes', () => {
    mockedFetchQualityScoreEvolution.mockResolvedValue(null);
    const {result} = renderHook(() => useFetchQualityScoreEvolution('ecommerce', 'en_US', null, null));
    expect(result.current).toBeNull();
  });

  it('fetches and returns score evolution with average rank', async () => {
    const mockData = {data: {'2023-01': 'A', '2023-02': 'B', '2023-03': null}, average_rank: 'A'};
    mockedFetchQualityScoreEvolution.mockResolvedValue(mockData);

    const {result} = renderHook(() => useFetchQualityScoreEvolution('ecommerce', 'en_US', null, null));

    await waitFor(() => expect(result.current).not.toBeNull());
    expect(result.current).toEqual(mockData);
    expect(mockedFetchQualityScoreEvolution).toHaveBeenCalledWith('ecommerce', 'en_US', null, null);
  });

  it('resets to null when params change before re-fetching', async () => {
    mockedFetchQualityScoreEvolution.mockResolvedValue({data: {}, average_rank: 'B'});

    const {result, rerender} = renderHook(
      ({locale}: {locale: string}) => useFetchQualityScoreEvolution('ecommerce', locale, null, null),
      {initialProps: {locale: 'en_US'}}
    );

    await waitFor(() => expect(result.current).not.toBeNull());
    rerender({locale: 'fr_FR'});
    expect(result.current).toBeNull();
  });
});
