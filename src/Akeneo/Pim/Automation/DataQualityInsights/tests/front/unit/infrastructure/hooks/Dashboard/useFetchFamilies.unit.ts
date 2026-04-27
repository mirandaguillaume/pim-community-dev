import {renderHook, waitFor} from '@testing-library/react';
import useFetchFamilies from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks/Dashboard/useFetchFamilies';
import fetchFamilies from '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/Dashboard/fetchFamilies';

jest.mock('@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/Dashboard/fetchFamilies');

const mockedFetchFamilies = fetchFamilies as jest.Mock;

describe('useFetchFamilies', () => {
  beforeEach(() => jest.clearAllMocks());

  it('does not fetch when isActive is false', () => {
    const {result} = renderHook(() => useFetchFamilies(false, 'en_US'));
    expect(result.current).toEqual([]);
    expect(mockedFetchFamilies).not.toHaveBeenCalled();
  });

  it('fetches and sorts families alphabetically by label when active', async () => {
    const mockFamilies = {
      cameras: {code: 'cameras', labels: {en_US: 'Cameras'}},
      accessories: {code: 'accessories', labels: {en_US: 'Accessories'}},
      bags: {code: 'bags', labels: {en_US: 'Bags'}},
    };
    mockedFetchFamilies.mockResolvedValue(mockFamilies);

    const {result} = renderHook(() => useFetchFamilies(true, 'en_US'));

    await waitFor(() => expect(result.current).toHaveLength(3));
    expect(result.current[0].code).toBe('accessories');
    expect(result.current[1].code).toBe('bags');
    expect(result.current[2].code).toBe('cameras');
  });

  it('uses bracketed code as sort key when label is missing for locale', async () => {
    const mockFamilies = {
      z_family: {code: 'z_family', labels: {}},
      a_family: {code: 'a_family', labels: {en_US: 'Alpha Family'}},
    };
    mockedFetchFamilies.mockResolvedValue(mockFamilies);

    const {result} = renderHook(() => useFetchFamilies(true, 'en_US'));

    await waitFor(() => expect(result.current).toHaveLength(2));
    // '[z_family]' (bracket notation) sorts before 'Alpha Family' alphabetically
    // because '[' (ASCII 91) precedes letters in localeCompare with sensitivity:'base'
    expect(result.current[0].code).toBe('z_family');
    expect(result.current[1].code).toBe('a_family');
  });
});
