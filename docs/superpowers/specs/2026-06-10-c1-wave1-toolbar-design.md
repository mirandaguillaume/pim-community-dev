# C1 Wave 1 — Toolbar (display-selector migration) — Design

Date: 2026-06-10
Branch: `c1/wave1-toolbar`
Audit input: `docs/plans/2026-06-10-product-grid-audit.md` (PR #261)

---

## Scope

First Strangler Fig wave on the product grid. Four deliverables, one PR:

1. **Playwright E2E spec** for the display selector (written FIRST, green against the current Backbone code, still green after migration)
2. **LocaleSwitcher async-mount race fix** (audit prerequisite)
3. **GridViewsView no-op mount removal** in `datagrid-builder.js` (audit: selector matches zero nodes)
4. **`display-selector.js` (108 lines, Backbone) → React** via the canonical `BaseView.renderReact()` bridge — the wave's actual Backbone kill

Out of scope: `column-selector.ts` (wave 1bis), pagination/grid-views dropdown (wave 3).

## Audit correction (found during this design)

The audit's bus-mismatch #1 said `display-selector.js:34` (`listenTo(getRoot(), 'grid_load:start')`) "receives nothing absent a bridge". **A bridge exists**: `product/index.yml:7-9` declares `forwarded-events: {grid_load:start, grid_load:complete}` on the root form `pim-product-index`. The listener is alive on the product index page. Consequence: `grid/mass-actions.js:43` ("confirmed dead listener") is likely alive too on this page. The wave-1 PR amends the audit report accordingly.

## Architecture

### DisplaySelector.tsx (new — pure React)

`src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datagrid/DisplaySelector.tsx`

```
Props: {
  types: {[name: string]: {label: string}},   // labels already translated by the host
  selectedType: string,
  onChange: (type: string) => void,
}
```

Renders the dropdown (DSM `Dropdown` + `useBooleanState`). No localStorage, no routing, no i18n — fully unit-testable. Keeps the CSS hooks Behat/Playwright rely on: root keeps a `display-selector` class and items keep `data-type="<name>"` attributes.

### Host: display-selector.tsx (replaces display-selector.js, same module path)

Same file path minus extension → the AMD/webpack alias `pim/datagrid/display-selector` keeps resolving (`.tsx` resolution proven by `ProductGalleryRow.tsx` in the same directory). **Zero YAML change.**

Responsibilities kept from the Backbone version:
- `listenTo(this.getRoot(), 'grid_load:start', …)` — same bus, bridge already in place (`product/index.yml:7-9`)
- Translate labels with `oro/translator`
- Read stored type: `localStorage.getItem('display-selector:<gridName>')`, fallback to first type — **key format unchanged** (read by `table.js` `applyDisplayType` at bootstrap)
- `onChange`: `localStorage.setItem(...)` + `Routing.reloadPage()`
- Render via `this.renderReact(DisplaySelector, props, this.el)`; unmount handled by `BaseView.remove()`

### LocaleSwitcher race fix

`locale-switcher.tsx:29-54`: `render()` mounts React inside `fetchLocales().then(...)`. If `remove()` runs before the promise resolves, React mounts into a detached node after `unmountReact()` already ran (leak — audit D5 gap).

Fix: track removal and guard the callback:
- add `private removed = false;`
- override `remove()`: set `this.removed = true`, then `super.remove()`
- in the `.then()`: `if (this.removed) return;`

### GridViewsView no-op removal

`datagrid-builder.js`: remove the `GridViewsView` import, the `gridGridViewsSelector` constant (`.page-title > .AknTitleContainer .span10:last` — matches nothing, audit-verified), and the instantiation/append block. The visible view dropdown is `pim/grid/view-selector` (UIBundle), untouched.

## Tests

| Layer | What | Validation |
|---|---|---|
| Playwright | `tests/front/e2e/product/product-grid-display-selector.spec.ts`: grid loads in default display; switch to gallery via the selector; gallery rendering asserted; **reload → gallery persists** (localStorage); switch back to list | CI (`test-playwright`) — must be green BEFORE the migration commit and after |
| Jest | `PimDataGridBundle/tests/front/unit/DisplaySelector.unit.tsx`: renders items from `types`, highlights `selectedType`, click fires `onChange(type)`, `data-type` attributes present | CI only (never run Jest locally) |
| Behat | existing grid scenarios keep passing (display selector untouched selectors) | CI |

Playwright spec must **fail, not skip**, when fixtures are missing (user rule).

## Constraints

- Keep legacy `ReactDOM.render` path (`BaseView.renderReact`) — Selenium constraint (`base.ts:263-272`)
- localStorage key contract byte-identical: `display-selector:<gridName>`
- No YAML/registration change; module path stable
- Jest mock of default exports needs `__esModule: true` (see memory)

## Delivery

Branch `c1/wave1-toolbar`, commits in order: Playwright spec → race fix → no-op removal → React component + Jest → host swap + delete `.js` → audit report amendment. One PR, auto-merge.
