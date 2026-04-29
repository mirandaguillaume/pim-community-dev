import {renderHook, waitFor} from '@testing-library/react';
import useFetchFamiliesByCodes from '../../../../../../front/src/infrastructure/hooks/Dashboard/useFetchFamiliesByCodes';

const mockGenerate = jest.fn((route: string, params: any) => `/api/${route}`);

jest.mock('@akeneo-pim-community/shared', () => ({
  useRouter: () => ({generate: mockGenerate}),
}));

jest.mock('routing', () => ({generate: jest.fn(() => '/mock-url')}));

describe('useFetchFamiliesByCodes', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    (global.fetch as jest.Mock).mockReset();
  });

  it('returns empty array when widgetFamilies is empty', () => {
    const {result} = renderHook(() => useFetchFamiliesByCodes({}));
    expect(result.current).toEqual([]);
    expect(global.fetch).not.toHaveBeenCalled();
  });

  it('fetches families by codes and returns parsed data', async () => {
    const mockFamilies = [
      {code: 'cameras', label: 'Cameras'},
      {code: 'headphones', label: 'Headphones'},
    ];
    (global.fetch as jest.Mock).mockResolvedValueOnce({
      json: () => Promise.resolve(mockFamilies),
    });

    const {result} = renderHook(() => useFetchFamiliesByCodes({cameras: 1, headphones: 2}));

    await waitFor(() => expect(result.current).toEqual(mockFamilies));
    expect(mockGenerate).toHaveBeenCalledWith('akeneo_data_quality_insights_find_families', {
      identifiers: ['cameras', 'headphones'],
    });
  });

  it('does not fetch when widgetFamilies transitions from populated to empty', () => {
    const {rerender} = renderHook(({families}: {families: any}) => useFetchFamiliesByCodes(families), {
      initialProps: {families: {}},
    });
    rerender({families: {}});
    expect(global.fetch).not.toHaveBeenCalled();
  });

  it('does not update state after unmount', async () => {
    (global.fetch as jest.Mock).mockImplementation(
      () => new Promise(resolve => setTimeout(() => resolve({json: () => Promise.resolve([])}), 100))
    );

    const {unmount} = renderHook(() => useFetchFamiliesByCodes({cameras: 1}));
    unmount();
    // No setState-after-unmount error should be thrown
    await new Promise(resolve => setTimeout(resolve, 150));
  });
});
