import {FilterValues, GridState, GridStateFilterWriter} from './GridState';

/**
 * Wave 4 — the filter write contract, implemented.
 *
 * `GridState.ts` (Wave 5, #283) typed `GridStateFilterWriter` but left it unimplemented; the filters
 * kept mutating `collection.state` directly (collection-filters-manager.js, filters-selector.ts).
 * This factory is the single, explicit mutation surface those write sites now route through, so the
 * filter layer no longer reaches into the raw state shape. When Wave 5 swaps the mutable
 * `PageableCollection.state` for an RTK slice, only this factory changes — the filter managers keep
 * calling `setFilters`/`resetPage` unchanged.
 *
 * Behaviour is a 1:1 wrapper of the previous direct writes (same synchronous mutation, same fields),
 * so it is byte-for-behaviour equivalent — it does NOT fetch, reset other state, or fire events.
 */
const createGridStateFilterWriter = (collection: {state: GridState}): GridStateFilterWriter => ({
  setFilters(values: FilterValues): void {
    collection.state.filters = values;
  },
  resetPage(): void {
    collection.state.currentPage = 1;
  },
});

export default createGridStateFilterWriter;
