# Grounding — Product-Grid Filter UI: Backbone → React Strangler

> 8-agent Workflow (2026-06-19). The filter UI is the audit's 'densest coupling cluster'. File:line refs verified against the working tree.

# Grounding Report — Product-Grid Filter UI: Backbone → React Strangler

All file:line references below were verified against the working tree on branch `c1/wave4-filter-state-contract`. Where the 7 reader maps disagreed with the code, I corrected to the code (noted inline).

## 1. Filter-UI architecture (container / orchestration)

There are **three coexisting bootstrap stacks**, not one. A migration must know which is live per grid or it will double-instantiate or silently drop the filter bar.

- **Stack A — product-grid column panel** (`product/index.yml`, "filtersAsColumn" mode). Two TS BaseView classes:
  - `datafilter/filters-column.ts` — sliding side panel. Listens `datagrid_collection_set_after` (`:266`), fetches attribute filters from REST, renders checkbox groups via `_.template`, fires `filters-column:update-filters` (`:250`). **Appends `.filter-list` directly to `$('body')` (`:284`)**; manual cleanup in `shutdown()` (`:295-298`); global `$('.filter-loading').show/hide()` (`:302,:306`). Pure Backbone/jQuery, no React.
  - `datafilter/filters-selector.ts` — mounting orchestrator. Listens `filters-column:update-filters` (`:59`), resolves each type via `resolveFilterModuleId` (`:75`) + `requireContext` (`:76`, **synchronous** — assumes modules already AMD-loaded), instantiates filter modules, appends their `.el`, restores URL state, writes back through `createGridStateFilterWriter` (`:190`). Fires `filters-column:init` (`:126`) + `datagrid_filters:rendered` (`:127`). Owns `getState()` (`:159`) — one of three independent state serializers.
- **Stack B — modal/tab grids** (user mgmt, associations, export profiles, currencies, groups, attributes): `filters-list.js` (loads modules via the **old `{{type}}-filter` string template**, NOT yet on `FilterTypeRegistry`) -> fires `datagrid_filters:loaded` -> `filters-button.js` creates a `collection-filters-manager` (`:33`), `this.$el.append(...)` (`:35`), fires `datagrid_filters:build.post` (`:37`).
- **Stack C — product-group-grid only**: `datafilter-builder.js`. Listens `datagrid_collection_set_after`, `combineOptions` (`:92`), instantiates `collection-filters-manager` (`:76`), `this.$el.prepend(...render().$el)` (`:77`), fires `datagrid_filters:rendered` (`:79`) + `datagrid_filters:build.post` (`:84`). Already on `FilterTypeRegistry` (`:7,:63`).

**Shared base managers** (both pure Backbone):
- `filters-manager.js` (`Backbone.View`) — owns `#add-filter-select` multiselect (MultiselectDecorator); positions the add-filter widget via `$el.offset().left`.
- `collection-filters-manager.js` extends it — holds `stateWriter = createGridStateFilterWriter(collection)` (`:21`), hooks `beforeFetch`->`setFilters(_createState())` (`:53`), `updateState`->`_applyState` (`:63`), `resetPage()` (`:40`), fires `collection-filters:createState.post` (`:105`).

### Dual-FiltersManager duplication (confirmed)
`datafilter-builder.js:76-77` (prepend) and `filters-button.js:33-35` (append) independently `new FiltersManager(options)` and both fire `datagrid_filters:build.post`. The product grid uses `filters-column.ts`/`filters-selector.ts` instead — a **third** state path. Three independent `_createState`/`getState` serializers exist (`collection-filters-manager._createState`, `filters-selector.getState`, builder inline) that must all agree on the `name` / `__name` encoding.

## 2. The 24 concrete filters by archetype

All 24 live in `datafilter/filter/` and extend `abstract-filter.js` (`Backbone.View`) directly or transitively. Canonical type->module map is `FilterTypeRegistry.ts` (22 module entries + 6 aliases: `string->choice`, `choice->select`, `boolean->select`, `selectrow->select-row`, `multichoice->multiselect`, `identifier->identifier`).

- **Archetype A — text popup (operator + text input), value `{type,value}`**: `text-filter`, `choice-filter`, `number-filter`, `identifier-filter`, `parent-filter`, `uuid-filter`. Outside-click via `document.addEventListener('mousedown')` (`text-filter.js:92`).
- **Archetype B — Select2 multi-tag popup, value `{type,value:string[]}`**: `select2-choice-filter`, `select2-rest-choice-filter`. Has a **synchronous `$.ajax`** cache-miss fetch that must become async in React.
- **Archetype C — date range popup, value `{type,value:{start,end}}`**: `date-filter`, `datetime-filter`. Uses the third-party `datepicker` AMD module + `pim/date-context`.
- **Archetype D — composite number + dimension**: `metric-filter` (`{type,value,unit}`, async `FetcherRegistry.getFetcher('measure').fetchAll()` before first render), `price-filter` (`{type,value,currency}`).
- **Archetype E — inline select / special / search**: `select-filter` (MultiselectDecorator), `multiselect-filter`, `ajax-choice-filter` (lazy fetch on `show()`), `select-row-filter` (Backgrid `backgrid:getSelected/selectAll`), `product_completeness-filter` (empty `SelectFilter.extend({})`, 118 bytes), `grouped-variant-filter` (`moveFilter()` -> `.search-zone`), `none-filter`, `search-filter`, `label_or_identifier-filter`, **`product_scope-filter`**, **`product_category-filter`**.

**Correction to the maps:** `search-filter.js` has **no `moveFilter()` and no `.search-zone`** — only `label_or_identifier-filter.js`, `grouped-variant-filter.js`, and `product_scope-filter.js` do (verified by grep). This makes plain `search-filter` strictly simpler than the maps stated and the cleanest first exemplar.

## 3. The managers (state surface)

`createGridStateFilterWriter.ts` (Wave 4, `setFilters`/`resetPage`) is the **single legal filter->state write surface**, already plain JS with no Backbone coupling — Wave 5 swaps only this file to an RTK slice. `GridState.ts` defines `FilterValues` encoding: `name->{type,value}` (active non-empty), `__name->1` (active empty), `__name->0` (explicitly disabled), absent (default). This `__name` double-underscore protocol is shared by all three serializers and the URL state — **untouchable** until Wave 5.

## 4. Coupling cluster (the "densest" — each verified file:line)

1. `datafilter-builder.js:77` — `this.$el.prepend(filtersList.render().$el)` into the grid container it does not own.
2. `product_scope-filter.js:54` — `this.$el.prependTo($('[data-drop-zone="column-context-switcher"]'))` — cross-zone DOM teleport into a page-shell slot.
3. `product_scope-filter.js:33,56-62` — reads `$('#grid-'+collection.inputName)` (fallback `[data-type="datagrid"]:first`), finds `#add-filter-select` inside, removes its own option.
4. `abstract-filter.js:144-145` — `$('.column-inner').off(ns).on(ns, ...)` — **global, unscoped** scroll binding for criteria repositioning (ns = `scroll.filterCriteria-${cid}`).
5. `abstract-filter.js:444-449` — `_updateCriteriaSelectorPosition()` sets `position:fixed` computed from `$('body')` size.
6. `filters-column.ts:284` — `.filter-list` appended to `$('body')` (outside any React root); `:276` removes globally; `:302,:306` global `$('.filter-loading')`.
7. `filters-manager.js` — add-filter widget positioned via `$el.offset().left` (breaks at `{0,0}` in a detached/portal node).
8. `datafilter-builder.js:76` + `filters-button.js:33` — dual FiltersManager instantiation (see section 1).
9. `product_category-filter.js:58-62` — on `datagrid_filters:build.post`, reverse-wires `filtersManager.listenTo(this,'update',filtersManager._onFilterUpdated)` and subscribes to `collection-filters:createState.post`; also `filters-column:init`/`update-filter` (`:65-69`) and `grid_action_execute:product-grid:delete` (`:73`). Embeds a `pim/tree/view` TreeView (`:48`). Most cross-coupled leaf.
10. `collection-filters-manager.js:21,40,53` — all writes via `stateWriter` (Wave-4 single surface). `product_scope-filter.js:71,77,130,135` **bypasses** it, writing `DatagridState`/`UserContext` directly.

## 5. Behat contracts (load-bearing — verified)

- `Grid.php:404` queries `.filter-item[data-name="%s"]`; `:410-412` reads `data-type` to pick a decorator from `filterDecorators` (`:25`). `abstract-filter.js:19` sets `className:'AknFilterBox-filterContainer filter-item oro-drop'`; `:105,:108` stamp `data-name`/`data-type`. **These three are the spine of the entire filter Behat suite.**
- `BaseDecorator.php`: `.filter-criteria-selector` (`:18` open), `open-filter` class (`:22`), `.disable-filter` (`:38` remove), `.filter-criteria-hint` (`:52`).
- `SearchDecorator.php`: `filter()` triggers `$('.filter-item[data-name][data-type] [name="value"]').trigger('change')` (`:39`); `search()` queries the **global** `.search-filter input[name="value"]` (`:78`), removes `readonly`, focuses. `label_or_identifier`->`SearchDecorator` (`Grid.php:143`); the page "Search filter" element is `.search-filter input` (`Grid.php:180-182`).
- Other decorators key off `.filter-criteria`, `.filter-update`, `data-toggle="dropdown"`, `.AknDropdown`, `input[name="start|end|value"]`, `.select2-choices`, `.ui-multiselect-menu.select-filter-widget`, `.AknFilterBox-addFilterButton`, `div.filter-list`, `data-drop-zone="column-context-switcher"`.

**Hard constraint surfaced by the code:** `SearchDecorator` drives the input with a jQuery `.trigger('change')`. A React-controlled `<input>` does **not** fire React `onChange` from a synthetic jQuery `change` (the documented jest-fireevent / React-jQuery event gap in project memory). Therefore the Backbone shell must keep its `keydown/change` jQuery delegation reading the DOM input and routing to `setValue` — React cannot be the sole owner of the search input's value path without rewriting `SearchDecorator` in lockstep.

## 6. React / DSM reuse verdict

**Strong reuse available — building from a proven base, not greenfield.**
- Bridge: `BaseView.renderReact()`/`unmountReact()` (ThemeProvider+DependenciesProvider, legacy `ReactDOM.render` for Selenium-safe delegation — must NOT become `createRoot`), and `ReactCellBase` (`react-cell-base.tsx`) is the exact Backbone-shell-hosts-React template. `DisplaySelector` proves "jQuery owns clicks, React owns open/close state". `PaginationBar` proves pure-render. All verified present.
- DSM inputs cover every value field: `TextInput`, `NumberInput`, `DateInput`, `SelectInput` (server-search, proven in `ViewSelectorCombobox.tsx`), `MultiSelectInput`, `BooleanInput`, `TagInput`. `Dropdown` + `useBooleanState` replace the `.filter-criteria` popup + `popupCriteriaShowed` flag. In-repo filter precedents exist (EventLog*, DQI TimePeriod/Family).
- **Must be built new:** a `ReactFilterBase` (analogous to `ReactCellBase`) that bridges `onValueChange -> this.setValue -> trigger('update')`; an operator-dropdown component (none exists); a `useFilterPopupPosition` hook reproducing the `position:fixed` + `.column-inner` scroll workaround (or floating-ui as in view-selector Slice B).
- **Correction to the maps:** `FilterTypeRegistry.ts` does **not** yet have React co-registrations — its header comment says "Wave 4 *will* extend this registry" (future tense). The co-registration slot is a design intent, not landed code.

## 7. What is already React vs Backbone

- **React/TS (done):** DSM inputs, `ViewSelectorCombobox`, `ReactCellBase`, `DisplaySelector`, `PaginationBar`, `renderReact` bridge. `createGridStateFilterWriter.ts` / `GridState.ts` / `FilterTypeRegistry.ts` are plain TS (no Backbone) — Wave-ready scaffolding.
- **Pure Backbone/jQuery (all of the filter layer):** `abstract-filter.js`, all 24 concrete filters, `filters-manager.js`, `collection-filters-manager.js`, `filters-selector.ts`, `filters-column.ts`, `filters-list.js`, `filters-button.js`, `datafilter-builder.js`. `filters-selector.ts` / `filters-column.ts` are TypeScript but extend Backbone `BaseView` and use `_.template` + raw DOM.

## 8. Honest assessment — what blocks clean slicing

1. **No single seam.** Three bootstrap stacks + three independent state serializers mean any filter touched must keep working in all paths it appears in.
2. **Page-shell DOM reach (abstract-filter prototype-level).** The `.column-inner` global scroll binding (`:145`) and `position:fixed` body-relative positioning (`:444-449`) live on the **prototype**, so they affect every popup filter at once.
3. **`product_scope-filter` bypasses the Wave-4 writer**, writing `DatagridState`/`UserContext` directly — gate it behind Wave 5.
4. **`product_category-filter`** is triple-cross-wired (TreeView + two mediator buses + reverse `listenTo`) — last among leaves.
5. **The React/jQuery `.trigger('change')` gap** means the Backbone shell must retain DOM event delegation for filters whose Behat decorator drives the input programmatically.
6. **Decorators are indivisible with Select2/multiselect filters** — migrating those requires rewriting their decorators in the same PR.

The clean-sliceable surface is therefore the **leaf inner-render only, behind a preserved Backbone shell**: keep `.filter-item[data-name][data-type]` + the `enable/disable/getValue/setValue/reset` API + the `update`/`disable` events; let React fill the inner DOM. This is exactly the cell/pagination/view-selector precedent and the only low-risk path until Wave 5.

## Strangler slices (least → most risky)

### Slice A — Search-style leaf inner-render (search-filter only) — **LOW**
- **Scope:** Migrate ONLY datafilter/filter/search-filter.js inner render to a React SearchFilterInput.tsx via BaseView.renderReact() into this.el. Backbone shell keeps className (incl. search-filter), data-name/data-type, emptyValue, isSearch, enable/disable/getValue/setValue, AND its jQuery keydown/change/focusin/focusout delegation (so Behat's $(...).trigger('change') still routes to setValue). React renders input[name="value"] + label + readonly handling; it is NOT the sole change-owner. STAYS Backbone: abstract-filter, all managers, FiltersSelector, FiltersColumn, all 23 other filters, label_or_identifier (defer). New: SearchFilterInput.tsx + Jest spec.
- **Rationale:** Simplest leaf: no popup, no operator, no Select2, no moveFilter, no .column-inner positioning. SearchDecorator only needs .search-filter input[name="value"] (Grid.php:180, SearchDecorator.php:78) — zero decorator change if input keeps name/class. Establishes four reusable assets: React-in-Backbone-shell filter pattern, onValueChange->setValue bridge, renderReact/unmount lifecycle for filters, a Jest-testable pure component. Fully reversible (restore the Underscore template). Mirrors the proven cell/pagination precedent.

### Slice B — Search batch + ReactFilterBase extraction — **LOW**
- **Scope:** Add label_or_identifier-filter.js (same archetype) and extract a reusable ReactFilterBase (analogous to ReactCellBase) that all React filters extend: renderReact on enable/reset, unmount on remove/disable, onValueChange->setValue->trigger('update') bridge. Keep moveFilter()/.search-zone graft as a Backbone concern (label_or_identifier only). STAYS Backbone: managers, FiltersSelector, FiltersColumn, all popup/select filters, scope, category.
- **Rationale:** Proves the pattern generalizes across two filters and produces the ReactFilterBase shell every later slice depends on. label_or_identifier->SearchDecorator (Grid.php:143) so still no decorator rewrite. Low risk: adds a sibling of an already-proven slice; the abstraction is local.

### Slice C — Archetype A text/choice popup batch — **MEDIUM**
- **Scope:** Migrate text-filter, choice-filter, number-filter, identifier-filter, parent-filter, uuid-filter inner render to a shared React FilterChip.tsx + FilterPopup.tsx (operator dropdown + text input as props). Replace the prototype-level .column-inner scroll + position:fixed logic (abstract-filter.js:144-145,444-449) with a shared useFilterPopupPosition hook (floating-ui, as in view-selector Slice B) applied to all six at once. Replicate document.addEventListener('mousedown') outside-click (text-filter.js:92) in a useEffect with cleanup. STAYS Backbone: managers, FiltersSelector, FiltersColumn, select/multiselect/select2/date/metric/price, scope, category.
- **Rationale:** Preserves .filter-criteria-selector/.filter-criteria-hint/.open-filter/.filter-update/.filter-criteria so BaseDecorator/StringDecorator/ChoiceDecorator(non-select2) keep working. Risk: the positioning hack lives on the abstract-filter prototype, so it must be solved for the whole popup family in one go; the mousedown handler must not leak; .filter-update submit semantics must be preserved (controlled input must still gate on the Apply button, not fire per-keystroke fetches).

### Slice D — Select/Select2/date/metric/price (decorator-coupled) — **HIGH**
- **Scope:** Migrate select-filter, multiselect-filter, ajax-choice-filter, product_completeness-filter, select2-choice-filter, select2-rest-choice-filter, date-filter, datetime-filter, metric-filter, price-filter to DSM SelectInput/MultiSelectInput/DateInput/NumberInput. REWRITE in lockstep: ChoiceDecorator, Select2ChoiceDecorator, DateDecorator, MetricDecorator, PriceDecorator. Handle async (metric FetcherRegistry, select2 $.ajax->async, ajax-choice lazy fetch). STAYS Backbone: filters-manager add-filter widget, both managers, FiltersSelector, FiltersColumn, scope, category.
- **Rationale:** Indivisible with Behat decorator rewrites — Select2Decorator targets .select2-choices/.ui-multiselect-menu DOM that DSM does not produce (the view-selector Slice C 'hardest slice' constraint). MultiselectDecorator teardown on hash_navigation_request:start must be preserved. Cannot validate locally; Behat-iterated in CI.

### Slice E — Managers + special filters + container teardown (gated on Wave 5) — **HIGH**
- **Scope:** Migrate filters-manager add-filter widget (#add-filter-select->React AddFilterMenu, .AknFilterBox-addFilterButton preserved), reconcile dual datafilter-builder/filters-button paths into one React host (still firing datagrid_filters:build.post), then product_scope-filter (route DatagridState/UserContext writes through RTK), product_category-filter (TreeView + mediator cross-wire), filters-column.ts side-panel (body-append -> React Portal), and remove abstract-filter.js.
- **Rationale:** GATE: requires Wave 5 RTK core/state live — product_scope bypasses GridStateFilterWriter (product_scope-filter.js:71-135) and category cross-wires via datagrid_filters:build.post (product_category-filter.js:58-62). Touches the page-shell drop-zone, the add-filter Behat entry point, and the TreeView subsystem. Highest blast radius; last.

## Recommended first slice
Slice A: build SearchFilterInput.tsx as a controlled React component and gut only the render body of datafilter/filter/search-filter.js, calling this.renderReact(SearchFilterInput, {label, value, onValueChange: v => this.setValue(v)}, this.el) while the Backbone shell keeps className (incl. search-filter), data-name/data-type, emptyValue, isSearch, the enable/disable/getValue/setValue API, AND its existing jQuery keydown/change/focusin/focusout delegation. It is the smallest reversible exemplar — no popup, no operator, no Select2, no moveFilter, no .column-inner positioning — and touches zero Behat selectors because SearchDecorator only needs .search-filter input[name="value"] (Grid.php:180, SearchDecorator.php:78), provided the shell retains the .trigger('change')->setValue jQuery bridge (a React-controlled input alone would not fire on a synthetic jQuery change event). It produces the four assets every later slice reuses: the React-in-Backbone-shell filter pattern, the onValueChange->setValue->trigger('update') bridge, the renderReact/unmount lifecycle for filter elements, and a Jest-testable pure component.

## Open questions
- Behat .trigger('change') vs React: confirm the Slice A shell-keeps-jQuery-delegation bridge is acceptable, or do you want SearchDecorator.php rewritten to dispatch a native input event React can observe (changes the 'inner-render-only' boundary and forces a decorator change in the first slice)?
- Per-grid path audit: which grids actually run Stack B (filters-list+filters-button) vs Stack C (datafilter-builder) vs Stack A (filters-column/selector)? A search-type filter appearing in two stacks must work in both — do we have a grid inventory, or should Slice A be scoped to product-grid-only first?
- filters-list.js still uses the legacy {{type}}-filter string template (NOT FilterTypeRegistry) and its inline alias map omits identifier. Should consolidating Stack B onto FilterTypeRegistry be a prerequisite cleanup PR before any React work, to remove the silent-404 drift?
- Wave 5 RTK timing: Slice E (scope + category + managers) is gated on the RTK core/state slice replacing collection.state. Is Wave 5 scheduled, or should the plan explicitly stop at Slice D and leave the special filters Backbone indefinitely?
- The .column-inner scroll + position:fixed popup positioning lives on the abstract-filter prototype, so it cannot be migrated per-filter. Acceptable to solve it for the whole popup family in Slice C (one floating-ui hook reused from view-selector Slice B), or do you want a dedicated prerequisite PR isolating the positioning mechanism first?
- Decorator-rewrite appetite: Slice D requires rewriting ChoiceDecorator/Select2ChoiceDecorator/DateDecorator/MetricDecorator/PriceDecorator in lockstep (DSM DOM != Select2/multiselect DOM). Is the team comfortable with Behat-iterated, CI-only validation for those, given they cannot be validated locally?
