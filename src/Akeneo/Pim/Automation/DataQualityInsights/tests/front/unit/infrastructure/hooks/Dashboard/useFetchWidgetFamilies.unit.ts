import {renderHook, waitFor} from '@testing-library/react';
import useFetchWidgetFamilies from '../../../../../../front/src/infrastructure/hooks/Dashboard/useFetchWidgetFamilies';
import fetchWidgetFamilies from '../../../../../../front/src/infrastructure/fetcher/Dashboard/fetchWidgetFamilies';

jest.mock('routing', () => ({generate: jest.fn(() => '/mock-url')}));
jest.mock('../../../../../../front/src/infrastructure/fetcher/Dashboard/fetchWidgetFamilies', () => jest.fn());

const mockedFetchWidgetFamilies = fetchWidgetFamilies as jest.Mock;

describe('useFetchWidgetFamilies', () => {
  beforeEach(() => jest.clearAllMocks());

  it('returns empty object without fetching when familyCodes is empty', () => {
    const noFamilyCodes: string[] = [];
    const {result} = renderHook(() => useFetchWidgetFamilies('ecommerce', 'en_US', noFamilyCodes));
    expect(result.current).toEqual({});
    expect(mockedFetchWidgetFamilies).not.toHaveBeenCalled();
  });

  it('fetches widget families when family codes are provided', async () => {
    const mockData = {cameras: {rank: 'A', label: 'Cameras'}};
    mockedFetchWidgetFamilies.mockResolvedValue(mockData);

    const {result} = renderHook(() => useFetchWidgetFamilies('ecommerce', 'en_US', ['cameras']));

    await waitFor(() => expect(result.current).toEqual(mockData));
    expect(mockedFetchWidgetFamilies).toHaveBeenCalledWith('ecommerce', 'en_US', ['cameras']);
  });

  it('resets to empty object when familyCodes becomes empty', async () => {
    const mockData = {cameras: {rank: 'A'}};
    mockedFetchWidgetFamilies.mockResolvedValue(mockData);

    const {result, rerender} = renderHook(
      ({codes}: {codes: string[]}) => useFetchWidgetFamilies('ecommerce', 'en_US', codes),
      {initialProps: {codes: ['cameras']}}
    );

    await waitFor(() => expect(result.current).toEqual(mockData));
    rerender({codes: []});
    expect(result.current).toEqual({});
  });
});
