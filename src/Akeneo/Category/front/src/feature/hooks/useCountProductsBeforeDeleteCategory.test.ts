import {renderHook, act, waitFor} from '@testing-library/react';
import {useCountProductsBeforeDeleteCategory} from './useCountProductsBeforeDeleteCategory';
import {useCountProductsByCategory} from './useCountProductsByCategory';

jest.mock('./useCountProductsByCategory');

const mockedUseCountProductsByCategory = useCountProductsByCategory as jest.Mock;

describe('useCountProductsBeforeDeleteCategory', () => {
  let mockLoadNumberOfProducts: jest.Mock;
  let mockNumberOfProducts: number | null;

  beforeEach(() => {
    mockLoadNumberOfProducts = jest.fn();
    mockNumberOfProducts = null;
    mockedUseCountProductsByCategory.mockImplementation(() => ({
      numberOfProducts: mockNumberOfProducts,
      loadNumberOfProducts: mockLoadNumberOfProducts,
    }));
  });

  it('returns a function', () => {
    const {result} = renderHook(() => useCountProductsBeforeDeleteCategory(1));
    expect(typeof result.current).toBe('function');
  });

  it('calls loadNumberOfProducts when beforeDelete is called and count is not yet loaded', async () => {
    const {result} = renderHook(() => useCountProductsBeforeDeleteCategory(1));

    act(() => {
      result.current(jest.fn());
    });

    await waitFor(() => expect(mockLoadNumberOfProducts).toHaveBeenCalledTimes(1));
  });

  it('calls the callback immediately when numberOfProducts is already known', async () => {
    mockNumberOfProducts = 3;
    mockedUseCountProductsByCategory.mockImplementation(() => ({
      numberOfProducts: mockNumberOfProducts,
      loadNumberOfProducts: mockLoadNumberOfProducts,
    }));

    const callback = jest.fn();
    const {result} = renderHook(() => useCountProductsBeforeDeleteCategory(1));

    act(() => {
      result.current(callback);
    });

    await waitFor(() => expect(callback).toHaveBeenCalledWith(3));
    expect(mockLoadNumberOfProducts).not.toHaveBeenCalled();
  });

  it('calls the callback with the product count once numberOfProducts loads', async () => {
    const callback = jest.fn();

    const {result, rerender} = renderHook(() => useCountProductsBeforeDeleteCategory(1));

    act(() => {
      result.current(callback);
    });

    await waitFor(() => expect(mockLoadNumberOfProducts).toHaveBeenCalled());
    expect(callback).not.toHaveBeenCalled();

    // Simulate numberOfProducts arriving
    mockNumberOfProducts = 7;
    mockedUseCountProductsByCategory.mockImplementation(() => ({
      numberOfProducts: mockNumberOfProducts,
      loadNumberOfProducts: mockLoadNumberOfProducts,
    }));
    rerender();

    await waitFor(() => expect(callback).toHaveBeenCalledWith(7));
  });

  it('does not call loadNumberOfProducts when beforeDelete has not been called', () => {
    renderHook(() => useCountProductsBeforeDeleteCategory(1));
    expect(mockLoadNumberOfProducts).not.toHaveBeenCalled();
  });
});
