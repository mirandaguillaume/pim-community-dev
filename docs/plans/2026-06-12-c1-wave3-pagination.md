# C1 Wave 3 — Pagination → React (Strangler) — Design

> Part of the C1 Product Grid Backbone→React migration. Grounded in the Wave 0 audit
> (`docs/plans/2026-06-10-product-grid-audit.md`) and the Wave 1/Wave 2 precedents.

## Context & scope finding

The audit listed "pagination" under Wave 3 (views) as a read-mostly surface. Grounding the
references corrected the scope: **`oro/datagrid/pagination-input` is not product-grid-specific —
it is the shared pagination of every datagrid in the PIM (~20 grids)**: product, family-variant,
product-group, users (role/group), history, import/export profiles, currency, group, channel,
attribute, group-type, family, association-type, plus dynamic loads from `item-picker.js:150` and
`associations.js:542`.

The base `oro/datagrid/pagination` (`pagination.js`) has **no consumer except as the superclass
of `pagination-input`** (verified: only the requirejs alias at `requirejs.yml:89` and the `import`
in `pagination-input.js`). Its inline `AknPagination-*` template is therefore never rendered —
`pagination-input` overrides `template` with `pim/template/datagrid/pagination.html` (the
`AknActionButton` markup).

**Decision:** migrate only the rendering of `pagination-input`; leave the base untouched. Wide
blast radius, but the markup is a Behat selector contract exercised across all grids, so CI is the
safety net.

## Inheritance & the bridge

`pagination-input` → `Pagination` (`pagination.js`) → `pim/form`. And `pim/form` is an alias of
`pimui/js/view/base` (`requirejs.yml:1265`). So the whole chain inherits `BaseView`'s
`renderReact()` / `unmountReact()` (`base.ts:273/292`), and `BaseView.remove()` already calls
`unmountReact()` (`base.ts:304`) — **no manual unmount needed** (unlike the Wave 2 cells, which
extended Backgrid `StringCell` and had to call `unmountComponentAtNode` themselves).

## Architecture (Strangler, Wave 1 pattern)

The Backbone host keeps every legacy responsibility; only the **render path** changes.

```
mediator events ──▶ pagination-input.js (host, .js, unchanged logic)
  grid_load:start/complete            │  setupPagination → bind collection
  datagrid_collection_set_after       │  getPages / makeHandles (pure, from collection.state)
                                       │  onChangePage  (jQuery-delegated, navigation writes)
                                       ▼
                         renderReact(PaginationBar, {handles, disabled}, this.el)
                                       ▼
                         PaginationBar.tsx (presentational, byte-identical markup)
                                       ▼
                  <a class="AknActionButton …"> ──click bubbles──▶ Backbone `events:{'click a'}`
```

**Clicks stay on jQuery delegation.** `events: {'click a': 'onChangePage'}` is inherited from the
base and lives on `this.$el`; the React-rendered `<a>` elements are inside `this.el`, so the
delegated listener catches the bubbled click exactly as today. `onChangePage` (read label → map to
`getPage`/`getNextPage`/`getPreviousPage`, with the `gap` guard) is unchanged. This is the explicit
Wave 1 decision (presentational React + jQuery delegation as the sole click handler) — it avoids
re-implementing navigation in React and avoids racing the collection reset against React's flush.

## Files

### Create: `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datagrid/PaginationBar.tsx`

Presentational, props-driven. Reproduces `pim/template/datagrid/pagination.html` exactly:

```tsx
import React from 'react';

type Handle = {
  label: string | number;
  title?: string;
  className?: string;
  wrapClass?: string;
};

type Props = {
  handles: Handle[];
  disabled: boolean;
};

const PaginationBar = ({handles, disabled}: Props) => (
  <>
    {handles.map((handle, index) => {
      const classes = ['AknActionButton', 'AknGridToolbar-actionButton'];
      if (handle.className) classes.push(handle.className);
      if (disabled) classes.push('disabled');

      return (
        <a key={index} className={classes.join(' ')} href="#" title={handle.title || undefined}>
          <span className={handle.wrapClass || undefined}>{handle.label}</span>
        </a>
      );
    })}
  </>
);

export default PaginationBar;
```

Fidelity notes vs the underscore template:
- The template adds the extra classes only when `handle.className || disabled`; class lists are
  whitespace-insensitive and Behat matches by class presence, so the unconditional `classes.join`
  is equivalent for selector purposes.
- `<%- handle.title %>` only emits the attribute when truthy → `title={handle.title || undefined}`
  (React omits the attribute on `undefined`).
- `wrapClass` only exists on base fast-forward handles (prev/next icons); `pagination-input`'s
  `fastForwardHandleConfig` is `{gap}` (no `wrapClass`), so in practice handles never carry one —
  supported anyway for faithfulness and zero behavioural change.

### Modify: `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datagrid/pagination-input.js`

Add the import and replace only `renderPagination`. Everything else (mediator wiring,
`setupPagination`, `getPages`, `makeHandles`, `onChangePage`, `maxRescoreWindow`, the rescore
warning, the `appendToGrid` prepend) is unchanged.

```js
import PaginationBar from './PaginationBar';
// … existing imports …

  renderPagination: function () {
    if (this.getPages().length <= 1) {
      this.unmountReact();
      this.$el.empty();
      return this;
    }

    const state = this.collection.state;

    this.renderReact(
      PaginationBar,
      {
        handles: this.makeHandles(),
        disabled: !this.enabled || !state.totalRecords,
      },
      this.el
    );

    const currentPage = state.firstPage === 0 ? state.currentPage : state.currentPage - 1;
    if (currentPage + 1 === Math.floor(this.maxRescoreWindow / state.pageSize)) {
      Messenger.notify('warning', __('oro.datagrid.pagination.limit_warning', {limit: this.maxRescoreWindow}));
    }

    if (this.options.appendToGrid) {
      this.gridElement.prepend(this.$el);
    }

    return this;
  },
```

Rationale for the `getPages().length <= 1` branch: `unmountReact()` tears down any prior React tree
before `$el.empty()` so we never `empty()` out from under a live React root (the legacy code did a
bare `$el.empty()` because the template path left plain DOM; the React path needs the explicit
unmount first).

The template-string import (`import template from 'pim/template/datagrid/pagination'`) and the
`template: _.template(template)` property become unused on the render path. **Keep them** this PR
(removing the `pim/template/datagrid/pagination` alias is a separate dead-template cleanup that
should follow only once Behat confirms no path renders it — out of scope here).

### Unchanged: `pagination.js` (base)

100% untouched. Only the superclass of `pagination-input`; no other consumer.

### No `requirejs.yml` change

The host stays `.js` (alias `oro/datagrid/pagination-input` resolves it as today). `PaginationBar`
is a **relative import**, not an alias — the build bundles it. Zero requirejs edits.

## Testing

### Create: `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/PaginationBar.unit.tsx`

`@testing-library/react` (the Wave 2 cell-component convention). Cases:
1. Renders one `<a>` per handle with `AknActionButton AknGridToolbar-actionButton`.
2. Applies `handle.className` (e.g. `active AknActionButton--highlight`).
3. Applies `disabled` to every handle when `disabled` is true; absent when false.
4. Sets `title` from `handle.title`; omits it when absent.
5. Applies `handle.wrapClass` to the inner `<span>`; omits it when absent.
6. `handles=[]` → renders nothing (no `<a>`).
7. Renders the `label` text (number and the `…` gap string).

### Stryker per-PR mutation gate

Add `'<rootDir>/src/Oro/Bundle/PimDataGridBundle/tests/front/unit/PaginationBar.unit.tsx'` to the
`testMatch` array in `tests/front/unit/jest/stryker.jest.js` (next to the Wave 2 cell entries,
~line 119) so the new component is in the per-PR mutation scope.

## Validation

No local run (Jest crashes the machine; the runner fleet is occasionally flaky). Rely on careful
review + CI: `build-front`, `test-front-unit`, `mutation-testing-front`, `lint-front`, and
**Behat** (the runtime backstop that exercises pagination markup/navigation across all grids).

## Risk & mitigations

| Risk | Mitigation |
|---|---|
| Wide blast radius (every grid) | Markup byte-identical; mediator/navigation/DOM untouched; Behat covers many grids |
| Click race (collection reset vs React flush) | Kept jQuery delegation as the sole click handler (Wave 1 decision) — no React-driven navigation |
| React-root leak on grid teardown | `BaseView.remove()` already calls `unmountReact()`; `getPages()<=1` branch unmounts before `empty()` |
| Selector drift breaking Behat | `className`/`title`/`label`/`wrapClass` reproduced verbatim from the underscore template |

## Out of scope

- Base `pagination.js` migration (no consumer; leave as-is).
- Removing the now-unused `pim/template/datagrid/pagination` alias/template (separate cleanup,
  needs Behat confirmation first).
- React-driven navigation / dropping jQuery delegation.
- The real product-grid saved-view selector (`pim/grid/view-selector`) and Wave 5 core/state —
  separate waves.
