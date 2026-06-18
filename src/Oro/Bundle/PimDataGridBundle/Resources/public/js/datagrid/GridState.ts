/**
 * Wave 5 state contract — designed first, landed last.
 *
 * Waves 2-4 shim against this read/write contract; Wave 5 replaces the
 * mutable Backbone `collection.state` with an RTK slice that satisfies it.
 *
 * Encoding conventions (replicated from pageable-collection.js stateShortKeys):
 *   currentPage → 'i', pageSize → 'p', sorters → 's', filters → 'f',
 *   gridName → 't', gridView → 'v'
 */

/** Sort direction as stored in `state.sorters`. */
export type SortDirection = -1 | 1;

/**
 * Active filter values keyed by filter name.
 *
 * Encoding (from collection-filters-manager.js `_createState`):
 *   - `name`     → filter value (filter enabled + non-empty)
 *   - `__name`   → 0 | 1  (filter explicitly disabled or enabled-empty)
 *
 * Absent key means "use filter default".
 */
export type FilterValues = Record<string, unknown>;

/** Extra parameters appended to every fetch (grid.js setAdditionalParameter). */
export type GridParameters = Record<string, unknown>;

/**
 * Canonical grid state contract.
 *
 * This is the single source of truth that every zone reads/writes.
 * Current implementation: mutable plain object on `PageableCollection.state`.
 * Future implementation (Wave 5): RTK slice.
 */
export type GridState = {
  /** 1-based current page. Written by filters (_onFilterUpdated) and pagination. */
  currentPage: number;
  /** Items per page. */
  pageSize: number;
  /** Total records returned by the last server response. */
  totalRecords: number;
  /** Total pages (derived: Math.ceil(totalRecords / pageSize)). */
  totalPages: number;
  /** Active sorters: field → direction. */
  sorters: Record<string, SortDirection>;
  /** Active filter values (see FilterValues encoding above). */
  filters: FilterValues;
  /** Grid name (== collection.inputName). */
  gridName?: string;
  /** ID of the currently selected saved view. */
  gridView?: string | number;
  /** Additional fetch parameters (setAdditionalParameter API). */
  parameters?: GridParameters;
  /** Product count subset (product-grid specific, set by parse()). */
  totalProducts?: number | null;
  /** Product-model count subset (product-grid specific, set by parse()). */
  totalProductModels?: number | null;
};

/**
 * Minimal read contract used by view-layer components (pagination, no-data block).
 * Waves 3+ read only these fields; they do not write state.
 */
export type GridStateReadView = Pick<GridState, 'currentPage' | 'pageSize' | 'totalRecords' | 'totalPages' | 'filters'>;

/**
 * Write contract used by filters (Wave 4).
 * Isolates the mutation surface from the full state shape.
 */
export type GridStateFilterWriter = {
  setFilters(values: FilterValues): void;
  resetPage(): void;
};
