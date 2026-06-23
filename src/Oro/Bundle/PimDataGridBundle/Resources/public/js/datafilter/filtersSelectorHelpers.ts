import * as _ from 'underscore';

/** A committed filter value (the shape stored in `collection.state.filters[name]`). */
export type FilterValue = {type: string; value: any};

/**
 * The persisted filter state keyed by filter name.
 *
 * Server contract (replicated from the legacy `filters-selector` getState):
 *   - `name`   → the filter value (filter enabled + non-empty)
 *   - `__name` → 1 (enabled but empty, NOT default-on) | 0 (disabled, but default-on)
 *   - absent   → "use the filter default"
 */
export type FilterState = {[name: string]: FilterValue | number};

/** The minimal slice of a filter module the state computation needs. */
export interface StatefulFilter {
  enabled: boolean;
  defaultEnabled: boolean;
  isEmpty(): boolean;
  getValue(): FilterValue;
}

/**
 * Compute the persisted filter state from the active filter modules — the pure core of
 * `FiltersSelector.getState()` (C1 slice D/E, E1 exemplar). Extracted verbatim so the `__name` 0/1
 * encoding is unit-tested independently of Backbone/DOM, ahead of the later React render rewrite
 * (mirrors how Slice C extracted `viewComboboxHelpers` / `computeFilterPopupPosition`).
 */
export const computeFilterState = (modules: {[name: string]: StatefulFilter}): FilterState => {
  const filterState: FilterState = {};

  for (const filterName in modules) {
    const filter = modules[filterName];
    const shortName = `__${filterName}`;

    if (filter.enabled) {
      if (!filter.isEmpty()) {
        filterState[filterName] = filter.getValue();
      } else if (!filter.defaultEnabled) {
        filterState[shortName] = 1;
      }
    } else if (filter.defaultEnabled) {
      filterState[shortName] = 0;
    }
  }

  return filterState;
};

/**
 * Merge the category filter on top of the computed filter state (the category wins), without mutating
 * either input — the pure equivalent of the legacy `Object.assign(this.getState(), categoryFilter)`.
 */
export const mergeCategoryFilter = (filterState: FilterState, categoryFilter: FilterState): FilterState =>
  Object.assign({}, filterState, categoryFilter);

/**
 * Decide whether the grid must reload (write the new filters + reset page + fetch): when the new
 * state differs from the current one (or the current one is empty), and we are NOT in a silent
 * restore. `currentState` MUST be the authoritative `collection.state.filters`, never the RTK mirror
 * clone (which can lag a sync event) — else a stale read would loop the fetch.
 */
export const shouldReloadGridState = (
  currentState: FilterState,
  updatedState: FilterState,
  silent: boolean
): boolean => {
  const stateHasChanged = !_.isEqual(currentState, updatedState);
  const currentStateIsEmpty = _.isEmpty(currentState);

  return (stateHasChanged || currentStateIsEmpty) && false === silent;
};
