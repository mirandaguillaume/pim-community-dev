import * as _ from 'underscore';

/** An available grid filter as listed in the "Manage filters" panel. */
export interface GridFilter {
  group: string;
  label: string;
  name: string;
  enabled: boolean;
}

/**
 * Merge added filters into the original set: dedupe by name (first occurrence wins) and mark a filter
 * enabled when its name is in the grid's active filter set.
 *
 * Pure core of `FiltersColumn.mergeAddedFilters` (C1 slice D/E, D1a exemplar). The caller passes the
 * active filter names (`Object.keys(collection.state.filters)`) so this stays testable without Backbone.
 * Like the legacy, it marks `enabled` on the input objects in place (no clone) — byte-for-behaviour.
 */
export const mergeAddedFilters = (
  originalFilters: GridFilter[],
  addedFilters: GridFilter[],
  enabledFilterNames: string[]
): GridFilter[] => {
  const filters = [...originalFilters, ...addedFilters];
  const uniqueFilters: GridFilter[] = [];

  filters.forEach(mergedFilter => {
    if (enabledFilterNames.includes(mergedFilter.name)) {
      mergedFilter.enabled = true;
    }

    if (undefined === uniqueFilters.find(searchedFilter => searchedFilter.name === mergedFilter.name)) {
      uniqueFilters.push(mergedFilter);
    }
  });

  return uniqueFilters;
};

/** Filter the list by a case-insensitive search term matched against the label OR the name. */
export const filterBySearchTerm = (filters: GridFilter[], searchValue: string): GridFilter[] => {
  const search = searchValue.toLowerCase();

  return filters.filter(
    filter => filter.label.toLowerCase().includes(search) || filter.name.toLowerCase().includes(search)
  );
};

/** Group the filters by their `group`, falling back to the 'System' group. */
export const groupFilters = (filters: GridFilter[]): {[group: string]: GridFilter[]} =>
  _.groupBy(filters, (filter: GridFilter) => filter.group || 'System');
