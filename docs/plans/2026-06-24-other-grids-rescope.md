# C1 "other-grids" wave — re-scoped (2026-06-24)

> 9-agent grounding (validate + adversarial verify + plan). Re-points the 14 non-product datagrids off
> the legacy jQuery-UI multiselect filter manager. **Verdict: GO-WITH-CHANGES** (high confidence).

## Why re-scoped

D1b (the React `FilterColumnPanel` add-panel) is **deferred** — a Selenium click never fires the React
checkbox's `change` in the multi-root PIM, so the React filter-ADD flow is broken (see PR #305 / the
`d1b-checkbox-add-deferred` memory). The re-scope **avoids D1b entirely**: re-point each of the 14 grids
to the **master `filters-column` (jQuery) + `filters-selector` (React)** pair — the same pair the product
grid already runs in production. The jQuery add-flow works; nothing new has to be debugged.

## Proven sound (re-verified against master)

- `filters-column.ts` on master is jQuery (emits `filters-column:update-filters`); `filters-selector.ts`
  is React (consumes it, writes state via `createGridStateFilterWriter`). The pair is wire-compatible.
- `datagrid_filters:loaded` is a private handshake (legacy `filters-list`↔`filters-button` only) — both replaced.
- `fetchFilters()` returns an empty resolved deferred when `attributeFiltersRoute` is undefined
  (`filters-column.ts:99`), so the 13 metadata-only grids populate from `metadata.filters` — no route, no 404.
- Exactly 14 YAML importers of `filters-list` and **zero** JS importers → D6 deletion is clean.

## The one real change: the add button (decided → hide via flag)

The 14 grids set `manageFilters:false`; the *legacy* manager hid the add button on that flag. The master
jQuery `filters-column` renders it unconditionally. **Decision: thread a `displayManageFilters` config flag**
(default `true`), set `false` on the 14 grids → byte-equivalent to today, product grid untouched.

Mechanism (D0, shared infra, ~10 LOC):
- `filters-column.ts`: `displayManageFilters?: boolean` on `FiltersConfig`; pass
  `displayManageFilters: false !== this.config.displayManageFilters` to the template.
- `filter-column.html`: wrap the toggle `<button>` in `<% if (displayManageFilters) { %> … <% } %>`.
- Each re-pointed grid yml: `config: { displayManageFilters: false }`.

## Guardrails (do not violate)

1. **Branch from `master`**, never from `c1/de-d1b-filters-column-react` (which carries the broken React shell).
2. **Never copy `attributeFiltersRoute`** from `product/index.yml` → it 404s on non-product grids. Omit it.
3. Both extensions target the existing `filters` drop-zone (the standard `form/index/index.html` has only
   `filters`+`toolbar`; no product-only `manage-filters-button` zone). The `.filter-list` panel self-appends to `<body>`.

## Slices (each a PR; branch base = master; rebase the stack forward)

| Slice | Scope | Risk |
|---|---|---|
| **D0** | flag infra + **group** pilot | medium |
| **D1** | channel, currency, group_type, association_type | low |
| **D2** | attribute, family/index, user/index-group, user/index-role | low |
| **D3** | UserManagement index, export/index-profile, import/index-profile (Behat-covered) | low |
| **D4** | family/edit **variant tab** — DEFER (body-panel orphan risk) | high |
| **D5** | associations **picker modal** (`displayAsPanel:true`, legacy-only) — DEFER | highest |
| **D6** | delete `filters-list.js`+`filters-button.js`+2 requirejs aliases — **gated on 14/14** | low |

**Full wave value lands at D3** (12/14 standard grids). D4/D5/D6 are an optional tail. D6 **keeps** the
shared chain `collection-filters-manager`→`filters-manager`→`multiselect-decorator` + `lib/multiselect/*`
(still used by `datafilter-builder` + `select-filter`).

## Per-grid table

All 14 currently wire `filters-list` + `filters-button` at `targetZone: filters` under `*-grid-container`.
Target = `filters-column` (`config: displayManageFilters:false`, NO `attributeFiltersRoute`) + `filters-selector`,
both at `targetZone: filters` under the same parent.

| # | Grid | YAML | Special |
|---|------|------|---------|
| 1 | group (D0) | `UIBundle …/group/index.yml:42/47` | pilot; search+choice |
| 2 | channel | `UIBundle …/channel/index.yml` | — |
| 3 | currency | `UIBundle …/currency/index.yml` | `browse_currencies.feature` (default-enabled short-circuit) |
| 4 | group_type | `UIBundle …/group_type/index.yml` | — |
| 5 | attribute | `UIBundle …/attribute/index.yml` | — |
| 6 | association_type | `UIBundle …/association_type/index.yml` | — |
| 7 | family/index | `UIBundle …/family/index.yml` | distinct from family/edit (D4) |
| 8 | user-group | `UIBundle …/user/index-group.yml` | — |
| 9 | user-role | `UIBundle …/user/index-role.yml` | source has cosmetic double-space after `parent:` |
| 10 | users | `UserManagement Bundle …/form_extensions/index.yml` | only non-product grid OUTSIDE UIBundle |
| 11 | export-profile | `UIBundle …/export/index-profile.yml` | only add-flow Behat (`browse_exports.feature:13`) — safe (default-enabled) |
| 12 | import-profile | `UIBundle …/import/index-profile.yml` | `browse_imports.feature:14` bare filter |
| 13 | family/edit (variant tab) | `UIBundle …/family/edit.yml:229/234` | **D4 DEFER** — tab, not index; verify body-panel cleanup |
| 14 | associations/product (modal) | `UIBundle …/associations/product.yml:11/16` | **D5 DEFER** — nested + `displayAsPanel:true`, modal scope |

## Risk register (highlights)

- **R1 add-button regression** → resolved by the `displayManageFilters` flag (this PR).
- **R3 attributeFiltersRoute → 404** → never copy it; per-grid recipe omits it.
- **R5 filter-type not registered** → before each re-point, confirm the grid's `datagrid` filter types resolve in `FilterTypeRegistry`; smoke each non-Behat grid.
- **R6 thin Behat coverage** → manual smoke per grid (no add button, filters render, filtering narrows, toolbar reveals).
- **R7/R8 D4/D5** → deferred; body `.filter-list` may orphan in tab/modal scope.
- **R9 D6 premature deletion** → delete ONLY `filters-list.js`+`filters-button.js`; keep the shared manager chain.
