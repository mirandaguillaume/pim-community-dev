# C1 "other-grids" filter-managers grounding (2026-06-23)

> 6-agent Workflow grounding + synthesis. The wave migrating every NON-product datagrid's filter
> manager off the legacy jQuery-UI multiselect path.

## Verdict: A — CONVERGE (re-point, don't rewrite)

Two fully parallel, non-overlapping filter pipelines exist:

- **Product grid (already React)**: `oro/datafilter/filters-column` (the React `FilterColumnPanel`
  add-panel, D1b) + `oro/datafilter/filters-selector` (the active-filter box, owns enable/disable +
  fetch + state writes).
- **14 non-product grids (legacy jQuery-UI)**: `oro/datafilter/filters-list` (collects/instantiates
  filter modules, emits `datagrid_filters:loaded`) + `oro/datafilter/filters-button` →
  `collection-filters-manager.js` (159) → `filters-manager.js` (527) → `multiselect-decorator.js`
  (162) → vendored `lib/multiselect/*`.

The per-filter UI (text/choice/number/select/date…) is **already React on every grid** (the shared
`FilterTypeRegistry` was re-pointed in Wave 4). So this wave touches ONLY the manager + add-panel
layer. **Re-point each non-product grid from the legacy pair to the proven React pair.**

### Why convergence is safe (verified in code)

1. **State protocol identical**: the `__name` 0/1 server-state encoding in
   `collection-filters-manager.js:85-108 _createState` is reproduced byte-for-byte by
   `filtersSelectorHelpers.ts computeFilterState` (E1); both write via `createGridStateFilterWriter`.
2. **Behat is grid-agnostic**: `DataGridContext::getDatagrid()` always uses `Base\Grid`, querying
   generic selectors (`.AknFilterBox-addFilterButton`, `div.filter-list`, `input[type="search"]`,
   `input[value="%s"]`, `.close`) that `FilterColumnPanel` emits verbatim + portals to `<body>`.
3. **Graceful degradation**: `filters-column.ts` handles non-attribute grids — `attributeFiltersRoute`
   undefined → empty deferred; filters sourced from `metadata.filters` (exactly the 13 metadata-only
   grids' need).
4. **Internal seam**: `datagrid_filters:loaded` has one emitter + one consumer (no plugin listeners).

### Caveats (not a one-line alias swap)

- **(a) Layout-zone re-parent** — the genuinely-new per-grid work: the React `filters-column` targets
  `parent: …-column-inner` / `targetZone: manage-filters-button` (product-grid-only); the 14 others use
  `parent: …-grid-container` / `targetZone: filters`. Each re-point must re-parent the React modules to
  the grid's actual toolbar zone.
- **(b) `jquery.multiselect` lib stays** — `select-filter.js` / `product_scope-filter.js` still use
  `MultiselectDecorator` for their own value dropdown. D6 deletes only the 5 manager files (~1010 LOC),
  not `lib/multiselect/*`.
- **(c) 2 special grids**: family/edit variant (filters in a form **tab**) and associations/product
  (modal picker, the only `displayAsPanel:true`).

## Slices (pilot-first, atomic per grid — do NOT bundle)

| Slice | Scope | Risk |
|---|---|---|
| **D0** pilot | `group/index.yml` → React pair; solve the layout-zone re-parent recipe on the simplest grid | medium |
| **D1** | settings grids: group_type, channel, currency, association_type | low |
| **D2** | attribute, family-index, export/import profiles | low |
| **D3** | user, user-group, user-role grids | low |
| **D4** | family/edit variant grid (form tab) — special, verify portal in tab | high |
| **D5** | associations/product (modal, `displayAsPanel:true`) — special, verify portal over modal | high |
| **D6** | delete the dead manager stack (filters-manager/collection-filters-manager/filters-list/filters-button/multiselect-decorator, ~1010 LOC) + their requirejs aliases | low |

**First slice = D0** (group grid): simplest top-level index, isolates the one new piece (layout-zone),
produces the repeatable recipe for D1–D3. Gated on D1b (#305) landing the React `filters-column` on master.
