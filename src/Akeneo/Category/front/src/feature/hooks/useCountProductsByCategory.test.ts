import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {act} from '@testing-library/react';
import {useCountProductsByCategory} from './useCountProductsByCategory';

describe('useCountProductsByCategory', () => {
  test('it returns initial state with null numberOfProducts and a load function', () => {
    const {result} = renderHookWithProviders(() => useCountProductsByCategory(42));
    expect(result.current.numberOfProducts).toBeNull();
    expect(result.current.loadNumberOfProducts).toBeInstanceOf(Function);
  });

  test('it fetches the number of products in a category', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      json: () => Promise.resolve(17),
    } as any);

    const {result} = renderHookWithProviders(() => useCountProductsByCategory(42));

    await act(async () => {
      result.current.loadNumberOfProducts();
    });

    expect(result.current.numberOfProducts).toBe(17);
  });
});
