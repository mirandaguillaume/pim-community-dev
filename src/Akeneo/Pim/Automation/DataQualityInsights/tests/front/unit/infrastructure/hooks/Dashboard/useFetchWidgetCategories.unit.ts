import {renderHook, waitFor} from '@testing-library/react';
import useFetchWidgetCategories from '../../../../../../front/src/infrastructure/hooks/Dashboard/useFetchWidgetCategories';
import fetchWidgetCategories from '../../../../../../front/src/infrastructure/fetcher/Dashboard/fetchWidgetCategories';

jest.mock('routing', () => ({generate: jest.fn(() => '/mock-url')}));
jest.mock('../../../../../../front/src/infrastructure/fetcher/Dashboard/fetchWidgetCategories', () => jest.fn());

const mockedFetchWidgetCategories = fetchWidgetCategories as jest.Mock;

describe('useFetchWidgetCategories', () => {
  beforeEach(() => jest.clearAllMocks());

  it('returns empty object without fetching when categories array is empty', () => {
    const {result} = renderHook(() => useFetchWidgetCategories('ecommerce', 'en_US', []));
    expect(result.current).toEqual({});
    expect(mockedFetchWidgetCategories).not.toHaveBeenCalled();
  });

  it('extracts category codes and passes them to the fetcher', async () => {
    const mockData = {master: {rank: 'B', label: 'Master'}};
    mockedFetchWidgetCategories.mockResolvedValue(mockData);

    const categories = [
      {code: 'master', id: 1, rootCategoryId: 1},
      {code: 'sales', id: 2, rootCategoryId: 1},
    ];
    const {result} = renderHook(() => useFetchWidgetCategories('ecommerce', 'en_US', categories));

    await waitFor(() => expect(result.current).toEqual(mockData));
    expect(mockedFetchWidgetCategories).toHaveBeenCalledWith('ecommerce', 'en_US', ['master', 'sales']);
  });

  it('resets to empty object when categories becomes empty', async () => {
    const mockData = {master: {rank: 'A'}};
    mockedFetchWidgetCategories.mockResolvedValue(mockData);

    const {result, rerender} = renderHook(
      ({cats}: {cats: any[]}) => useFetchWidgetCategories('ecommerce', 'en_US', cats),
      {initialProps: {cats: [{code: 'master', id: 1, rootCategoryId: 1}]}}
    );

    await waitFor(() => expect(result.current).toEqual(mockData));
    rerender({cats: []});
    expect(result.current).toEqual({});
  });
});
