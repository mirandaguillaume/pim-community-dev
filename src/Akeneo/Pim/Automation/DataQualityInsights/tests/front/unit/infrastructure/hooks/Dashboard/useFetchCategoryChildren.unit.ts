import {renderHook, waitFor} from '@testing-library/react';
import useFetchCategoryChildren from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks/Dashboard/useFetchCategoryChildren';
import fetchCategoryChildren from '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/Dashboard/fetchCategoryChildren';

jest.mock('@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/Dashboard/fetchCategoryChildren');

const mockedFetchCategoryChildren = fetchCategoryChildren as jest.Mock;

describe('useFetchCategoryChildren', () => {
  beforeEach(() => jest.clearAllMocks());

  it('returns empty object initially', () => {
    const {result} = renderHook(() => useFetchCategoryChildren('en_US', '1', false));
    expect(result.current).toEqual({});
  });

  it('does not fetch when isOpened is false', () => {
    renderHook(() => useFetchCategoryChildren('en_US', '1', false));
    expect(mockedFetchCategoryChildren).not.toHaveBeenCalled();
  });

  it('fetches children when isOpened is true', async () => {
    const mockChildren = {2: {id: 2, code: 'child_cat', label: 'Child category'}};
    mockedFetchCategoryChildren.mockResolvedValue(mockChildren);

    const {result} = renderHook(() => useFetchCategoryChildren('en_US', '1', true));

    await waitFor(() => expect(result.current).toEqual(mockChildren));
    expect(mockedFetchCategoryChildren).toHaveBeenCalledWith('en_US', '1');
  });

  it('does not re-fetch when locale changes but isOpened stays false', () => {
    const {rerender} = renderHook(({locale}: {locale: string}) => useFetchCategoryChildren(locale, '1', false), {
      initialProps: {locale: 'en_US'},
    });
    rerender({locale: 'fr_FR'});
    expect(mockedFetchCategoryChildren).not.toHaveBeenCalled();
  });
});
