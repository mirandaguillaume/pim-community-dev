# Pagination → React (Strangler) Implementation Plan

> **For agentic workers:** companion to the design at
> `docs/plans/2026-06-12-c1-wave3-pagination.md`. Steps use checkbox (`- [ ]`) syntax.
> **Local constraint:** Jest must NOT be run locally (crashes the machine). The TDD loop here is
> "write the test + the implementation, validate via CI" — there is no local `run-the-test` step.

**Goal:** Render the shared datagrid pagination (`oro/datagrid/pagination-input`, ~20 grids) with a
presentational React component instead of an underscore template, keeping all Backbone host logic.

**Architecture:** Strangler. Backbone host keeps mediator wiring, navigation, DOM injection, rescore
warning; only `renderPagination` changes to `renderReact(PaginationBar, …)`. Clicks stay on the
inherited jQuery `events:{'click a':'onChangePage'}` delegation. Base `pagination.js` untouched.

**Tech Stack:** React 17, `@testing-library/react`, Backbone/`pim/form` (`pimui/js/view/base`),
requirejs, Stryker (per-PR mutation gate).

---

### Task 1: Presentational `PaginationBar` component

**Files:**
- Create: `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datagrid/PaginationBar.tsx`
- Test: `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/PaginationBar.unit.tsx`

- [ ] **Step 1: Write the test** (`PaginationBar.unit.tsx`)

```tsx
import React from 'react';
import {render} from '@testing-library/react';
import PaginationBar from '../../Resources/public/js/datagrid/PaginationBar';

const handle = (over = {}) => ({label: 1, title: 'No. 1', ...over});

test('renders one anchor per handle with the base classes', () => {
  const {container} = render(<PaginationBar handles={[handle(), handle({label: 2, title: 'No. 2'})]} disabled={false} />);
  const links = container.querySelectorAll('a.AknActionButton.AknGridToolbar-actionButton');
  expect(links).toHaveLength(2);
  links.forEach(a => expect(a).toHaveAttribute('href', '#'));
});

test('applies the handle className (active highlight)', () => {
  const {container} = render(
    <PaginationBar handles={[handle({className: 'active AknActionButton--highlight'})]} disabled={false} />
  );
  const a = container.querySelector('a')!;
  expect(a).toHaveClass('active');
  expect(a).toHaveClass('AknActionButton--highlight');
});

test('applies disabled to every handle when disabled is true, absent when false', () => {
  const on = render(<PaginationBar handles={[handle(), handle()]} disabled={true} />);
  on.container.querySelectorAll('a').forEach(a => expect(a).toHaveClass('disabled'));
  const off = render(<PaginationBar handles={[handle()]} disabled={false} />);
  expect(off.container.querySelector('a')).not.toHaveClass('disabled');
});

test('sets title from the handle, omits it when absent', () => {
  const {container} = render(
    <PaginationBar handles={[handle({title: 'No. 7'}), handle({title: undefined})]} disabled={false} />
  );
  const [withTitle, without] = Array.from(container.querySelectorAll('a'));
  expect(withTitle).toHaveAttribute('title', 'No. 7');
  expect(without).not.toHaveAttribute('title');
});

test('applies wrapClass to the inner span, omits it when absent', () => {
  const {container} = render(
    <PaginationBar handles={[handle({wrapClass: 'icon-chevron-left'}), handle()]} disabled={false} />
  );
  const [withWrap, without] = Array.from(container.querySelectorAll('a span'));
  expect(withWrap).toHaveClass('icon-chevron-left');
  expect(without.className).toBe('');
});

test('renders the label text including the gap string', () => {
  const {container} = render(
    <PaginationBar handles={[handle({label: 3, title: 'No. 3'}), handle({label: '…', title: '…', className: 'AknActionButton--unclickable'})]} disabled={false} />
  );
  expect(container.textContent).toContain('3');
  expect(container.textContent).toContain('…');
});

test('renders nothing for an empty handle list', () => {
  const {container} = render(<PaginationBar handles={[]} disabled={false} />);
  expect(container.querySelectorAll('a')).toHaveLength(0);
});
```

- [ ] **Step 2: Implement** (`PaginationBar.tsx`) — exact content from the design doc:

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

- [ ] **Step 3: Add to the Stryker per-PR `testMatch`**

In `tests/front/unit/jest/stryker.jest.js`, add next to the Wave 2 cell entries (~line 119):

```js
    '<rootDir>/src/Oro/Bundle/PimDataGridBundle/tests/front/unit/PaginationBar.unit.tsx',
```

---

### Task 2: Wire the host to render React

**Files:**
- Modify: `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datagrid/pagination-input.js`

- [ ] **Step 1: Add the import** (top of file, after the existing imports):

```js
import PaginationBar from './PaginationBar';
```

- [ ] **Step 2: Replace `renderPagination`** with the React render path (keep mediator/getPages/
  makeHandles/onChangePage/rescore/prepend untouched):

```js
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

- [ ] **Step 3: Leave the rest of the file unchanged** — `template`/`import template` stay (now
  unused on the render path but still valid; removing the alias is a separate cleanup).

---

### Task 3: Commit

- [ ] **Step 1: Lint the changed front files** (project Prettier, per the formatter-hook lesson)

Run: `yarn prettier --write src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datagrid/PaginationBar.tsx src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datagrid/pagination-input.js src/Oro/Bundle/PimDataGridBundle/tests/front/unit/PaginationBar.unit.tsx`

- [ ] **Step 2: Commit**

```bash
git add src/Oro/Bundle/PimDataGridBundle docs/plans/2026-06-12-c1-wave3-pagination.md docs/plans/2026-06-12-c1-wave3-pagination-plan.md tests/front/unit/jest/stryker.jest.js
git commit -m "feat(datagrid): C1 wave 3 — pagination rendering to React (shared across grids)"
```

- [ ] **Step 3: Push, open PR, enable auto-merge**

```bash
git push -u origin c1/wave3-pagination
gh pr create --fill
gh pr merge --auto --squash
```

- [ ] **Step 4: Watch CI** — build-front, test-front-unit, mutation-testing-front, lint-front,
  and Behat (the runtime backstop across all grids). Rerun infra failures; ALERT on a real
  frontend-gate or Behat failure.
