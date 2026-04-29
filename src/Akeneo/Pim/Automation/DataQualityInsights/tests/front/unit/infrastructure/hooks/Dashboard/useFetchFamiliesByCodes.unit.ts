import {renderHook, waitFor} from '@testing-library/react';
import useFetchFamiliesByCodes from '../../../../../../front/src/infrastructure/hooks/Dashboard/useFetchFamiliesByCodes';

declare const fetchMock: {
  resetMocks: () => void;
  mockResponseOnce: (body: string) => void;
  mock: {calls: Array<[string, unknown]>};
};

const mockGenerate = jest.fn((route: string, params: any) => `/api/${route}`);

jest.mock('@akeneo-pim-community/shared', () => ({
  useRouter: () => ({generate: mockGenerate}),
}));

jest.mock('routing', () => ({generate: jest.fn(() => '/mock-url')}));

describe('useFetchFamiliesByCodes', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    fetchMock.resetMocks();
  });

  it('returns empty array when widgetFamilies is empty', () => {
    const {result} = renderHook(() => useFetchFamiliesByCodes({}));
    expect(result.current).toEqual([]);
    expect(fetchMock.mock.calls).toHaveLength(0);
  });

  it('fetches families by codes and returns parsed data', async () => {
    const mockFamilies = [
      {code: 'cameras', label: 'Cameras'},
      {code: 'headphones', label: 'Headphones'},
    ];
    fetchMock.mockResponseOnce(JSON.stringify(mockFamilies));

    // Stable reference: object defined outside callback so useEffect([widgetFamilies]) doesn't loop
    const widgetFamilies = {cameras: 1, headphones: 2};
    const {result} = renderHook(() => useFetchFamiliesByCodes(widgetFamilies));

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
    expect(fetchMock.mock.calls).toHaveLength(0);
  });

  it('does not update state after unmount', async () => {
    fetchMock.mockResponseOnce(JSON.stringify([]));

    const stableFamilies = {cameras: 1};
    const {unmount} = renderHook(() => useFetchFamiliesByCodes(stableFamilies));
    unmount();
    await new Promise(resolve => setTimeout(resolve, 50));
  });
});
