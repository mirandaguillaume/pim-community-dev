# View-Selector Slice C — prerequisite combobox component (no-op) — Spec

> Part of the C1 product-grid view-selector wave. Grounded in
> `docs/plans/2026-06-15-view-selector-slice-c-grounding.md`. This is the **de-risking prerequisite
> PR**: it ships the React combobox + its unit tests **in isolation, NOT wired** into the inner
> selector. Zero Behat impact (not mounted). The atomic wiring + Behat-decorator rewrite lands in a
> follow-up PR. **Local constraint:** Jest is NOT run locally → validate via CI.

## Why a no-op first

Slice C is atomic for Behat-safety (removing Select2 nulls `.grid-view-selector .select2-container`,
the anchor every scenario spins on). The grounding's de-risk: build + unit/mutation-test the
combobox now, so the atomic PR contains only the wiring + decorator swap.

## Files

### Create `js/grid/viewComboboxHelpers.ts`
Pure, side-effect-free helpers reproducing the legacy Select2 `query()` data semantics:
- `toComboView` — `toSelect2Format` (text↔label fallback, keep id/type).
- `hasMore` — explicit `response.more` else the full-page heuristic (`results.length === pageSize`).
- `mergeViewPages` — append a page, **de-dupe by id** across pages (DSM throws on duplicate
  `Option.value`).
- `ensureDefaultView` — prepend the synthetic "Default view" (id 0) when the host says so.
- `idToValue`/`valueToId` — DSM `Option.value` is a string; ids are numbers (id 0 = default).

### Create `js/grid/ViewSelectorCombobox.tsx`
DSM `SelectInput` adapter (server-search mode): `disableInternalSearch` + `onSearchChange` (page 1
reset) + `onNextPage` (accumulate, dedup); each option renders the already-React `ViewSelectorLine`;
the current view is always an option (so `currentValueElement` shows it off-page); selection is
delegated to the host via `onSelectView`. Public props designed for the wiring PR:
`{currentView, defaultView, showDefaultView, searchViews, onSelectView, labels}`.

**Deferred to the wiring PR** (documented in the component): the 400ms search debounce, the
stale-response guard, and the imperative `close-selector` (needs a DSM `SelectInput` enhancement —
no external `open` prop).

### Tests
- `viewComboboxHelpers.unit.ts` — the pure logic (Stryker-safe, no DSM/React).
- `ViewSelectorCombobox.unit.tsx` — the component wiring, with `akeneo-design-system` `SelectInput`
  **mocked** to a stub (its real `usePagination`/IntersectionObserver + styled-components do not run
  cleanly in jsdom/Stryker). Covers: current view as option + value; page-1 search + current-view
  dedup; default-view prepend; cross-page accumulate + dedup on next-page; selection → `onSelectView`.
- Both registered in the per-PR Stryker `testMatch`.

## Validation
No local run. CI: build-front (bundles the new component), test-front-unit, mutation-testing-front,
prettier:check. No Behat impact (not mounted). `eslint src/**/*.js` does not touch `.ts/.tsx`.

## Out of scope (the follow-up atomic PR)
Rewriting `view-selector.js` to mount the combobox; the 2 Behat decorators
(`Select2Decorator`/`GridCapableDecorator`); the DSM imperative-close enhancement; debounce +
stale-guard; dead-code cleanup of the `pim-grid-view-selector-line` FormBuilder key.
