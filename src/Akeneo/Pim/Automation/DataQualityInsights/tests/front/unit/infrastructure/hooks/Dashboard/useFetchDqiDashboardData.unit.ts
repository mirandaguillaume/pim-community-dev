import {renderHook, waitFor} from '@testing-library/react';
import useFetchDqiDashboardData from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks/Dashboard/useFetchDqiDashboardData';
import {fetchDqiDashboardData} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher';

jest.mock('@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher');

const mockedFetchDqiDashboardData = fetchDqiDashboardData as jest.Mock;

describe('useFetchDqiDashboardData', () => {
  beforeEach(() => jest.clearAllMocks());

  it('returns null initially before fetch completes', () => {
    mockedFetchDqiDashboardData.mockResolvedValue({});
    const {result} = renderHook(() => useFetchDqiDashboardData('ecommerce', 'en_US', 'monthly', null, null));
    expect(result.current).toBeNull();
  });

  it('fetches and returns dashboard score distribution data', async () => {
    const mockData = {'2023-01': {A: 10, B: 20, C: 30, D: 25, E: 15}};
    mockedFetchDqiDashboardData.mockResolvedValue(mockData);

    const {result} = renderHook(() => useFetchDqiDashboardData('ecommerce', 'en_US', 'monthly', null, null));

    await waitFor(() => expect(result.current).not.toBeNull());
    expect(result.current).toEqual(mockData);
    expect(mockedFetchDqiDashboardData).toHaveBeenCalledWith('ecommerce', 'en_US', 'monthly', null, null);
  });

  it('forwards all filters including family and category', async () => {
    mockedFetchDqiDashboardData.mockResolvedValue({});

    renderHook(() => useFetchDqiDashboardData('ecommerce', 'en_US', 'weekly', 'cameras', 'master'));

    await waitFor(() =>
      expect(mockedFetchDqiDashboardData).toHaveBeenCalledWith('ecommerce', 'en_US', 'weekly', 'cameras', 'master')
    );
  });
});
