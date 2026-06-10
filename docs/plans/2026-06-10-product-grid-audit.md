# Product Grid Audit — C1 Wave 0
Date: 2026-06-10 · Method: 5-dimension agent fan-out + adversarial verification (115 claims checked, 5 dropped)

This report synthesizes the Backbone product-grid audit feeding C1 Wave 0 of the Backbone→React migration. Every statement is grounded in a verified finding with inline `file:line` evidence. Where the evidence base is thin for a given zone, that is stated explicitly rather than inferred.

---

## 1. Zone map

The grid decomposes into seven zones plus a central glue layer. Most static imports stay intra-bundle (PimDataGridBundle); the structural blockers are dynamic `requireContext` resolution, the mediator bus, sessionStorage state, and cross-zone DOM selectors.

### 1.1 engine

**Files / responsibilities.** Core Backgrid/Backbone rendering and the collection model: `pageable-collection.js`, `datagrid/grid.js`, `datagrid/body.js`, `datagrid/row.js`, header cells (`header-cell/header-cell.js`, `header-cell/attribute-header-cell.ts`, `select-all-header-cell.js`), data cells (`select-row-cell.js`, number/string cells), and `ProductGalleryRow.tsx` (a React row that extends Backgrid.Row). `grid.js` is the central Grid class; `pageable-collection.js` is the mutable state container (see §1.6).

**Inbound (who needs this zone).** `datagrid-builder.js` statically imports `PageableCollection` (line 5), `Grid` (line 6), `GridViewsView` (line 7) — all intra-bundle (D1). `table.js` (views) drives the engine indirectly through the builder (`table.js:4,88`, D1). Filters write directly into engine state (`collection-filters-manager.js:44-45`, D3). Listeners reach into engine-rendered DOM (`column-form-listener.js:22-29`, D4).

**Outbound (what it needs).** `grid.js` imports PIM platform templates and analytics (`pim/template/common/no-data`, `pim/template/common/grid`, `pim/analytics` — `grid.js:12-14`, minor, D1). `attribute-header-cell.ts` reaches the state/fetcher layer (`pim/fetcher-registry`, `pim/user-context` — lines 1-2, notable, D1) to resolve attribute labels at render time. `ProductGalleryRow.tsx` extends `oro/datagrid/row` and renders a DSM Card via legacy `ReactDOM.render` (lines 8,12,75,92-97; D1/D5).

### 1.2 toolbar

**Files / responsibilities.** Column/display selection, pagination, grid-view dropdown, and the React view-title: `column-selector.ts`, `display-selector.js`, `pagination.js`, `pagination-input.js`, `grid-views/view.js`, `actions-panel.js`, `ProductGridViewTitle.tsx` (+ its context `product-grid-view-title-context.ts`), `locale-switcher.tsx`.

**Inbound.** `datagrid-builder.js` mounts `GridViewsView` into a page-shell selector (`gridGridViewsSelector` at line 10, append at line 117) — but that selector (`.page-title > .AknTitleContainer .span10:last`) matches **zero** DOM nodes today; the append is a silent no-op (D4, structural). `ProductGridViewTitleContext` and `LocaleSwitcher` are wired as form extensions, not via the builder (D5).

**Outbound.** `column-selector.ts` imports `DatagridState` (line 8), subscribes once to mediator `datagrid_collection_set_after` to capture the live collection (line 75), and on save calls `DatagridState.set()` + `Backbone.history.navigate(url, true)` to force a full page reload (lines 486-491; notable, D1). `actions-panel.js` extends `BaseForm` (=`BaseView`) and mounts the React `QuickExportConfigurator` (D5). `ProductGridViewTitleContext` listens to mediator `grid:view:selected` (live) and `grid:project:selected` (dead — never triggered) and re-renders (D5).

### 1.3 filters

**Files / responsibilities.** Filter bootstrap, managers, the column panel, and individual filter types: `datafilter-builder.js`, `filters-manager.js`, `collection-filters-manager.js`, `filters-column.ts`, `filters-list.js`, `filters-button.js`, `filters-selector.ts`, `abstract-filter.js`, and concrete filters (`product_scope-filter.js`, `product_category-filter.js`, etc.). `locale-switcher.tsx` is classed in the filters zone by D5 evidence.

**Inbound.** `state-listener.js` triggers `collection.trigger('updateState')` after `datagrid_filters:rendered` (D2/D3). `product_category-filter.js` consumes `grid_action_execute:product-grid:delete` to refresh the category tree (D2).

**Outbound.** `datafilter-builder.js` resolves filters via the dynamic template `oro/datafilter/{{type}}-filter` (lines 10-18, 69-74; D1) and `filters-selector.ts` duplicates that resolution independently (line 83; D1). Heavy DOM coupling: `datafilter-builder.js` prepends FiltersManager into the datagrid container (`:87`); `product_scope-filter.js` relocates into `[data-drop-zone="column-context-switcher"]` and reads `#add-filter-select` (lines 48-63); `filters-column.ts` appends to `$('body')` (line 284); `abstract-filter.js` binds scroll on `.column-inner` (line 142); `filters-manager.js` reads `.AknHeader`/`.AknColumn` for offsets (lines 504-528) — all structural/notable D4.

### 1.4 views

**Files / responsibilities.** UIBundle wrappers that present the grid: `product/grid/table.js` (bootstrap entry, also acts as glue), `view-selector.js`, `grid-views/view.js` (view dropdown), the pagination views, and the no-data block in `grid.js`.

**Inbound.** `table.js` is the bootstrap entry via `pim-product-index-grid` (form extension `product/index.yml:179`, D3). `grid-views/view.js` is imported and unconditionally instantiated by `datagrid-builder.js` (lines 7,117; D3).

**Outbound.** `grid-views/view.js` listens to collection `updateState`/`beforeFetch` to re-render and reads `collection.state.gridView` to highlight the active view (lines 63-64,104,134; notable, D3). Pagination views read `collection.state` (currentPage/firstPage/lastPage/totalRecords/pageSize) and mutate `state.currentPage` via backbone-pageable page getters, bypassing `updateState()` (`pagination.js:138-141`, `pagination-input.js:71-72,94,115`; notable, D3).

### 1.5 actions

**Files / responsibilities.** Row/mass action classes and the actions panel: `abstract-action.js`, `model-action.js`, `mass-action.js`, `navigate-action.js`, `delete-action.js`, `ajax-action.js`, `mass-delete-action.js`, `sequential-edit-action.js`, `attribute-mass-delete-action.ts`, and UIBundle children `delete-product-action.js`, `toggle-product-action.js`, `navigate-product-and-product-model-action.js`, `delete-attribute-action.tsx`.

**Inbound.** Resolved by the `{{type}}-action` template in `datagrid-builder.js` (lines 13,84-96; D1). Concrete row types: navigate-product-and-product-model, delete-product, toggle-product, delete-attribute, ajax; mass types: edit, sequential_edit, mass_delete, export (with `mass-action` as fallback for reserved types). `refresh-collection`/`reset-collection` are registered but never used as `type:` values — unreachable via this mechanism (D1).

**Outbound.** Deep cross-bundle prototype chains: UIBundle `navigate-product-and-product-model-action.js` → `NavigateAction` → `ModelAction` → `AbstractAction` (4 levels, two bundles; `:6` each, D1). `abstract-action.js` does a synchronous `requireContext('oro/' + frontend_type + '-widget')` at execute time (lines 172,185; intra-bundle, D1). `delete-action.js` fires `grid_action_execute:product-grid:delete` (hardcoded grid name) and `datagrid:doRefresh` (lines 68-69; structural, D2). React modals: `delete-attribute-action.tsx` (ephemeral div on body, lines 13-19,27-34) and `attribute-mass-delete-action.ts` (`ReactDOM.render` into `this.el`, lines 79-87) — D1/D5. Several actions reach the page shell: `sequential-edit-action.js:22` and `mass-delete-action.js:86` append into `.hash-loading-mask` (notable, D4).

### 1.6 state

**Files / responsibilities.** The state subsystem is split across (a) `pageable-collection.js`'s `state` object — a **plain mutable JS object, not a Backbone model** (`pageable-collection.js:52-66`, D3); (b) `datagrid/state.js` (`DatagridState`), a sessionStorage façade firing `grid:<alias>:state_*` mediator events (state.js:45-57,7-11; D3); (c) `state-listener.js`, which persists state to sessionStorage on `grid_load:complete` (lines 27-68; D3); and (d) the column-form listeners (`abstract-listener.js`, `column-form-listener.js`, `oro-column-form-listener.js`) that bridge model edits to selection events.

**Inbound.** Six+ independent direct mutation sites write `collection.state` with **no single source of truth** (D3): `parse()` writes totals (`:261-264`); `onRemove()` decrements totalRecords (`:139-143`); `setSorting()` rewrites sorters in place with no event (`:555-607`); `collection-filters-manager` writes `state.filters` (`:44-45`) and `state.currentPage=1` (`:33`); `grid.js setAdditionalParameter/removeAdditionalParameter` mutate `state.parameters` (`:474-493`); the mediator `datagrid:setParam:<gridName>` command lets any external module mutate `state.parameters` with no guard (`grid.js:279`, producer `associations.js:453`; D2/D3). The controlled path `updateState()` (a **merge** via `_.extend({}, this.state, state)`, `:285`) fires `'updateState'` but is bypassed by the majority of mutations.

**Outbound.** `mass-actions.ts` reads `datagrid.getSelectionState()`, coupling UIBundle state to the Backbone grid object (lines 1-15; notable, D1). The selection pipeline (`column-form-listener.js`) fires `datagrid:selectModel/unselectModel` and `column_form_listener:initialized` on the mediator with **zero in-scope consumers** (lines 70,107; structural, D2) — all consumers are external (item-picker, associations, form/common/grid).

### 1.7 glue

**Files / responsibilities.** Bootstrap hubs and bridges: `datagrid-builder.js`, `datafilter-builder.js`, `product/grid/table.js`, `state-listener.js`, plus `category-tree.js`/`category-switcher.js` (root-form glue).

**Inbound.** `table.js` is the bootstrap entry; it writes `$.data()` into `#grid-<gridName>` and calls `datagridBuilder([StateListener])` (D3). `datagrid-builder.js` re-reads that same node via `[data-type="datagrid"]` — a **DOM-node-as-message-bus** coupling between producer (`table.js:79-82`) and consumer (D4, structural).

**Outbound.** `datagrid-builder.js` owns the four dynamic resolution sites — `{{type}}-cell`, `{{type}}-header-cell`, `{{type}}-action` (lines 11-13) and `requireContext(metadata.options.rowView)` (line 202) — none statically analyzable (D1). `table.js` promotes `displayTypes.gallery.rowView` to `options.rowView` at runtime (line 116; D1). `BaseView.renderReact()` is the canonical bridge, deliberately using legacy `ReactDOM.render` (base.ts:263-272; D5). `category-tree.js` scrapes JSTree DOM it owns (`#tree`, lines 12,89,99; notable, D4).

**Evidence sufficiency note.** The producer of `column_form_listener:set_selectors:<gridName>` and of `div.toolbar` (targeted by `state-listener.js:49,60-62`) were **not** found within scope (D2/D4). These are flagged as out-of-scope dependencies, not invented.

---

## 2. Mediator event matrix

Three concurrent buses operate: the global mediator singleton, per-object Backbone events, and jQuery DOM events on the grid `$el`. "Crosses zones?" marks events whose producer and at least one consumer live in different zones (or outside scope). All rows are verified (D2).

| Event | Producers (file:line) | Consumers (file:line) | Crosses zones? |
|---|---|---|---|
| `datagrid_collection_set_after` | datagrid-builder.js:124 | datafilter-builder.js:27, filters-list.js:36, pagination-input.js:41, state-listener.js:28, column-form-listener.js:19; **ext** item-picker.js:66-67 | Yes (engine→filters/toolbar/state/glue + external) |
| `grid_load:complete` | grid.js:424 | actions-panel.js:37, pagination-input.js:45, state-listener.js:29, oro-column-form-listener.js:43, column-form-listener.js:49-51; **ext** item-picker.js:68, grid-title.js:15; grid/mass-actions.js:44 (**dead — bus mismatch**, listenTo getRoot) | Yes |
| `datagrid_filters:rendered` | datafilter-builder.js:89, filters-list.js:66 | product_scope-filter.js:29-30, state-listener.js:41-44 (→ triggers `updateState`) | Yes (filters→state) |
| `datagrid_filters:build.post` | datafilter-builder.js:94, filters-button.js:37 | product_category-filter.js:58 (sets up filtersManager cross-wiring) | No (filters-internal) |
| `datagrid_filters:loaded` | filters-list.js:65 | filters-button.js:16 (→ creates FiltersManager, fires build.post) | No (filters-internal) |
| `grid_action_execute:product-grid:delete` | delete-action.js:68 (hardcoded grid name) | product_category-filter.js:73 | Yes (actions→filters) |
| `datagrid:doRefresh:{gridName}` | delete-action.js:69; **ext** form/common/grid.js:143, creation/modal/client.js:17, family-variant/save.js:50, associations.js:457, family-variant.js:36 | grid.js:298 | Yes (actions/external→engine) |
| `datagrid:setParam:{gridName}` | **ext** associations.js:453 (no in-scope producer) | grid.js:279 | Yes (external→state/engine) |
| `datagrid:restoreState:{gridName}` | oro-column-form-listener.js:153 | grid.js:287 | No (listener/grid internal) |
| `datagrid:selectModel/unselectModel:{gridName}` | column-form-listener.js:107 | **ext** item-picker.js:64-65, form/common/grid.js:38,45, associations.js:116,124 (no in-scope consumer) | Yes (state→external only) |
| `column_form_listener:initialized` | column-form-listener.js:70 | **ext** item-picker.js:69, associations.js:560 (no in-scope consumer) | Yes (state→external only) |
| `column_form_listener:set_selectors:{gridName}` | **none in scope** | column-form-listener.js:59-65 | Yes (external→state) |
| `grid:{alias}:state_set / state_changed / state_reset` | state.js:51,53,64 | **ext** grid/view-selector.js:57 via forwarded-events bridge (requirejs.yml:19-21 + view-selector.js:49-51) — **NOT dead** | Yes (state→external, bridged) |
| `grid_action:navigateAction:preExecute` | navigate-action.js:54 | **none anywhere** — dead channel | n/a (dead) |
| `grid_action_execute:{gridName}:{actionName}` | abstract-action.js:138 (once-trigger) | abstract-action.js:135 (once-listen) — confirmation trampoline | No (internal one-shot) |
| `collection-filters:createState.post` | collection-filters-manager.js:95 (Backbone obj event) | product_category-filter.js:59 | No (filters-internal) |
| `hash_navigation_request:start` | **ext** Oro navigation (no in-scope producer) | datafilter-builder.js:28, filters-manager.js:96, state-listener.js:33, multiselect-decorator.js:77 | Yes (external→4 in-scope teardowns) |
| `beforeFetch / updateState / beforeReset` (collection) | pageable-collection.js:383/287/275 | collection-filters-manager.js:16-18 (all three), grid-views/view.js:63-64 (no reset); state-listener.js:44 also triggers updateState | Yes (engine→filters/views) |
| `backgrid:selected` (model→collection bubble) | select-row-cell.js:63; ProductGalleryRow.tsx:53-55 | select-all-header-cell.js:35, row.js, product-row.js; **ext** grid/mass-actions.js (also producer at 93/109/119) | Yes (engine↔external) |
| `datagrid:change:{gridName}` (jQuery DOM) | grid.js:223 ($el.trigger) | abstract-listener.js:42 ($gridContainer.on) | No (engine→state listener) |
| `update / update_label / enable / disable` (filter objs) | abstract-filter.js:268,133,154; product_category-filter.js:40,141 | filters-manager.js:87-88; category-tree.js:52,56; cross-wire filtersManager←this at product_category-filter.js:62 | No (filters-internal) |

**Two bus mismatches to fix before migration (D2, structural):**
1. `grid_load:complete`/`grid_load:start` are fired on the **mediator** by `grid.js`, while `display-selector.js` and `grid/mass-actions.js:43` subscribe via `listenTo(getRoot(), …)` (the form-root bus). **Correction (wave 1):** on the product index page these listeners ARE alive — `form_extensions/product/index.yml:7-9` declares `forwarded-events: {grid_load:start, grid_load:complete}` on the root form `pim-product-index`. The mismatch remains a real risk for any page whose root form lacks that bridge, and any migration must replicate the bridge explicitly.
2. `grid:{alias}:state_changed` is fired on the mediator but `grid/view-selector.js:57` listens on `getRoot()` — here a forwarded-events bridge IS configured (requirejs.yml:19-21 + view-selector.js:49-51), so it works. Any migration must replicate this bridge explicitly.

---

## 3. Top-10 dangerous couplings (ranked)

Ranked by blast radius for a Strangler-Fig replacement. All are verified findings.

| # | Coupling | Evidence | What breaks if the owner zone goes React |
|---|---|---|---|
| 1 | **DOM-node-as-message-bus**: `table.js` writes `$.data()` + metadata into `#grid-{gridName}`; `datagrid-builder.js` re-reads it via `[data-type="datagrid"]`. Producer/consumer coupled through a mutable DOM node. | table.js:79-82; datagrid-builder.js:40-50,107 (D4) | Replacing either side breaks bootstrap entirely; the data channel vanishes. Must be replaced with an explicit props/context contract first. |
| 2 | **Four dynamic `requireContext` resolution sites** ({{type}}-cell/header-cell/action, rowView, {{type}}-filter, plus filters-selector's own) — none statically analyzable; YAML→DOM JSON→JS-bootstrap is the source of truth. | datagrid-builder.js:11-13,202; datafilter-builder.js:10-18; filters-selector.ts:83; reactCell.tsx:25 (D1) | A bundler-based React build cannot tree-shake or resolve these; every registry must be re-implemented explicitly. Blocks any "just import the component" approach. |
| 3 | **Selection pipeline is entirely cross-boundary**: `column-form-listener` fires selectModel/unselectModel + initialized on mediator with zero in-scope consumers; `set_selectors` has no in-scope producer. | column-form-listener.js:70,107,59-65; ext item-picker/associations (D2) | Migrating the grid's selection in isolation is impossible — it only makes sense jointly with external form modules. Silent breakage of item-picker & associations. |
| 4 | **`#grid-{name}` cross-three-module reach**: `product_scope-filter` selects the datagrid mount node then finds `#add-filter-select` (FiltersManager's node) inside it; also relocates itself into `[data-drop-zone="column-context-switcher"]`. | product_scope-filter.js:48-63 (D4) | React-owning any of grid/filters/shell breaks this selector chain; the scope filter silently fails to mount or de-dupe. |
| 5 | **`hash_navigation_request:start` controls 4 teardowns** from an out-of-scope Oro producer. | datafilter-builder.js:28, filters-manager.js:96, state-listener.js:33, multiselect-decorator.js:77 (D2) | Replacing the Oro navigation layer leaves four listeners dangling indefinitely (leaks). React lifecycle must own teardown explicitly. |
| 6 | **No single source of truth for state** — 6+ direct mutation sites bypass `updateState()`; state is a plain mutable object. | pageable-collection.js:261-264,139-143,555-607; collection-filters-manager.js:44-45,33; grid.js:474-493; mediator setParam grid.js:279 (D3) | A React store cannot wrap the existing object incrementally; every mutator must be rerouted before any reducer-based replacement is trusted. |
| 7 | **`grid.js._updateNoDataBlock()` toggles a global unscoped `[data-drop-zone="toolbar"]`**. | grid.js:40-41,449-454 (D4) | If a React shell owns the toolbar, this jQuery show/hide hits the wrong/nonexistent node — empty-state toggling silently breaks. |
| 8 | **`column-form-listener` rewrites Backgrid's rendered header** (`table.grid thead th`, `.empty()/.html()`) from a listener module. | column-form-listener.js:22-29 (D4) | A React header (cells wave) is clobbered by jQuery injection of the select-all checkbox; must move checkbox into the React header first. |
| 9 | **`datafilter-builder` prepends FiltersManager into the datagrid container it does not own**; `filters-column.ts` appends to `$('body')`. | datafilter-builder.js:87; filters-column.ts:284 (D4) | React-owning the datagrid container drops the prepend; filter UI floats outside any React root, incompatible with portals/unified layout. |
| 10 | **Deep cross-bundle action prototype chains** (UIBundle action → NavigateAction → ModelAction → AbstractAction, 4 levels, two bundles). | navigate-product-and-product-model-action.js:6; navigate-action.js:6; model-action.js:4; abstract-action.js:15 (D1) | Cannot port one action without the whole chain; `requireContext('oro/'+frontend_type+'-widget')` at execute time (abstract-action.js:172,185) further blocks static extraction. |

*Demoted from the danger list (verified non-issues):* the `gridGridViewsSelector` append is a no-op (matches zero nodes; datagrid-builder.js:10,117); `reactCell` is dead code for the product grid (no column uses `type: react`; reactCell.tsx + product.yml); `table.js`'s `#product-grid` class write targets a wrapper it legitimately owns, not the builder's mount node (table.js:118).

---

## 4. Recommended wave order

**Provisional order was: toolbar → cells → views → filters → core → teardown.**

Findings force three explicit revisions. The revised order and rationale (each driven by cited findings):

| Wave | Zone | Prerequisites | Risk | Rationale (findings) |
|---|---|---|---|---|
| 1 | **toolbar** (view-title + locale-switcher + quick-export panel) | Fix LocaleSwitcher async-mount race (locale-switcher.tsx:30-51 vs base.ts:257-259) | Low | Confirmed self-contained React surfaces already using the canonical bridge: `ProductGridViewTitleContext` (clean exemplar, no remove override needed — base.ts:302-307) and `ActionsPanel`/`QuickExportConfigurator` (pure React tree, single outgoing callback — D5). The `gridGridViewsSelector` append is already a no-op (datagrid-builder.js:10,117), so removing GridViewsView's shell mount costs nothing. **Kept first.** |
| 2 | **cells / header (read-only cells first)** | Re-implement `{{type}}-cell` + `{{type}}-header-cell` registries explicitly (datagrid-builder.js:11-12); **do NOT generalize reactCell** (dead code, leaks — reactCell.tsx:22-52) | Medium | Cells are leaf nodes with a narrow data contract (`this.model.get`). But the select-all checkbox is injected into `thead th` by `column-form-listener.js:22-29`, and `attribute-header-cell.ts:1-2` pulls fetcher/user-context — so header cells must be migrated **with** the listener's checkbox logic, not before. **Revision 1: split "cells" into read-only data cells (here) vs selection header (deferred to wave 5 with state).** |
| 3 | **views (pagination + grid-view dropdown + no-data)** | State read-contract from wave 5 must exist, OR shim reads against the live `state` object | Medium | Pagination and `grid-views/view.js` only **read** `collection.state` and call backbone-pageable getters (pagination.js:138-141; grid-views/view.js:63-64,134; D3). They mutate `state.currentPage` via getters, not `updateState()` — tolerable while the engine stays Backbone. `grid.js:360` no-data reads `state.filters`. Low write-risk, so views can precede a full state rewrite. **Kept third.** |
| 4 | **filters** | Replicate `{{type}}-filter` resolution (datafilter-builder.js + filters-selector.ts:83); untangle 3 page-shell DOM reaches (product_scope-filter.js:48-63, abstract-filter.js:142, filters-manager.js:504-528, filters-column.ts:284) | **High** | Filters are the densest coupling cluster: they write engine state directly (collection-filters-manager.js:44-45,33), prepend into the datagrid container (datafilter-builder.js:87), float panels on `$('body')`, and carry the dual-FiltersManager duplication risk (datafilter-builder.js:86+94 vs filters-button.js:33+37) plus the category-filter cross-wiring (product_category-filter.js:59-62). **Revision 2: filters must come AFTER state has a single source of truth, not before core — moved to depend on wave 5.** |
| 5 | **core / state engine** (PageableCollection, grid.js, selection listeners) | All write-mutation sites rerouted; selection pipeline coordinated with external item-picker/associations | **Highest** | No single source of truth (6+ direct mutators — D3); selection events have zero in-scope consumers (column-form-listener.js:70,107 — D2); the `#grid-{name}` `$.data` bus (table.js:79-82) and `datagrid:setParam` external mutation (grid.js:279) must all be replaced together. **Revision 3: filters (wave 4) and the selection header (from wave 2) both DEPEND on this wave's single-source-of-truth state; in practice core/state should be designed first even though it lands last, because waves 2-4 shim against it.** |
| 6 | **teardown** (remove Backbone builders, mediator bridges, sessionStorage façade) | All above migrated | Medium | Final removal of `datagrid-builder.js`, `datafilter-builder.js`, `state-listener.js`, the forwarded-events bridge (requirejs.yml:19-21), and the `hash_navigation_request:start` teardown handlers (D2). Cannot precede core because those bridges keep the dead listeners alive. |

**Summary of revisions vs provisional order:**
- **Revision 1** — "cells" is split: read-only cells in wave 2, the select-all **header** checkbox deferred to wave 5, because `column-form-listener.js:22-29` injects it into Backgrid's rendered `thead` and feeds the selection pipeline (D2/D4).
- **Revision 2** — filters move to **depend on** core/state (not precede it), because they directly write `collection.state` (collection-filters-manager.js:44-45,33) and would otherwise write into a half-migrated store.
- **Revision 3** — core/state is **designed first, landed last**: waves 2-4 shim against its read/write contract. The provisional "core before teardown" ordering is preserved, but core is now an explicit prerequisite for waves 2 (header), 4 (filters), and 6.

---

## 5. Bridge strategy

**Current mechanisms (D5).** All five React surfaces deliberately use the legacy `ReactDOM.render()` API. `BaseView.renderReact()` documents why (base.ts:263-272): React 18's `createRoot` scopes event delegation to the container, which breaks Selenium/ChromeDriver native-event bubbling on Backbone-managed DOM. `BaseView` exposes `renderReactElement()` (bare render) and `renderReact<T>()` (wraps ThemeProvider+DependenciesProvider); both set `this.reactRef`, and `BaseView.remove()` calls `unmountReact()` → `ReactDOM.unmountComponentAtNode(reactRef)` (base.ts:257-287,302-307). The canonical exemplar is `ProductGridViewTitleContext` (mediator → re-render, no custom remove — D5).

**Verdict on generalizing `reactCell`.** Do **not** generalize it as-is. It is currently **dead code for the product grid** — no column uses `type: react` (reactCell.tsx + product.yml; D5 + minor). Worse, it has **no `remove()` override and no `unmountComponentAtNode()`**, so React trees leak when Backgrid disposes cells on refresh (reactCell.tsx:22-52), and it **re-creates the ThemeProvider+DependenciesProvider tree on every render call** (reactCell.tsx:39-44). Its one redeeming trait — `requireContext`-based per-column component injection with `convertToType()` prop coercion and a `refreshCollection` escape hatch (reactCell.tsx:24-37) — is the right shape, but the lifecycle must be fixed before it becomes the cell-wave pattern.

**Recommended pattern for waves 1-5.** Adopt the `BaseView.renderReact()` + mediator-bridge pattern proven by `ProductGridViewTitleContext` and `ActionsPanel`/`QuickExportConfigurator`:
1. Extend `BaseView`; wire mediator listeners in `configure()` whose handlers call `render()` (product-grid-view-title-context.ts:24-36).
2. `render()` calls `this.renderReact(Component, derivedProps, this.el)` — automatic provider wrapping (base.ts:279-287).
3. Rely on `BaseView.remove()` for unmount; add a subclass `remove()` only when a non-Backbone node is used (the ephemeral-container pattern of `DeleteAttributeAction`: create div on `body`, remove in close callback — delete-attribute-action.tsx:13-19,27-34 — is the safest choice for modal dialogs but does not generalize to in-grid components).
4. Keep legacy `ReactDOM.render()` until Selenium/ChromeDriver event-delegation is no longer a constraint.

**Gaps to fix first (before generalizing any pattern):**
- **ReactCell unmount lifecycle** — add `remove()` → `unmountComponentAtNode` and hoist providers above the render loop (reactCell.tsx:22-52,39-44).
- **LocaleSwitcher async race** — `renderReactElement` runs inside a `.then()`, so `reactRef` may be set after `remove()` has already detached the node, defeating cleanup (locale-switcher.tsx:30-51 vs base.ts:257-259). Guard against removal-during-fetch before reusing this pattern.
- **ProductGalleryRow re-render cost** — it re-renders the full React tree on every `backgrid:selected` toggle (ProductGalleryRow.tsx:103-106); acceptable now (it correctly unmounts, lines 93-94) but must become a prop diff at scale.
- **BaseView.remove() detached-node warning** — `super.remove()` detaches the DOM before `unmountReact()` runs, so React 18 logs a warning when unmounting from a detached root (base.ts:302-306). Harmless but noisy; reorder unmount-before-detach when revisiting BaseView.
- **createRoot migration is out of scope for waves 1-5** — explicitly blocked by the Selenium constraint (base.ts:263-272); revisit only after E2E moves off ChromeDriver native events.

---

## Appendix — minor findings

- `datagrid/grid.js` imports `pim/template/common/no-data`, `pim/template/common/grid`, and `pim/analytics`, making the central Grid class depend on PIM platform templates/analytics. (grid.js:12-14)
- `state-listener.js` depends on `AbstractListener` (engine) and `DatagridState`, a state→engine cross-cluster import for the listener base. (state-listener.js:3)
- `actions-panel.js` reads `this.getParent().count` to obtain the product count via Backbone form-tree traversal, an invisible coupling feeding the React QuickExportConfigurator. (actions-panel.js:144)
- `datagrid:getParams` is fully internal to the product-grid root form: produced by table.js:234 (`getRoot().trigger`), consumed by category-tree.js:33 — no mediator, correctly scoped. (table.js:234)
- `pim_enrich:form:category_updated` is root-form-internal: produced by category-tree.js:57,75, consumed by category-switcher.js:23. (category-tree.js:57)
- `grid:third_column:toggle` is a self-contained root-form sidebar-toggle event: produced by category-switcher.js:69 and category-tree-done.js:29, consumed by category-switcher.js:24. (category-switcher.js:69)
- Backbone `preExecute`/`postExecute` on action objects: produced by abstract-action.js:123,126, consumed by navigate-action.js:28 (which then fires the dead `grid_action:navigateAction:preExecute`). (abstract-action.js:123)
- Backbone `rowClicked`: produced by row.js:66 and body.js:75, consumed by body.js:74 and grid.js:234 (re-triggers on the grid, runs row-click action). (row.js:66)
- FiltersManager `rendered` event (filters-manager.js:308) has no in-scope consumer — likely consumed by external modules on the instance. (filters-manager.js:308)
- FiltersManager `updateFilter`/`disableFilter`/`updateList` (filters-manager.js:119,129,172) have no in-scope consumer — observable hooks for external consumers. (filters-manager.js:119)
- `datagrid-view-fetcher.js`/`datagrid-view-saver.js` are pure REST wrappers with no access to `PageableCollection.state`. (datagrid-view-fetcher.js:13-41; datagrid-view-saver.js:14-18)
- `datagrid-view-remover.js` sends a DELETE with no state interaction. (datagrid-view-remover.js:13-17)
- `refresh-collection-action.execute()` sets a transient `refresh` flag in `state.parameters` around a fetch, then removes it. (refresh-collection-action.js:27-32)
- `filters-column.ts` globally queries `$('.filter-loading').show/hide()` — could match elements outside the datagrid scope. (filters-column.ts:302,306)
- `product/grid/view-selector.js` calls `form.setElement('.view-selector')`, assuming a page-template-owned node pre-exists. (view-selector.js:29)
- `multiselect-decorator.js` falls back to a global `$('.ui-multiselect-menu.pimmultiselect')` when the widget API throws, reaching any popup in the document. (multiselect-decorator.js:137)
- `ProductGridViewTitleContext` has no explicit `remove()`; relies on `BaseView.remove()` → `unmountReact()` which is safe when `reactRef` is non-null. (product-grid-view-title-context.ts:1-56; base.ts:302-307)
- The `oro/datagrid/react-cell` alias (requirejs.yml:744 → `pimui/js/datagrid/reactCell`) is registered but no product-grid column config uses it — ReactCell is unused in scope. (requirejs.yml:744; product.yml)

*Method note: 115 claims were adversarially checked; 5 were dropped — 4 refuted on evidence, 1 lost to a verifier infrastructure timeout (a D2 mediator-events claim; dropped conservatively rather than admitted unverified). The minor findings above are appendix-grade and were not independently re-verified.*
