import {renderHook, waitFor} from '@testing-library/react';
import {useFetchKeyIndicators} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks/Dashboard/useFetchKeyIndicators';
import {fetchKeyIndicators} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher';

jest.mock('@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher');

const mockedFetchKeyIndicators = fetchKeyIndicators as jest.Mock;

describe('useFetchKeyIndicators', () => {
  beforeEach(() => jest.clearAllMocks());

  it('returns null initially before fetch completes', () => {
    mockedFetchKeyIndicators.mockResolvedValue({});
    const {result} = renderHook(() => useFetchKeyIndicators('ecommerce', 'en_US', null, null));
    expect(result.current).toBeNull();
  });

  it('fetches and returns key indicators data', async () => {
    const mockData = {completeness: {value: 75, products_count: 100}};
    mockedFetchKeyIndicators.mockResolvedValue(mockData);

    const {result} = renderHook(() => useFetchKeyIndicators('ecommerce', 'en_US', null, null));

    await waitFor(() => expect(result.current).not.toBeNull());
    expect(result.current).toEqual(mockData);
    expect(mockedFetchKeyIndicators).toHaveBeenCalledWith('ecommerce', 'en_US', null, null);
  });

  it('forwards family and category filters to the fetcher', async () => {
    mockedFetchKeyIndicators.mockResolvedValue({});

    renderHook(() => useFetchKeyIndicators('ecommerce', 'en_US', 'cameras', 'master'));

    await waitFor(() =>
      expect(mockedFetchKeyIndicators).toHaveBeenCalledWith('ecommerce', 'en_US', 'cameras', 'master')
    );
  });

  it('resets to null when params change before re-fetching', async () => {
    mockedFetchKeyIndicators.mockResolvedValue({completeness: {}});

    const {result, rerender} = renderHook(
      ({channel}: {channel: string}) => useFetchKeyIndicators(channel, 'en_US', null, null),
      {initialProps: {channel: 'ecommerce'}}
    );

    await waitFor(() => expect(result.current).not.toBeNull());
    rerender({channel: 'mobile'});
    expect(result.current).toBeNull();
  });
});
