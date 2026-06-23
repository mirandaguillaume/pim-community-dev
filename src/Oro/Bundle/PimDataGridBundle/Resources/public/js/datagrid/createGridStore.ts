import {configureStore} from '@reduxjs/toolkit';
import gridStateReducer, {GridRootState} from './gridStateSlice';

/**
 * Wave 5 — a per-grid-instance RTK store holding the `gridState` mirror.
 *
 * One store is created per `Grid` instance (in grid.js) rather than a shared singleton, so two grids
 * on one page never collide. The store is exposed on the grid so the React filter managers (Wave D/E)
 * can wrap their roots in `<Provider store={grid.gridStore}>` and read via `useSelector`.
 *
 * `serializableCheck` is disabled: the mirror faithfully copies arbitrary grid state (filter values
 * can hold non-plain shapes), and it is a read-only reflection — not user-dispatched actions — so the
 * check would only add dev-console noise.
 */
export const createGridStore = () =>
  configureStore({
    reducer: {gridState: gridStateReducer},
    middleware: getDefaultMiddleware => getDefaultMiddleware({serializableCheck: false}),
  });

export type GridStore = ReturnType<typeof createGridStore>;
export type {GridRootState};

export default createGridStore;
