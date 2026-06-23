# C1 Slices D/E — filter managers grounding (2026-06-23)

> 6-agent Workflow grounding + synthesis (`de-filter-managers-grounding`). The final C1 product-grid
> migration step: the filter _managers_ that orchestrate the (already-React) individual filters.

## Scope correction (the pivotal finding)

The product grid does **NOT** use `collection-filters-manager.js`. Per `src/Akeneo/Platform/Bundle/UIBundle/Resources/config/form_extensions/product/index.yml:130-140`, the product grid wires:

- **`filters-selector.ts`** (199 lines) — the **active-filters box**: instantiates/renders each enabled filter view, restores/reads state, writes via the #288 `createGridStateFilterWriter`, fetches.
- **`filters-column.ts`** (311 lines) — the **"Manage filters" add-panel**: a jQuery+underscore checkbox list of available attribute filters, appended to `$('body')` (line 284 — a de-facto portal Behat already relies on).

They communicate ONLY via 3 mediator events (`filters-column:update-filters`, `filters-selector:disable-filter`, `filters-column:update-filter`). The individual filters they host are ALREADY React (Slice C).

`collection-filters-manager.js` + `filters-manager.js` (the jQuery-UI MultiselectDecorator path, via `datafilter-builder.js`) drive the OTHER grids (export/group/channel/attribute) → **deferred to a later "other-grids" wave** (yields nothing for the product grid, drags in legacy jQuery-UI).

## Two design decisions (user-approved)

1. **Defer the RTK mirror.** The managers read `collection.state` in ~5 spots (all `state.filters` + one `totalRecords`), every one inside an imperative event handler already firing at the right moment. They orchestrate far more than they display reactively → a faithful React migration needs **no `useSelector`**. The mirror pays off only on re-render-from-outside-own-handlers (saved-view switch, hash-nav restore), which D/E doesn't introduce. Also: the managers are built from the collection alone and **cannot reach `grid.gridStore` today** — consuming the mirror would first need a store re-homing slice. Both deferred.
2. **Start with E1, the pure-helper exemplar** (the Slice C playbook: `viewComboboxHelpers` #274, `computeFilterPopupPosition` #297).

## Slice plan

| Slice           | Scope                                                                                                                                                                                                                                | Mirror | Behat selectors to preserve                                                                                                                                                                              | Risk     |
| --------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------- |
| **E1** ✅       | Extract `filters-selector` pure state helpers (`computeFilterState` = the `__name` 0/1 encoding; `shouldReloadGridState` = `(changed‖empty) && !silent`; `mergeCategoryFilter`) → tested `.ts` behind existing seams. No DOM change. | no     | none (no-op)                                                                                                                                                                                             | low      |
| **E2**          | `filters-selector.ts` active box render → React (`ReactDOM.render`/`unmount` in a `remove()` override; keep the 3-mediator contract + child-filter mounts).                                                                          | no     | `.filter-box`/`.filter-wrapper`; child `.filter-item[data-name][data-type]`; must NOT break the `.AknDropdown` ancestor of shown operator dropdowns (OperatorDecorator `getClosest`)                     | high     |
| **D1**          | `filters-column.ts` add-panel → React (portal to `<body>`, Behat-safe; keep the `$.get` attribute fetch + infinite-scroll + debounced search).                                                                                       | no     | `.filter-list.select-filter-widget`, `.ui-multiselect-checkboxes li label span`, `input[type=checkbox][value=NAME]`, `input[type=search]`, `.close`, `.AknFilterBox-addFilterButton`, bare `data-toggle` | high     |
| **other-grids** | `collection-filters-manager.js` / jQuery-UI multiselect path                                                                                                                                                                         | —      | jQuery-UI markup                                                                                                                                                                                         | deferred |

## Hazards (for E2/D1)

- **Silent double-fetch race**: `restoreFilterState` (filters-selector.ts:130-157) sets `this.silent` around enable/setValue to suppress `updateGridState` fetches; a React rewrite must preserve that guard window.
- **The diff reads the AUTHORITATIVE `collection.state`**, never the RTK mirror clone (which can lag a sync event) — else a stale read loops the fetch. (Codified in `shouldReloadGridState`'s doc.)
- **Store reachability gap**: if/when the mirror is consumed, re-home `grid.gridStore` (hang `collection.gridStore` in grid.js, or pass via the `grid_load:start (collection, gridView)` mediator event) — a small wiring slice first.

## Pattern reuse

The proven Backbone↔React seam: `ReactDOM.render(el, this.el)` in render + `unmountComponentAtNode` in a `remove()`/`shutdown()` override (ReactFilterBase / react-cell-base), inputs uncontrolled. Neither manager overrides `remove()` today → each React slice adds one. `react-redux` (^8) is installed (proven in DQI) but unused in the datagrid — D/E's React-redux adoption (if any) would be the first `<Provider>` here.
