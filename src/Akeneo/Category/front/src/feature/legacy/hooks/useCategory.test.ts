import {renderHook} from '@testing-library/react';
import {useCategory} from './useCategory';
import {useFetch, useRoute} from '@akeneo-pim-community/shared';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useFetch: jest.fn(),
  useRoute: jest.fn(),
}));

const mockedUseRoute = useRoute as jest.Mock;
const mockedUseFetch = useFetch as jest.Mock;

describe('useCategory (legacy)', () => {
  const mockLoad = jest.fn();

  beforeEach(() => {
    mockedUseRoute.mockReturnValue('/category-url');
    mockedUseFetch.mockReturnValue([null, mockLoad, 'fetching', null]);
  });

  it('generates the route with categoryId as a string', () => {
    renderHook(() => useCategory(42));
    expect(mockedUseRoute).toHaveBeenCalledWith('pim_enrich_categorytree_edit', {id: '42'});
  });

  it('passes the generated url to useFetch', () => {
    renderHook(() => useCategory(42));
    expect(mockedUseFetch).toHaveBeenCalledWith('/category-url');
  });

  it('returns the [data, load, status, error] tuple from useFetch', () => {
    const mockData = {category: {id: 42}, form: {}};
    mockedUseFetch.mockReturnValue([mockData, mockLoad, 'fetched', null]);

    const {result} = renderHook(() => useCategory(42));

    const [data, load, status, error] = result.current;
    expect(data).toBe(mockData);
    expect(load).toBe(mockLoad);
    expect(status).toBe('fetched');
    expect(error).toBeNull();
  });
});
