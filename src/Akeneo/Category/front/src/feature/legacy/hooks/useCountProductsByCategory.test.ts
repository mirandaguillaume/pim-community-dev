import {renderHook} from '@testing-library/react';
import {useCountProductsByCategory} from './useCountProductsByCategory';
import {useFetch, useRoute} from '@akeneo-pim-community/shared';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useFetch: jest.fn(),
  useRoute: jest.fn(),
}));

const mockedUseRoute = useRoute as jest.Mock;
const mockedUseFetch = useFetch as jest.Mock;

describe('useCountProductsByCategory', () => {
  beforeEach(() => {
    mockedUseRoute.mockReturnValue('/route/url');
    mockedUseFetch.mockReturnValue([null, jest.fn()]);
  });

  it('returns numberOfProducts and loadNumberOfProducts from useFetch', () => {
    const mockLoad = jest.fn();
    mockedUseFetch.mockReturnValue([42, mockLoad]);

    const {result} = renderHook(() => useCountProductsByCategory(5));

    expect(result.current.numberOfProducts).toBe(42);
    expect(result.current.loadNumberOfProducts).toBe(mockLoad);
  });

  it('generates the route with the category id as string', () => {
    renderHook(() => useCountProductsByCategory(7));

    expect(mockedUseRoute).toHaveBeenCalledWith('pim_enrich_categorytree_count_category_products', {id: '7'});
  });

  it('passes the generated url to useFetch', () => {
    mockedUseRoute.mockReturnValue('/my-route/7');
    renderHook(() => useCountProductsByCategory(7));

    expect(mockedUseFetch).toHaveBeenCalledWith('/my-route/7');
  });
});
