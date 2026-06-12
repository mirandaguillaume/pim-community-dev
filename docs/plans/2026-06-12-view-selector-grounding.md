# Grounding Report — Product-Grid View-Selector (Backbone → React Strangler)

> Produced by an 8-reader + synthesis Workflow (2026-06-12) to ground the C1 view-selector wave
> brainstorm. Subsystem: `pim/grid/view-selector` (8 JS/TS modules + 7 templates + backend).

## 1. Subsystem overview

The product-grid **view-selector** lets a user pick, create, save, and remove a *datagrid view* (a
saved set of filters + columns) for the `product-grid`. It is the dropdown labelled by the current
view name, with a secondary-actions ("…") menu carrying Create / Save / Remove.

It is a **two-layer Backbone form**, mounted into the product index page via `form_extensions`:

```
pim-product-index (pim/common/simple-view)
  └─ pim-product-index-left-column
       └─ pim-product-index-column-inner (targetZone: navigation)
            └─ pim-product-index-view-selector  → module pim/grid/view-selector
                                                  config: { gridName: product-grid }
```

- **Outer shell** — `pim/grid/view-selector` (`js/product/grid/view-selector.js`, 33 lines).
  `className: 'view-selector'`, default `config.gridName='product-grid'`. `render()` only:
  `FormBuilder.getFormMeta('pim-grid-view-selector').then(buildForm).then(form =>
  form.configure(this.config.gridName).then(() => form.setElement('.view-selector').render()))`.
  **No state.** The natural Strangler seam.
- **Inner form** — `pim/grid/view-selector/selector` (`js/grid/view-selector.js`, 505 lines). The
  real component: Select2 widget, `currentView`/`initialView`/`defaultColumns`/`gridAlias`, the
  forwarded-events bridge, datagrid-view fetcher calls, DatagridState read/write, analytics, and
  the soft `reloadPage()`.

Inner form children (`config/form_extensions/grid/grid_view_selector.yml`):

| form code | module | parent / zone | role |
|---|---|---|---|
| `pim-grid-view-selector` | `…/selector` | root; `config:{viewTypes:['view'],fetchers:{view:'datagrid-view'}}` | the inner form |
| `…-secondary-actions` | `…/view-selector-secondary-actions` | parent=root, zone=`buttons` | the "…" dropdown |
| `…-line` | `…/line` | (built inline) | one dropdown row |
| `…-current` | `…/current` | (built inline) | selected-value label |
| `…-create-view` | `…/create-view` | parent=secondary-actions, zone=`secondary-actions`, pos 110 | Create |
| `…-save-view` | `…/save-view` | parent=secondary-actions, zone=`secondary-actions`, pos 120 | Save |
| `…-remove-view` | `…/remove-view` | parent=secondary-actions, zone=`secondary-actions`, pos 130 | Remove |

`line` and `current` are **not** in the parent/zone tree — built on demand by FormBuilder *inside*
Select2's `formatResult`/`formatSelection` callbacks. Backend is stable and AJAX-only (single
`DatagridViewController`, 8 REST routes under `/datagrid_view`); the Strangler needs **no backend
change**.

## 2. Module-by-module (reads vs writes)

**Orchestration & state**
- `js/product/grid/view-selector.js` (`pim/grid/view-selector`) — outer shell. Reads `config.gridName`. Writes none. Mounts inner form at `.view-selector`.
- `js/grid/view-selector.js` (`…/selector`) — inner form. Reads DatagridState, datagrid-view fetcher, `__moduleConfig['forwarded-events']`. Writes DatagridState, mediator `grid:view:selected`, local root events (`initialized`/`view-selected`/`state-changed`), history via `reloadPage()`. Holds mutable `currentView`/`initialView` read by children via `getRoot()`.

**Presentational pieces (read-only)**
- `js/grid/view-selector-line.js` (`…/line`) — one dropdown row. `setView(item,viewType,currentViewId)` imperative. No mediator/writes. No CE extensions.
- `js/grid/view-selector-current.js` (`…/current`) — selected-value label with `*` dirty marker. Reads `getRoot().initialView`/`defaultColumns`, root event `state-changed`. No writes.
- `js/grid/view-selector-secondary-actions.ts` — "…" container, extends `pim/form/common/secondary-actions`. Sets `.AknDropdown-menu` `position:fixed` on click (Oro datafilter position workaround). Parent of the 3 CRUD children.

**CRUD write surface**
- `…/create-view` — `<a class="AknDropdown-menuLink create">` when `currentViewType==='view'`. Opens a **`Backbone.BootstrapModal`** (raw underscore, not FormBuilder): `input[name="new-view-label"]`, `.AknCreateView-typeSelector` (`isPrivateView` default **true**), OK held `AknButton--disabled` until label non-empty. Reads DatagridState filters/columns. `POST /rest/{alias}` via DatagridViewSaver → fires `view-created`. Failure iterates `responseJSON` array into messenger.
- `…/save-view` — `<a class="… save">` when `view` AND `UserContext.meta.id === currentView.owner_id`. Listens `state-changed` → computes `this.dirty` (filters string-eq, columns `_.isEqual`), re-renders (`dirty` drives `AknDropdown-menuLink--hidden`). Writes merged clone, `POST /rest/{alias}`, fires `view-saved`, `analytics.appcuesTrack`.
- `…/remove-view` — `<a class="… remove">` when `view` AND `id!==0` AND owner. `stopPropagation()` → `Dialog.confirm` (pim/dialog) → DatagridViewRemover `DELETE /rest/{identifier}` → fires `view-removed`. **Asymmetry:** failure passes raw `responseJSON` (not iterated) → `[object Object]` risk.

**Savers/fetchers (thin, no Backbone coupling):** `pim/datagrid-view-fetcher` (adds `defaultColumns`/`defaultUserView`), `pim/saver/datagrid-view` (`$.post` save), `pim/remover/datagrid-view` (`$.ajax` DELETE), `pim/datagrid/state` (DatagridState sessionStorage facade — **shared, out of scope**).

## 3. The forwarded-events bridge (requirejs.yml:19-21 ↔ view-selector.js:57)

The Oro grid fires `grid:product-grid:state_changed` on the **global `oro/mediator`** (via
DatagridState writes); the inner form subscribes on the **local form root**. Different buses. The
bridge reconciles them: requirejs.yml:19-21 declares `forwarded-events`, `requirejs-utils.js`
deep-merges it into `__moduleConfig`, `config-loader.js` prepends `var __moduleConfig=…` to `.js`
files referencing it, and `configure()` calls `BaseView.forwardMediatorEvents()` to relay the
mediator event onto `getRoot()`. **Removing the bridge or the `…/selector` alias silently kills
dirty/highlight — no console error.** A React replacement should subscribe directly to
`oro/mediator grid:{alias}:state_changed` in a `useEffect` (also drops the hardcoded `product-grid`
alias). Config block stays until the Oro grid producer is migrated. `__moduleConfig` injection only
matches `.js`, not `.ts`.

## 4. Select2 coupling points

Select2 **v3** on a hidden `input.select-field` via `initSelect2.init`:
`dropdownCssClass:'grid-view-selector'`, `closeOnSelect:false`, debounced (400ms) paged `query` via
the datagrid-view fetcher (`ensureDefaultView` prepends synthetic "Default view" id 0),
`initSelection`, **`formatResult`** (`FormBuilder.build('pim-grid-view-selector-line')` →
`$container.append(form.render().$el)`), **`formatSelection`** (builds `…-current`), events
`select2-selecting`→`selectView`, `select2-close`, `closeSelect2()`. Behat hard-codes
`.select2-container`, `.select2-arrow`, `.select2-drop.grid-view-selector`,
`.select2-selection-label-view`, `.select2-result-label-view`. **Replacing Select2 ⇒ rewrite
`GridCapableDecorator` + `Select2Decorator` in the same PR.**

## 5. View-CRUD write surface + backend routes

Create/Save build `{filters,columns,label,type}` from DatagridState (create) or a clone of
`currentView`+live state (save) → `POST /rest/{alias}` (`pim_datagrid_view_rest_save`, dual
create/update, discriminated by `view[id]`, strips `type` on update). Remove → `DELETE
/rest/{identifier}`. Children fire root events; inner form reacts (fetch+`selectView`), clears
fetcher cache, writes DatagridState, fires `grid:view:selected`, `reloadPage()`.

| route | method/path |
|---|---|
| `pim_datagrid_view_rest_types` | GET `/rest/types` |
| `pim_datagrid_view_rest_index` | GET `/rest/{alias}/views` |
| `pim_datagrid_view_rest_default_columns` | GET `/rest/{alias}/default-columns` |
| `pim_datagrid_view_list_available_columns` | GET `/rest/{alias}/available-columns` (no JS consumer) |
| `pim_datagrid_view_rest_default_user_view` | GET `/rest/{alias}/default` |
| `pim_datagrid_view_rest_save` | POST `/rest/{alias}` (create+update) |
| `pim_datagrid_view_rest_remove` | DELETE `/rest/{identifier}` |
| `pim_datagrid_view_rest_get` | GET `/rest/{identifier}` |

`saveAction`/`removeAction` require `X-Requested-With: XMLHttpRequest` (CSRF backstop) — a React
`fetch` must send it. Success flashes come from the server session bag, rendered on next page load
(why `reloadPage()` matters).

## 6. Load-bearing contracts (DOM / mediator / FormBuilder / Behat)

**Root template** `templates/grid/view-selector.html`: `.grid-view-selector` root; optional
`.view-selector-type-switcher[data-toggle="dropdown"]` with `.current-view-type` /
`.view-type-item[data-value]`; `input[type=hidden].select-field`; `data-drop-zone="buttons"` /
`"resume"`.

**Behat-load-bearing CSS** (`GridCapableDecorator`): `.grid-view-selector .select2-container`,
`.view-selector-type-switcher`, `.create-button .create`, `.save-button .save`,
`.remove-button .remove`, `.secondary-actions`, `.select2-drop.grid-view-selector`,
`.select2-arrow`, `.select2-selection-label-view`; modal `input[name="new-view-label"]`,
`.AknCreateView-typeSelector`, `.modal .ok`/`AknButton--disabled`; shared `.dropdown-button`;
presentational `.view-label`, `.view-label-current`, `.view-type`, `.view-line`, `.current`;
grid-load gate `.hash-loading-mask .loading-mask`.

**Mediator/root events:** global `grid:view:selected` (consumed by the already-React
`ProductGridViewTitleContext`); local root events `initialized`/`view-selected`/`state-changed`/
`view-created`/`view-saved`/`view-removed`/`close-selector`; bridged `grid:product-grid:state_changed`.

**FormBuilder keys** `pim-grid-view-selector`(+`-line`/`-current`/`-secondary-actions`) must stay
resolvable. `pimcommunity/grid/view-selector/selector` alias override must stay in sync (CE/EE).

**Behat backstop:** `tests/legacy/features/pim/enrichment/product/datagrid/datagrid_views.feature`
(8 scenarios: default view, apply, create+update filters/columns, ownership gate, delete own
default, custom default, fallback after deletion; asserts flash text), and
`…/csrf_attacks/allow_only_xhr_requests_for_datagrid_views.feature`. Glue: `ViewSelectorContext`,
`DataGridContext`, `GridCapableDecorator`, `Select2Decorator`.

## 7. Consolidated risks (18)

1. **Select2 v3 lock-in / Behat coupling** — `formatResult`/`formatSelection` append child forms into Select2-owned containers; ~6 `.select2-*` selectors hard-coded across decorators. Replacing Select2 is one indivisible change + decorator rewrites.
2. **Forwarded-events bridge is invisible glue** — losing requirejs.yml:19-21 / the `…/selector` alias silently breaks dirty/highlight. Hardcoded `product-grid`.
3. **`getRoot().currentView`/`initialView` direct reads** — children read mutable plain objects off the root with no setter protocol.
4. **`reloadPage()` soft-navigate** — mutates `Backbone.history.fragment` then `navigate(url,true)`; no `load` event → Playwright/Behat poll the loading-mask. Preserve byte-for-behavior.
5. **`DatagridState.set()` must precede `reloadPage()`** — grid restores from sessionStorage on reload; reversing loads stale state.
6. **`mediator.trigger('grid:view:selected')`** — `ProductGridViewTitle` depends on it; keep firing until consumers migrate.
7. **`secondary-actions.ts` position:fixed via jQuery `offset()`** — breaks inside a CSS-transform ancestor; a React port needs floating-ui/`getBoundingClientRect`.
8. **`.dropdown-button` is shared** — `DropdownMenuDecorator.open()` targets it generically; renaming breaks unrelated Behat.
9. **Save-button hidden vs removed** — `AknDropdown-menuLink--hidden` hides without removal; `isViewCanBeSaved()` checks presence. React `return null` flips assertion semantics.
10. **Dual-mode `saveAction`** — one route create+update; `type` stripped on update.
11. **`columns` duality** — array in GET (normalizer), comma-string in DB/POST.
12. **XHR-only guard / CSRF feature** — React `fetch` must send `X-Requested-With`.
13. **remove-view error asymmetry** — raw `responseJSON` → `[object Object]`; fix deliberately.
14. **`initializeSelection()` returns a jQuery Deferred** — a rejection hangs `render()`.
15. **Double FormBuilder indirection** — outer `configure(gridName)` → inner `configure(gridAlias)`; drift makes `gridAlias` null silently.
16. **`.view-selector` must pre-exist** — `setElement('.view-selector')` no-ops into a detached node if column-inner hasn't painted; React mount needs a guaranteed container/portal.
17. **CE/EE alias sync** — `pimcommunity/grid/view-selector/selector` override.
18. **`_rest_index`/`_types` hardcoded in `UserManagement/default-grid-views.ts`** — out-of-subsystem coupling on route rename.

## 8. What stays Backbone regardless of slice (until late waves)

The `pim-product-index-view-selector` form_extensions mount; `DatagridState`; the
savers/remover/fetcher AMD modules (or thin `fetch` wrappers); the forwarded-events bridge **config**;
`reloadPage()` + `mediator.trigger('grid:view:selected')`.

## 9. Strangler slice options (least → most risky)

### Slice A — Leaf presentational pieces (`line` + `current`) — **LOW risk**
Migrate `view-selector-line.js` and `view-selector-current.js`. Keep each Backbone shell (so
`FormBuilder.build('…-line')` / `getFormMeta('…-current')` still resolve and Select2's
`formatResult`/`formatSelection` still append `form.render().$el`). Replace only the template+jQuery
body of `render()` with `renderReact(<ViewSelectorLine/>` / `<ViewSelectorCurrent/>, this.el)`. The
dirty comparison is extracted as a pure helper fed from the Backbone shell. **Stays Backbone:** inner
selector, Select2, secondary-actions, all CRUD, DatagridState, the bridge. *Proven ReactDOM-into-
jQuery-container pattern; Behat only reads text via className → zero decorator change.*

### Slice B — CRUD action buttons + create modal — **MEDIUM risk**
Replace the 3 Backbone span shells (`.create-button`/`.save-button`/`.remove-button`) with one React
`ViewCrudActions` in the secondary-actions zone via a Backbone bridge. Props `gridAlias, currentView,
initialView, currentViewType, ownerId`; callbacks `onCreated/onSaved/onRemoved` wired to the parent's
existing root-event emits. Re-implement the create modal as React/DSM preserving
`input[name="new-view-label"]`, `.AknCreateView-typeSelector`, `.modal .ok`+`AknButton--disabled`,
`isPrivateView=true`. Keep Saver/Remover (wrap Deferreds), fix the remove `[object Object]`
asymmetry. **Stays Backbone:** inner selector, Select2, line/current, secondary-actions container +
position:fixed, DatagridState, reloadPage, mediator, backend, the bridge. *Touches the write surface
+ create-modal Behat steps; does NOT touch Select2.*

### Slice C — Replace Select2 with a DSM/React async combobox — **HIGH risk**
Replace the Select2 widget in `view-selector.js` (initializeSelectWidget, formatResult/
formatSelection, query/initSelection, select2 events, closeSelect2) with a React async combobox
owning the dropdown UX. **Must update in the same PR:** `GridCapableDecorator` + `Select2Decorator`
(every `.select2-*` selector re-pointed); keep `.grid-view-selector` root + `data-toggle="dropdown"`.
**Stays Backbone:** outer mount, DatagridState, reloadPage, `grid:view:selected`, the bridge config.
*Indivisible with the decorator rewrite; lands only after A+B prove the bridge and after a Select2-
replacement decision.*

## 10. Open questions for the brainstorm

1. **Select2 strategy** — keep Select2 as host (Slice A) for now, or commit to replacing it (Slice C)? Decides the decorator rewrite.
2. **CRUD modals** — React/DSM dialog (Slice B) or keep Backbone.BootstrapModal? Must preserve the "fill in the popin" Behat selectors.
3. **Shared mutable state** — React children get `currentView`/`initialView` via the Backbone root (Option 1, fine for A/B) or a React context/store (Option 2, full migration)?
4. **Forwarded-events** — React subscribes directly to `oro/mediator` (drops hardcoded alias) or keeps `forwardMediatorEvents`?
5. **Scope per PR** — one slice per PR (A→B→C, recommended) or bundle A+B (both keep Select2)?
6. **Save-button visibility** — keep hidden-not-removed (`AknDropdown-menuLink--hidden`) or conditional-render + update the Behat decorator in lockstep?
7. **reloadPage / grid:view:selected** — keep untouched in all near-term slices (recommended), or decouple the grid loader (out of scope for A/B/C)?
8. **CE/EE alias parity** — confirm no live EE extensions inject into the line/current/secondary-actions drop-zones before dropping `renderExtensions()`; keep `pimcommunity/…/selector` in sync.
