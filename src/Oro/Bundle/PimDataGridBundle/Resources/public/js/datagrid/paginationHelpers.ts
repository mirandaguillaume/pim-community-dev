/**
 * Pure pagination-window computation extracted verbatim from `pagination-input.js`
 * (`makeHandles` + `getPages`). Kept behaviour-identical so the byte-for-byte Behat
 * markup contract of `PaginationBar` holds across every grid (~20) that uses it.
 *
 * This extraction is Wave 5 groundwork: once the logic is a pure function of a small,
 * typed input it can be unit-tested exhaustively and then fed from the RTK gridState
 * mirror (`selectGridStateReadView`) instead of Backbone `collection.state` — see the
 * follow-up `ConnectedPaginationBar`. For now `pagination-input.js` calls it with the
 * live `collection.state`, so nothing changes at runtime.
 */

/** The subset of Backbone-pageable `collection.state` the page window depends on. */
export type PageableState = {
  /** Backbone-pageable index base: 0 (0-based pages) or 1 (1-based). */
  firstPage: number;
  /** Last page index in the collection's own base (may be undefined before first load). */
  lastPage?: number;
  /** Current page in the collection's own base. */
  currentPage: number;
  pageSize: number;
  totalRecords: number;
};

export type PaginationConfig = {
  /** Number of page buttons shown around the current one. */
  windowSize: number;
  /** Hard cap on reachable records (Elasticsearch rescore window). */
  maxRescoreWindow: number;
  /** `collection.mode` — an `'infinite'` collection renders no numbered handles. */
  mode?: string;
  /** Label used for the non-clickable `...` fast-forward gap. */
  gapLabel: string;
};

export type PaginationHandle = {
  label: string | number;
  title?: string;
  className?: string;
};

/**
 * Zero-based list of page ids to display: always the first page, a window around the
 * current page, and (when within the rescore window) the last page — deduped.
 */
export const getPages = (
  state: PageableState,
  {windowSize, maxRescoreWindow}: Pick<PaginationConfig, 'windowSize' | 'maxRescoreWindow'>
): number[] => {
  let lastPage = state.lastPage ? state.lastPage : state.firstPage;
  lastPage = state.firstPage === 0 ? lastPage : lastPage - 1;
  const lastAccessiblePage = Math.floor(maxRescoreWindow / state.pageSize);
  const currentPage = state.firstPage === 0 ? state.currentPage : state.currentPage - 1;
  let windowStart = currentPage - (windowSize - 1) / 2;
  windowStart = Math.max(Math.min(windowStart, lastPage - windowSize + 1), 0);
  const windowEnd = Math.min(windowStart + windowSize, lastPage, lastAccessiblePage);
  const ids: number[] = [];

  ids.push(state.firstPage - 1);
  for (let i = windowStart; i < windowEnd; i++) {
    ids.push(i);
  }

  if (state.totalRecords < maxRescoreWindow) {
    ids.push(lastPage);
  }

  // Dedupe preserving first-occurrence order (was `_.uniq`). Deliberately NOT `[...new Set(ids)]`:
  // under tsconfig `target: es5` without `downlevelIteration`, spreading a Set transpiles to
  // array-copy logic that reads `.length`/indices off the Set and silently yields `[]`.
  return ids.filter((value, index) => ids.indexOf(value) === index);
};

/**
 * Builds the ordered list of pagination button descriptors consumed by `PaginationBar`.
 * Returns an empty list for `'infinite'` collections (no numbered pagination).
 */
export const makePaginationHandles = (state: PageableState, config: PaginationConfig): PaginationHandle[] => {
  const {windowSize, maxRescoreWindow, mode, gapLabel} = config;
  const handles: PaginationHandle[] = [];

  if (mode === 'infinite') {
    return handles;
  }

  const pageIds = getPages(state, {windowSize, maxRescoreWindow});
  const currentPage = state.firstPage === 0 ? state.currentPage : state.currentPage - 1;
  let previousId = pageIds[0];

  pageIds.forEach(id => {
    if (id - previousId > 1) {
      handles.push({label: gapLabel, title: gapLabel, className: 'AknActionButton--unclickable'});
    }
    previousId = id;
    handles.push({
      label: id + 1,
      title: 'No. ' + (id + 1),
      className: currentPage === id ? 'active AknActionButton--highlight' : undefined,
    });
  });

  if (state.totalRecords > maxRescoreWindow && (previousId + 1) * state.pageSize < maxRescoreWindow) {
    handles.push({label: gapLabel, title: gapLabel, className: 'AknActionButton--unclickable'});
  }

  return handles;
};
