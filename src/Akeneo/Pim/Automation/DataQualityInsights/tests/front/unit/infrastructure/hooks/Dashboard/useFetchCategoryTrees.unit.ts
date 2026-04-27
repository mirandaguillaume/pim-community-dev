import {renderHook, waitFor} from '@testing-library/react';
import useFetchCategoryTrees from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks/Dashboard/useFetchCategoryTrees';
import fetchCategoryTrees from '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/Dashboard/fetchCategoryTrees';

jest.mock('@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/Dashboard/fetchCategoryTrees');

const mockedFetchCategoryTrees = fetchCategoryTrees as jest.Mock;

describe('useFetchCategoryTrees', () => {
  beforeEach(() => jest.clearAllMocks());

  it('returns empty array as initial state', () => {
    mockedFetchCategoryTrees.mockResolvedValue([]);
    const {result} = renderHook(() => useFetchCategoryTrees());
    expect(result.current).toEqual([]);
  });

  it('fetches and returns category trees on mount', async () => {
    const mockTrees = [
      {id: 1, code: 'master', label: 'Master catalog'},
      {id: 2, code: 'sales', label: 'Sales catalog'},
    ];
    mockedFetchCategoryTrees.mockResolvedValue(mockTrees);

    const {result} = renderHook(() => useFetchCategoryTrees());

    await waitFor(() => expect(result.current).toEqual(mockTrees));
    expect(mockedFetchCategoryTrees).toHaveBeenCalledTimes(1);
  });
});
