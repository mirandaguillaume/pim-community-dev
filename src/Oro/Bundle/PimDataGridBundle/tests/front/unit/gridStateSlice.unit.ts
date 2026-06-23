import gridStateReducer, {
  setGridState,
  toGridState,
  selectGridState,
  selectGridStateReadView,
} from '../../../Resources/public/js/datagrid/gridStateSlice';
import {GridState} from '../../../Resources/public/js/datagrid/GridState';

const aState = (over: Partial<GridState> = {}): GridState => ({
  currentPage: 2,
  pageSize: 25,
  totalRecords: 100,
  totalPages: 4,
  sorters: {label: 1},
  filters: {enabled: true},
  ...over,
});

describe('gridStateSlice reducer', () => {
  test('setGridState replaces the whole state', () => {
    const next = gridStateReducer(aState(), setGridState(aState({currentPage: 5, totalRecords: 200})));

    expect(next.currentPage).toBe(5);
    expect(next.totalRecords).toBe(200);
  });
});

describe('toGridState', () => {
  test('maps the core fields', () => {
    expect(
      toGridState({currentPage: 3, pageSize: 50, totalRecords: 10, totalPages: 1, sorters: {a: 1}, filters: {b: 2}})
    ).toEqual({currentPage: 3, pageSize: 50, totalRecords: 10, totalPages: 1, sorters: {a: 1}, filters: {b: 2}});
  });

  test('clones nested sorters/filters so Immer freezing the mirror cannot freeze the live objects', () => {
    const sorters = {label: 1 as const};
    const filters = {enabled: true};

    const result = toGridState({currentPage: 1, pageSize: 25, totalRecords: 0, totalPages: 1, sorters, filters});

    expect(result.sorters).toEqual(sorters);
    expect(result.sorters).not.toBe(sorters);
    expect(result.filters).not.toBe(filters);
  });

  test('includes optional fields only when present', () => {
    const base = {currentPage: 1, pageSize: 25, totalRecords: 0, totalPages: 1, sorters: {}, filters: {}};

    const without = toGridState(base);
    expect('gridName' in without).toBe(false);
    expect('totalProducts' in without).toBe(false);

    const withOpt = toGridState({
      ...base,
      gridName: 'product-grid',
      gridView: 42,
      totalProducts: 7,
      totalProductModels: 3,
    });
    expect(withOpt.gridName).toBe('product-grid');
    expect(withOpt.gridView).toBe(42);
    expect(withOpt.totalProducts).toBe(7);
    expect(withOpt.totalProductModels).toBe(3);
  });

  test('clones the optional parameters object (setAdditionalParameter mutates the live one in place)', () => {
    const base = {currentPage: 1, pageSize: 25, totalRecords: 0, totalPages: 1, sorters: {}, filters: {}};
    const parameters = {scope: 'ecommerce'};

    const without = toGridState(base);
    expect('parameters' in without).toBe(false);

    const result = toGridState({...base, parameters});
    expect(result.parameters).toEqual(parameters);
    expect(result.parameters).not.toBe(parameters);
  });

  test('falls back to defaults for missing core fields', () => {
    const result = toGridState({});

    expect(result.currentPage).toBe(1);
    expect(result.pageSize).toBe(25);
    expect(result.sorters).toEqual({});
  });
});

describe('selectors', () => {
  test('selectGridState returns the whole slice', () => {
    expect(selectGridState({gridState: aState()})).toEqual(aState());
  });

  test('selectGridStateReadView returns only the read-view fields (no sorters)', () => {
    const view = selectGridStateReadView({gridState: aState()});

    expect(view).toEqual({currentPage: 2, pageSize: 25, totalRecords: 100, totalPages: 4, filters: {enabled: true}});
    expect('sorters' in view).toBe(false);
  });
});
