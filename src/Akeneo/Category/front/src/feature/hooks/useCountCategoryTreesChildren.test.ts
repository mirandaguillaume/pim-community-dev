import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {act} from '@testing-library/react';
import {useCountCategoryTreesChildren} from './useCountCategoryTreesChildren';

describe('useCountCategoryTreesChildren', () => {
  afterEach(() => jest.restoreAllMocks());

  test('it returns null initially', () => {
    jest.spyOn(global, 'fetch').mockResolvedValue({json: jest.fn()} as any);
    const {result} = renderHookWithProviders(() => useCountCategoryTreesChildren());
    expect(result.current).toBeNull();
  });

  test('it fetches and returns the count of children per category tree', async () => {
    const mockData = {1: 5, 2: 12, 3: 0};
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      json: () => Promise.resolve(mockData),
    } as any);

    const {result} = renderHookWithProviders(() => useCountCategoryTreesChildren());

    await act(async () => {});

    expect(result.current).toEqual(mockData);
  });
});
