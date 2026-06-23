import {createSlice, PayloadAction} from '@reduxjs/toolkit';
import {GridState, GridStateReadView} from './GridState';

/**
 * Wave 5 — the RTK mirror of the Backbone `collection.state`.
 *
 * MIRROR approach: the Backbone `PageableCollection` stays authoritative (it keeps owning fetch /
 * parse / `_checkState`). This slice holds a read-only copy, kept in sync by `createGridStateMirror`
 * from the collection's settle events (`reset`/`beforeFetch`/`updateState`), so React zones (the Wave
 * D/E filter managers) can read grid state via `useSelector` without reaching into the mutable
 * Backbone object.
 *
 * The only reducer is a wholesale `setGridState` replace — the mirror reflects the authoritative state
 * as a unit; granular reducers belong to a future write-authority phase, not the mirror.
 */
const initialState: GridState = {
  currentPage: 1,
  pageSize: 25,
  totalRecords: 0,
  totalPages: 1,
  sorters: {},
  filters: {},
};

const gridStateSlice = createSlice({
  name: 'gridState',
  initialState,
  reducers: {
    setGridState: (_state, action: PayloadAction<GridState>) => action.payload,
  },
});

export const {setGridState} = gridStateSlice.actions;
export default gridStateSlice.reducer;

/** Root state shape of the per-grid store (see createGridStore). */
export type GridRootState = {gridState: GridState};

/**
 * Map a Backbone `collection.state` object (a superset of GridState — it also carries Backbone-pageable
 * internals like `lastPage`/`firstPage`) to the canonical GridState.
 *
 * Crucially this CLONES the nested `sorters`/`filters`/`parameters` objects: the value dispatched into
 * the store is frozen by Immer, and Backbone keeps mutating its own `collection.state` — sharing the
 * references would freeze the live objects and throw. A shallow clone of each nested object decouples
 * the mirror copy from the authoritative one.
 */
export const toGridState = (state: Partial<GridState> & Record<string, unknown>): GridState => ({
  currentPage: state.currentPage ?? initialState.currentPage,
  pageSize: state.pageSize ?? initialState.pageSize,
  totalRecords: state.totalRecords ?? initialState.totalRecords,
  totalPages: state.totalPages ?? initialState.totalPages,
  sorters: {...(state.sorters ?? {})},
  filters: {...(state.filters ?? {})},
  ...(state.gridName !== undefined ? {gridName: state.gridName} : {}),
  ...(state.gridView !== undefined ? {gridView: state.gridView} : {}),
  ...(state.parameters !== undefined ? {parameters: {...state.parameters}} : {}),
  ...(state.totalProducts !== undefined ? {totalProducts: state.totalProducts} : {}),
  ...(state.totalProductModels !== undefined ? {totalProductModels: state.totalProductModels} : {}),
});

/** Full state selector. */
export const selectGridState = (root: GridRootState): GridState => root.gridState;

/** Minimal read view (pagination / no-data zones) — the GridStateReadView contract. */
export const selectGridStateReadView = (root: GridRootState): GridStateReadView => {
  const {currentPage, pageSize, totalRecords, totalPages, filters} = root.gridState;

  return {currentPage, pageSize, totalRecords, totalPages, filters};
};
