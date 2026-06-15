# Slice C — Replace Select2 v3 in the product-grid view-selector — Grounding

> Produced by a 6-reader + synthesis Workflow (2026-06-15) to ground the highest-risk slice of the
> view-selector wave. Verified against the actual code.

## Verdict in one line

Slice C is **effectively atomic for Behat-safety** (one wiring+decorator PR), best replaced with the
**DSM `SelectInput`** component, and **de-riskable by landing the React combobox component + Jest
tests as a no-op prerequisite PR first**. Its biggest unknowns are **only resolvable on a real Behat
run** (which cannot be run locally).

## 1. Why it is atomic

`GridCapableDecorator::getViewSelector()` spins on `.grid-view-selector .select2-container`. The
instant Select2 is removed, that anchor is null and **every** view-selector scenario fails at step 1
before any assertion. So the component swap and the Behat decorator rewrite are inseparable — one PR.
The two natural earlier splits were already taken (Slice A leaves #272, Slice B CRUD #273). The
type-switcher-first idea is a non-split: in CE `viewTypes` is always `['view']`, so the switcher is
never shown (zero Behat coverage, zero user-visible change). **De-risk instead by sequencing:** a
prerequisite no-op PR with the combobox component + Jest tests, then the atomic wiring+decorator PR.

## 2. Select2 API surface to reproduce (view-selector.js, 505 lines)

- **`query`** (153-197): 400ms debounce; paged server search via
  `FetcherRegistry.getFetcher('datagrid-view').search(params)`; `limit:20`; `more` =
  `response.more ?? choices.length===20`; `ensureDefaultView` unshifts synthetic **id-0** "Default
  view" on page-1 + empty-term when `defaultUserView===null && currentViewType==='view'`. → React:
  `onSearchChange` (debounced) + `onNextPage` (`usePagination`); the **adapter** owns the page
  counter, reset-on-search, stale-response dedup, and **cross-page id dedup** (DSM throws on
  duplicate `Option.value`).
- **`initSelection`/`initializeSelection`** (121-127, 251-303): jQuery Deferred resolved BEFORE
  mount; deep-clones into `initialView`/`currentView`, overlays in-progress filters/columns from
  sessionStorage, `DatagridState.set(alias,{initialViewState})`, fires `grid:view-selector:initialized`
  (root) **and** `mediator.trigger('grid:view:selected', currentView)`. `.fail()` falls back to the
  user default so a missing view does not hang render. **Keep the resolve-before-render gate + both
  events.**
- **`formatResult`/`formatSelection`** (203-232): today build Backbone forms
  (`pim-grid-view-selector-line`/`-current`) into Select2 containers. → React: render
  `ViewSelectorLine`/`ViewSelectorCurrent` (already React, #272) directly as Option children /
  `currentValueElement`. The `pim-grid-view-selector-line` FormBuilder key (and likely `-current`)
  go **dead** → cleanup.
- **`selectView`** (436-452), side-effect ORDER load-bearing: (1) `DatagridState.set(alias,{view,filters,columns:join(',')})`
  **before** reloadPage; (2) `currentView=view`; (3) `trigger('grid:view-selector:view-selected')`;
  (4) `mediator.trigger('grid:view:selected')` (consumed by `product-grid-view-title-context.ts`);
  (5) `getFetcher('locale').clear()`; (6) `analytics.appcuesTrack`; (7) `reloadPage()`
  (`Backbone.history.fragment=Date.now(); navigate(hash,true)` — soft reload, no browser load event).
- **`closeOnSelect:false`** (117): selecting must NOT close (reloadPage tears the page down).
  `grid:view-selector:close-selector` (425-429) needs an **imperative close** (DSM has no external
  `open` prop → ref/`useImperativeHandle` or lifted `isOpen`).
- **`onGridStateChange`** (372-384) + `forwardMediatorEvents` (49-51): React must
  `mediator.on('grid:product-grid:state_changed', …)` in a `useEffect` with **stable cleanup** (wrong
  deps → multiplied subscriptions); fires `grid:view-selector:state-changed` (consumed by the
  still-Backbone current/save shells). `onViewCreated/Saved/Removed` (392-420) re-select after the
  Slice B CRUD events.

## 3. Behat decorator rewrite inventory (selector → new hook)

Strategy A (recommended): **keep the Select2 classnames**, only open/close mechanics change.

| Behat method (file:line) | Today | Slice C |
|---|---|---|
| `getViewSelector` (Grid:50-62) | `.grid-view-selector .select2-container` | keep `.grid-view-selector` root; **re-emit `.select2-container`** on the React root |
| `open()` (Select2:92-101) | `.select2-arrow`, guard `select2-dropdown-open` | **rewrite** → click React trigger, guard React open class |
| `close()`/`getWidget()` (Select2:106-138) | `#select2-drop-mask`, `.select2-drop` | **rewrite** → React dropdown container/backdrop or Escape |
| `openViewSelector` (Grid:69-82) | poll `.select2-drop.grid-view-selector`, click `.select2-arrow` | **rewrite** → poll React dropdown, click trigger |
| `getCurrentValue` (Select2:253-260) | `.select2-selection-label-view` | **no change** (owned by `ViewSelectorCurrent`) |
| `getAvailableValues` (Select2:193-223) | `.select2-result-label`, `.select2-no-results` | **GAP**: leaf emits `.select2-result-label-view` → add a `.select2-result-label` wrapper OR change decorator; render `.select2-no-results` empty state |
| `search($text)` (Select2:230-246) | `.select2-search input` `.val().trigger('input')` | keep a live `.select2-search` input reacting to native `input`; **verify DSM SearchInput honors jQuery injection** |
| `setValue($value)` (Select2:20-60) | `.select2-result-label:contains():mouseup()` | **riskiest**: DSM OptionContainer must respond to `.mouseup()` (not just click) else rewrite these steps |
| type-switcher methods | `.view-selector-type-switcher`, `.view-type-item` | **DEAD in CE** (switcher hidden, `viewTypes==['view']`) |

CRUD selectors (Slice B) are NOT touched: `.create/.save/.remove`, `.secondary-actions`,
`.AknCreateView-typeSelector`, `input[name="new-view-label"]`, `.modal .ok`.

## 4. DSM SelectInput vs custom — verdict: **SelectInput**

Natively covers the async contract: `disableInternalSearch:true` + `onSearchChange` (server-only
query) + `onNextPage` via `usePagination` IntersectionObserver (= Select2 `more`/scroll);
`Option value={id}>{ReactNode}` = custom rows (host `ViewSelectorLine`); `currentValueElement` =
selection display (host `ViewSelectorCurrent`); keyboard nav; `data-testid={value}` per option. A
custom Dropdown-primitive combobox would re-implement exactly what SelectInput ships.

**Three SelectInput adaptations:** (1) always inject the selected view as a child (even off the
current page) or `currentValueElement` falls back to the raw id; (2) no external `open` prop →
imperative close needs a ref / lifted state (**touches the shared DSM package**); (3) stringify ids
(`Option.value` is string; synthetic default `id:0` is int → stringify/reparse, special-case `'0'`).

**Adapter-owned (not in SelectInput):** the 400ms debounce, page counter + reset-on-search,
stale-response dedup, cross-page id dedup, and CSS for the narrow `AknColumn` toolbar.

## 5. Contracts to preserve

`mediator grid:view:selected` (both init + selectView; consumed by the already-React title);
`DatagridState.set` BEFORE `reloadPage`; `.grid-view-selector` root; `data-toggle="dropdown"` on any
migrated dropdown (generic dropdown step + soft-reload contract); exact `reloadPage()` behaviour
(`ViewSelectorContext` waits on `.hash-loading-mask .loading-mask`); `grid:view-selector:state-changed`
(dirty `*`); the `initialized/view-created/saved/removed/close-selector` root events; **CE/EE alias
sync** (`requirejs.yml` 996 + 1269 → same module); `FetcherRegistry` `.clear()` side-effects;
`X-Requested-With` (CSRF); **no CSS-transform ancestor** on `.grid-view-selector` (the
secondary-actions `position:fixed` workaround breaks otherwise).

## 6. Approach options

- **A — DSM SelectInput, keep Select2 classnames (recommended, one PR).** Smallest decorator churn
  (only open/close mechanics). Re-emit `.select2-container`; keep `.select2-selection-label-view`;
  add `.select2-result-label`/`.select2-search`/`.select2-no-results` inside. Risk: jQuery
  `.val().trigger('input')` + `.mouseup()` reaching DSM's controlled input, and CSS clipping of
  Select2 classnames inside SelectInput's styled containers — **provable only on a real Behat run**.
- **B — DSM SelectInput, clean React decorator (data-testid).** New decorator class; every method
  rewritten to `data-testid`/DSM backdrop. Cleaner long-term, larger rewrite, more places to miss a
  selector (each miss = silent spin-timeout).
- **C — Feature-flagged parallel.** False confidence (React path has zero Behat coverage until the
  flip), flag debt. Only for production rollback safety, not review size.

## 7. Recommended shape (de-risked)

1. **Prerequisite no-op PR**: `ViewSelectorCombobox.tsx` (DSM SelectInput adapter: debounce,
   pagination, dedup, id stringify, `ensureDefaultView`, imperative close) + Jest tests. NOT wired →
   zero Behat impact, fully unit/mutation-tested in isolation.
2. **Atomic PR**: rewrite `view-selector.js` (initializeSelectWidget/query/formatResult/formatSelection
   → the combobox; keep selectView/initializeSelection/onGridStateChange/reloadPage) + the 2 Behat
   PHP decorators (Select2Decorator open/close/getWidget + GridCapableDecorator openViewSelector) +
   dead-code cleanup (`pim-grid-view-selector-line` key, the line/current shells). Behat-iterated.

## 8. Open questions (several Behat-only-resolvable)

1. **Does DSM SelectInput respond to jQuery `$(…).val(x).trigger('input')` + `.mouseup()`?** (the
   decorator's injection model) — biggest unknown, **only resolvable on a real Behat run**.
2. Classname reuse vs CSS clipping inside DSM's styled containers (Approach A vs B).
3. The `.select2-result-label` vs `.select2-result-label-view` gap — add a wrapper or change decorator?
4. Imperative close — add `useImperativeHandle`/an `open` prop to DSM SelectInput (touches the shared
   package), lift state, or fork a minimal combobox?
5. Toolbar width — SelectInput overlay 150-400px vs the narrow `AknColumn`; `fullWidth`+CSS or custom?
6. Dead-code scope — confirm the FormBuilder keys + line/current shells are deletable (CE/EE alias
   parity; verify via Twig `require([…])` scan + Behat, not grep).
7. Mount sequencing — `initSelection` must resolve before the combobox renders; preserve the
   `.fail()` fallback so a missing view does not hang render.
8. Is the type-switcher truly dead in CE — can its markup be dropped, or kept for EE/forward-compat?
