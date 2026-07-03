import React from 'react';
import {Provider, useSelector} from 'react-redux';
import PaginationBar from './PaginationBar';
import {makePaginationHandles} from './paginationHelpers';
import {selectGridStateReadView} from './gridStateSlice';
import {GridStore} from './createGridStore';

type PaginationConfig = {
  /** Backbone-pageable index base (1 for the numbered grids). */
  firstPage: number;
  windowSize: number;
  maxRescoreWindow: number;
  mode?: string;
  gapLabel: string;
};

type ViewProps = {
  enabled: boolean;
  config: PaginationConfig;
};

/**
 * Reads the grid position from the RTK mirror and rebuilds the page window (C1 Wave 5).
 *
 * The pure `makePaginationHandles` needs `firstPage`/`lastPage`, which the mirror's
 * `GridStateReadView` does not carry — but for a 1-based grid `lastPage === totalPages`,
 * so we feed `totalPages` as `lastPage` and the real `firstPage` from config. Returns
 * `null` for a single page (matches the host's `getPages().length <= 1` hide).
 */
const PaginationBarFromMirror = ({enabled, config}: ViewProps) => {
  const {currentPage, pageSize, totalRecords, totalPages} = useSelector(selectGridStateReadView);
  const handles = makePaginationHandles(
    {firstPage: config.firstPage, lastPage: totalPages, currentPage, pageSize, totalRecords},
    {
      windowSize: config.windowSize,
      maxRescoreWindow: config.maxRescoreWindow,
      mode: config.mode,
      gapLabel: config.gapLabel,
    }
  );

  if (handles.length <= 1) {
    return null;
  }

  return <PaginationBar handles={handles} disabled={!enabled || !totalRecords} />;
};

type Props = ViewProps & {store: GridStore};

/**
 * Mirror-consuming pagination bar (C1 Wave 5). The Backbone host (`pagination-input.js`)
 * still owns the lifecycle and the click→`getPage` navigation; this component only reads
 * the position from the per-grid RTK store via `useSelector` and rebuilds the buttons
 * reactively. It is self-contained — it wraps its own `<Provider store={grid.gridStore}>`
 * so the host can mount it through the standard `renderReact` (Theme/Dependencies) bridge.
 */
const ConnectedPaginationBar = ({store, enabled, config}: Props) => (
  <Provider store={store}>
    <PaginationBarFromMirror enabled={enabled} config={config} />
  </Provider>
);

export default ConnectedPaginationBar;
