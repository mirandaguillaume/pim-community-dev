import {
  getPages,
  makePaginationHandles,
  PageableState,
  PaginationConfig,
} from '../../../Resources/public/js/datagrid/paginationHelpers';

// The runtime values from pagination-input.js (windowSize 3, 10k rescore window, `...` gap).
const config = (overrides: Partial<PaginationConfig> = {}): PaginationConfig => ({
  windowSize: 3,
  maxRescoreWindow: 10000,
  mode: 'server',
  gapLabel: '...',
  ...overrides,
});

const state = (overrides: Partial<PageableState> = {}): PageableState => ({
  firstPage: 1,
  lastPage: 1,
  currentPage: 1,
  pageSize: 25,
  totalRecords: 10,
  ...overrides,
});

describe('getPages', () => {
  test('single page (1-based): collapses to just the first page id', () => {
    expect(getPages(state({lastPage: 1, currentPage: 1, totalRecords: 10}), config())).toEqual([0]);
  });

  test('current page in the middle: first + window(3) + last, deduped', () => {
    // 10 pages (250 records / 25), current page 5 (1-based)
    expect(getPages(state({lastPage: 10, currentPage: 5, totalRecords: 250}), config())).toEqual([0, 3, 4, 5, 9]);
  });

  test('beyond the rescore window: no last page, window capped by lastAccessiblePage', () => {
    // 20000 records > 10000 cap; lastAccessiblePage = 400. Current at start.
    expect(getPages(state({lastPage: 800, currentPage: 1, totalRecords: 20000}), config())).toEqual([0, 1, 2]);
  });

  test('0-based base (legacy): reproduces the original index math verbatim', () => {
    expect(getPages(state({firstPage: 0, lastPage: 9, currentPage: 4, totalRecords: 250}), config())).toEqual([
      -1, 3, 4, 5, 9,
    ]);
  });
});

describe('makePaginationHandles', () => {
  test('infinite collections render no numbered handles', () => {
    expect(makePaginationHandles(state({lastPage: 10, currentPage: 5}), config({mode: 'infinite'}))).toEqual([]);
  });

  test('marks the current page active and inserts `...` gaps between non-contiguous ids', () => {
    const handles = makePaginationHandles(state({lastPage: 10, currentPage: 5, totalRecords: 250}), config());

    expect(handles).toEqual([
      {label: 1, title: 'No. 1', className: undefined},
      {label: '...', title: '...', className: 'AknActionButton--unclickable'},
      {label: 4, title: 'No. 4', className: undefined},
      {label: 5, title: 'No. 5', className: 'active AknActionButton--highlight'},
      {label: 6, title: 'No. 6', className: undefined},
      {label: '...', title: '...', className: 'AknActionButton--unclickable'},
      {label: 10, title: 'No. 10', className: undefined},
    ]);
  });

  test('appends a trailing `...` gap when records exceed the rescore window', () => {
    const handles = makePaginationHandles(state({lastPage: 800, currentPage: 1, totalRecords: 20000}), config());

    expect(handles).toEqual([
      {label: 1, title: 'No. 1', className: 'active AknActionButton--highlight'},
      {label: 2, title: 'No. 2', className: undefined},
      {label: 3, title: 'No. 3', className: undefined},
      {label: '...', title: '...', className: 'AknActionButton--unclickable'},
    ]);
  });

  test('a single-page collection yields one active handle, no gaps', () => {
    expect(makePaginationHandles(state({lastPage: 1, currentPage: 1, totalRecords: 10}), config())).toEqual([
      {label: 1, title: 'No. 1', className: 'active AknActionButton--highlight'},
    ]);
  });
});
