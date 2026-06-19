# C1 Wave 4 — filter state-writer contract — Plan

> Routes the product-grid filter state-writes through the `GridStateFilterWriter` contract that
> Wave 5 (#283) typed but left unimplemented. First step of Wave 4 (filters), per the Wave 0 audit
> (Revision 2/3: filters write `collection.state` directly and must shim against the single-source-
> of-truth state). **Local constraint:** Jest/Behat are NOT run locally → CI validates.

## Why

`GridState.ts` (#283) defined `GridStateFilterWriter = {setFilters(values), resetPage()}` to "isolate
the mutation surface from the full state shape", but the filters still mutated `collection.state`
directly. This PR implements that writer and routes the **4 filter-zone write sites** through it, so
the filter layer no longer reaches into the raw state object. When Wave 5 replaces the mutable
`PageableCollection.state` with an RTK slice, only the writer changes.

## Scope (the complete filter-zone write surface — verified)

| Site | Was | Now |
|---|---|---|
| `collection-filters-manager.js` `_onFilterUpdated` | `this.collection.state.currentPage = 1` | `this.stateWriter.resetPage()` |
| `collection-filters-manager.js` `_beforeCollectionFetch` | `collection.state.filters = this._createState()` | `this.stateWriter.setFilters(this._createState())` |
| `filters-selector.ts` `updateGridState` | `state.filters = updatedState` | `stateWriter.setFilters(updatedState)` |
| `filters-selector.ts` `updateGridState` | `state.currentPage = 1` | `stateWriter.resetPage()` |

The CORE/state-engine writes (`pageable-collection.js`, `grid.js` parse/onRemove/setSorting/
setAdditionalParameter) are **Wave 5**, NOT this PR. The base `filters-manager.js` and the
`filter/*.js` types do not write `filters`/`currentPage` directly.

## Files

- **Create** `js/datagrid/createGridStateFilterWriter.ts` — `(collection) => ({setFilters, resetPage})`,
  a 1:1 wrapper of the previous direct writes (same synchronous mutation, no fetch/event/other-state).
- **Modify** `js/datafilter/collection-filters-manager.js` — import the writer; build `this.stateWriter`
  in `initialize` (after `this.collection = options.collection`); route the 2 sites. `_beforeCollectionFetch`
  loses its now-unused `collection` param (the beforeFetch arg is always `this.collection`).
- **Modify** `js/datafilter/filters-selector.ts` — import the writer; build it inline in
  `updateGridState` from `this.datagridCollection` (set lazily, so per-call is cleanest); route the 2 sites.
- **Create** `tests/front/unit/createGridStateFilterWriter.unit.ts` — setFilters writes filters only,
  resetPage sets currentPage=1 only, in-place mutation. Registered in the per-PR Stryker `testMatch`.

## Behavioural equivalence

1:1 routing: same fields, same synchronous timing, same order (resetPage/setFilters then fetch). The
writer does not fetch, fire events, or touch other state. Verified by a 3-angle adversarial workflow
(behavioural equivalence / completeness / contract+tests) before commit.

## Validation

`js/datafilter/` is eslint-ignored (legacy Oro); `.ts` covered by prettier:check. CI: build-front,
test-front-unit, mutation, and **Behat** (filtering across grids — the runtime backstop). No UI/markup
change → low Behat risk.

## Out of scope (later Wave 4 / Wave 5)
The React filter UI migration; untangling the page-shell DOM reaches; the dual-FiltersManager
duplication; and the Wave 5 core/state engine that will replace `collection.state` with RTK (the
writer is the seam it plugs into).
