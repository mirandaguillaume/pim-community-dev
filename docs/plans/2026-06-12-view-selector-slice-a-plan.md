# View-Selector Slice A (line + current → React) — Spec & Plan

> Part of the C1 product-grid view-selector wave. Grounded in
> `docs/plans/2026-06-12-view-selector-grounding.md` (§9 Slice A). Follows the proven Strangler
> pattern (Wave 1 display-selector, Wave 2 cells, Wave 3 pagination): a Backbone host keeps its
> lifecycle/logic and renders a presentational React component.
> **Local constraint:** Jest is NOT run locally (crashes the machine) → TDD = write test +
> implementation together, validate via CI.

**Goal:** Render the two leaf presentational pieces of the product-grid view-selector
(`view-selector-line`, `view-selector-current`) with React behind their Backbone shells, byte-for-
byte identical markup, Select2 untouched.

**Architecture:** Each Backbone shell keeps its `setView`/`configure`/dirty-computation logic; only
`render()` swaps `$el.html(template)` → `renderReact(Component, props, this.el); return this;`. The
shells are built by FormBuilder inside Select2's `formatResult`/`formatSelection`, so `render()` must
still return `this` (Select2 appends `form.render().$el`). Hosts stay `.js`; components are `.tsx`
relative imports → no `requirejs.yml` change.

## Files

### Create `…/Resources/public/js/grid/ViewSelectorLine.tsx`
Reproduces `templates/grid/view-selector-line.html`:
```tsx
import React from 'react';
import __ from 'oro/translator';

type View = {id: number; text: string; type?: string};
type Props = {view: View; isCurrent: boolean};

const ViewSelectorLine = ({view, isCurrent}: Props) => (
  <div className="select2-result-label-view">
    <div className="view-line">
      <span className={`view-label ${isCurrent ? 'view-label-current' : ''}`}>{view.text}</span>
      {view.type === 'public' && (
        <span className="view-type">{__('pim_datagrid.view_selector.public_label')}</span>
      )}
    </div>
  </div>
);

export default ViewSelectorLine;
```

### Create `…/Resources/public/js/grid/ViewSelectorCurrent.tsx`
Reproduces `templates/grid/view-selector-current.html` (keeps the empty `before`/`after` drop-zone
spans for byte-identical markup; they are unused in CE):
```tsx
import React from 'react';

type View = {text: string};
type Props = {view: View; dirtyFilters: boolean; dirtyColumns: boolean};

const ViewSelectorCurrent = ({view, dirtyFilters, dirtyColumns}: Props) => (
  <span className="select2-selection-label-view">
    <span className="before" data-drop-zone="before" />
    <span className="current">
      {dirtyColumns || dirtyFilters ? '*' : ''}
      {' '}
      {view.text}
    </span>
    <span className="after" data-drop-zone="after" />
  </span>
);

export default ViewSelectorCurrent;
```

### Modify `…/Resources/public/js/grid/view-selector-line.js`
- Import `ViewSelectorLine from './ViewSelectorLine'`; drop the now-unused `template` and `_` imports
  and the `template:` property.
- `render()` → `this.renderReact(ViewSelectorLine, {view: this.datagridView, isCurrent: this.currentViewId === this.datagridView.id}, this.el); return this;` (drop `renderExtensions()` — no CE extensions).
- Keep `setView` and the `datagridView/datagridViewType/currentViewId` fields unchanged.

### Modify `…/Resources/public/js/grid/view-selector-current.js`
- Import `ViewSelectorCurrent from './ViewSelectorCurrent'`; drop the now-unused `template` import and
  `template:` property. **Keep `_`** (used by `_.isEqual`).
- `render()` → `this.renderReact(ViewSelectorCurrent, {view: this.datagridView, dirtyFilters: this.dirtyFilters, dirtyColumns: this.dirtyColumns}, this.el); return this;` (drop `renderExtensions()`).
- Keep `configure()`, `onDatagridStateChange`, `areFiltersModified`, `setView` and the
  `datagridView/dirtyColumns/dirtyFilters` fields **unchanged** — the dirty computation stays Backbone.

### Tests — `…/UIBundle/tests/front/unit/grid/ViewSelectorLine.unit.tsx` + `ViewSelectorCurrent.unit.tsx`
Raw-DOM assertions (Stryker-safe, Wave 2/3 convention), 4-level import path
(`../../../../Resources/public/js/grid/…`). Register both in the Stryker `testMatch`
(`tests/front/unit/jest/stryker.jest.js`).

## Behat contract preserved
`.select2-result-label-view`, `.view-line`, `.view-label`, `.view-label-current`, `.view-type`
(line); `.select2-selection-label-view`, `.current` (current). All reproduced as `className`.

## Validation
No local run. CI: build-front, test-front-unit, mutation-testing-front, prettier:check, and Behat
(`datagrid_views.feature` — the runtime backstop: default view, apply, create/update, ownership,
delete, fallback).

## Out of scope
Slice B (CRUD buttons + create modal), Slice C (Select2 replacement), the inner selector,
secondary-actions, DatagridState, the forwarded-events bridge, the backend.
