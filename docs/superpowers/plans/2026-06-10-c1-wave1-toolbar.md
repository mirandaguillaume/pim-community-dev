# C1 Wave 1 — Toolbar Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** First Strangler Fig wave on the product grid — migrate `display-selector.js` to React, fix the LocaleSwitcher race, remove the GridViewsView no-op mount, with a Playwright spec guarding the behavior.

**Architecture:** Pure React `DisplaySelector` + thin BaseForm host at the same module path (`pim/datagrid/display-selector`) using the canonical `BaseView.renderReact()` bridge. localStorage contract (`display-selector:<gridName>`) byte-identical (read by `table.js`).

**Tech Stack:** React 18 (legacy `ReactDOM.render` via BaseView — Selenium constraint), DSM theme via `renderReact`, Jest (CI only), Playwright.

---

## CRITICAL CONSTRAINTS

- **NEVER run Jest locally** (crashes machine). Playwright requires a running PIM — validate via CI.
- localStorage key stays `display-selector:<gridName>`.
- Markup keeps `.AknTitleContainer-displaySelector` root, `.display-selector-item` + `data-type` hooks (defined CSS + the Playwright spec's contract).
- The dropdown currently opens via Bootstrap `data-toggle="dropdown"` — the React component manages open/close itself (no Bootstrap dependency).
- Jest mocks of default exports need `__esModule: true`.

---

## Task 0: Wait for PR #261, branch

- [ ] **Step 1:** Wait until PR #261 (audit + this spec/plan) is merged. Then:

```bash
git checkout master && git pull
git checkout -b c1/wave1-toolbar
```

---

## Task 1: Playwright spec (green BEFORE migration)

**Files:**
- Create: `tests/front/e2e/product/product-grid-display-selector.spec.ts`

- [ ] **Step 1: Write the spec**

```ts
import {test, expect} from '@playwright/test';
import {login, goToProductsGrid, ensureProductExists} from '../fixtures/pim';

// Guards C1 wave 1: the display selector must keep working identically
// after the Backbone → React migration (localStorage contract + reload survival).
test.describe('Product grid display selector', () => {
  test('switches display type and persists across reload', async ({page}) => {
    await login(page, 'admin', 'admin');
    await ensureProductExists(page);
    await goToProductsGrid(page);

    const selector = page.locator('.AknTitleContainer-displaySelector');
    await expect(selector).toBeVisible();

    // open the dropdown and pick the gallery display
    await selector.click();
    const galleryItem = page.locator('.display-selector-item[data-type="gallery"]');
    await expect(galleryItem).toBeVisible();
    await galleryItem.click();

    // selection triggers a full page reload; the contract is the localStorage key
    await page.waitForLoadState('load');
    expect(await page.evaluate(() => localStorage.getItem('display-selector:product-grid'))).toBe('gallery');

    // survives an explicit reload
    await page.reload();
    await page.waitForLoadState('load');
    expect(await page.evaluate(() => localStorage.getItem('display-selector:product-grid'))).toBe('gallery');
    await expect(page.locator('.AknTitleContainer-displaySelector')).toBeVisible();

    // switch back to list
    await page.locator('.AknTitleContainer-displaySelector').click();
    const listItem = page.locator('.display-selector-item[data-type="list"]');
    await expect(listItem).toBeVisible();
    await listItem.click();
    await page.waitForLoadState('load');
    expect(await page.evaluate(() => localStorage.getItem('display-selector:product-grid'))).toBe('list');
  });
});
```

Notes for the implementer:
- MUST fail (not skip) if fixtures are missing — do not wrap in try/skip.
- If the actual `data-type` values differ (check `table.js` `displayTypes` config and the grid YAML), adapt `gallery`/`list` to the real keys — verify via `grep -rn "displayTypes" src/Oro/Bundle/PimDataGridBundle/Resources/config/datagrid/product.yml` and `src/Akeneo/.../product/index.yml`.

- [ ] **Step 2: Commit**

```bash
git add tests/front/e2e/product/product-grid-display-selector.spec.ts
git commit -m "test(e2e): product grid display selector contract"
```

---

## Task 2: LocaleSwitcher race fix

**Files:**
- Modify: `src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/product/grid/locale-switcher.tsx`

- [ ] **Step 1: Add the removal guard.** Current bug: `render()` mounts React inside `fetchLocales().then(...)`; if `remove()` runs first, React mounts into a detached node after `unmountReact()` (leak). Apply:

```tsx
class LocaleSwitcher extends BaseView {
  private config: any;
  private removed = false;
  // ... initialize/configure unchanged ...

  render(): any {
    this.fetchLocales().then((locales: Locale[]) => {
      if (this.removed) {
        return;
      }
      // ... existing body unchanged ...
    });

    return this;
  }

  remove(): any {
    this.removed = true;

    return super.remove();
  }
  // ... rest unchanged ...
}
```

- [ ] **Step 2: Commit**

```bash
git add src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/product/grid/locale-switcher.tsx
git commit -m "fix(grid): guard LocaleSwitcher against render-after-remove race"
```

---

## Task 3: Remove GridViewsView no-op mount

**Files:**
- Modify: `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datagrid-builder.js`

The audit verified `gridGridViewsSelector` (`.page-title > .AknTitleContainer .span10:last`) matches zero nodes; the append at line 117 is a silent no-op. The real view dropdown is `pim/grid/view-selector` (UIBundle), untouched.

- [ ] **Step 1: Remove** (a) `import GridViewsView from 'oro/datagrid/grid-views/view';` (line 7), (b) the `gridGridViewsSelector` variable (line 10), (c) the "create grid view" block:

```js
      // create grid view
      options = methods.combineGridViewsOptions.call(this);
      $(gridGridViewsSelector).append(new GridViewsView(_.extend({collection: collection}, options)).render().$el);
```

(d) If `combineGridViewsOptions` is now unreferenced, remove that method too (verify with grep first).

- [ ] **Step 2: Commit**

```bash
git add src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datagrid-builder.js
git commit -m "refactor(datagrid): remove GridViewsView no-op mount (selector matches nothing)"
```

---

## Task 4: DisplaySelector React component + Jest test

**Files:**
- Create: `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datagrid/DisplaySelector.tsx`
- Create: `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/DisplaySelector.unit.tsx`

- [ ] **Step 1: Write the component** (markup mirrors the legacy template — same Akn classes and data hooks — open/close state in React):

```tsx
import React, {useRef} from 'react';
import {useBooleanState} from 'akeneo-design-system';

type DisplayType = {
  label: string;
};

type DisplaySelectorProps = {
  types: {[name: string]: DisplayType};
  selectedType: string;
  displayLabel: string;
  onChange: (type: string) => void;
};

const DisplaySelector = ({types, selectedType, displayLabel, onChange}: DisplaySelectorProps) => {
  const [isOpen, open, close] = useBooleanState(false);
  const containerRef = useRef<HTMLDivElement>(null);

  const handleSelect = (type: string) => {
    close();
    onChange(type);
  };

  return (
    <div ref={containerRef} className={isOpen ? 'open' : ''}>
      <div className="AknActionButton AknActionButton--withoutBorder" onClick={isOpen ? close : open}>
        {displayLabel}: <span className="AknActionButton-highlight">{types[selectedType]?.label}</span>
        <span className="AknActionButton-caret" />
      </div>
      {isOpen && (
        <ul className="AknDropdown-menu">
          <div className="AknDropdown-menuTitle">{displayLabel}</div>
          {Object.entries(types).map(([key, type]) => (
            <li key={key} className="display-selector-item" data-type={key} onClick={() => handleSelect(key)}>
              <a className={`AknDropdown-menuLink${key === selectedType ? ' AknDropdown-menuLink--active' : ''}`} data-type={key}>
                {type.label}
              </a>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
};

export {DisplaySelector};
export type {DisplaySelectorProps};
```

Note: the legacy Bootstrap dropdown toggled an `open` class on the root — keep that class for Akn CSS compatibility. The host's `el` carries the `AknDropdown AknDropdown--left AknTitleContainer-displaySelector` classes (Task 5), so the component root stays a plain div.

- [ ] **Step 2: Write the Jest test**

```tsx
import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {DisplaySelector} from '../../Resources/public/js/datagrid/DisplaySelector';

const types = {
  list: {label: 'List'},
  gallery: {label: 'Gallery'},
};

test('It renders the selected type label and opens the menu', () => {
  renderWithProviders(
    <DisplaySelector types={types} selectedType="list" displayLabel="Views" onChange={jest.fn()} />
  );

  expect(screen.getByText('List')).toBeInTheDocument();
  expect(screen.queryByText('Gallery')).not.toBeInTheDocument();

  userEvent.click(screen.getByText('List'));
  expect(screen.getByText('Gallery')).toBeInTheDocument();
});

test('It calls onChange with the clicked type and closes the menu', () => {
  const onChange = jest.fn();
  renderWithProviders(
    <DisplaySelector types={types} selectedType="list" displayLabel="Views" onChange={onChange} />
  );

  userEvent.click(screen.getByText('List'));
  userEvent.click(screen.getByText('Gallery'));

  expect(onChange).toHaveBeenCalledWith('gallery');
  expect(screen.queryByText('Gallery')).not.toBeInTheDocument();
});

test('It marks the selected item active and keeps data-type hooks', () => {
  renderWithProviders(
    <DisplaySelector types={types} selectedType="gallery" displayLabel="Views" onChange={jest.fn()} />
  );

  userEvent.click(screen.getAllByText('Gallery')[0]);
  const items = document.querySelectorAll('.display-selector-item');
  expect(items).toHaveLength(2);
  expect(items[1].getAttribute('data-type')).toBe('gallery');
  expect(items[1].querySelector('a')!.className).toContain('AknDropdown-menuLink--active');
});
```

Note: `selectedType="gallery"` renders "Gallery" both in the toggle and (when open) in the menu — hence `getAllByText`.

- [ ] **Step 3: Commit**

```bash
git add src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datagrid/DisplaySelector.tsx \
        src/Oro/Bundle/PimDataGridBundle/tests/front/unit/DisplaySelector.unit.tsx
git commit -m "feat(datagrid): DisplaySelector React component"
```

---

## Task 5: Host swap — display-selector.js → display-selector.tsx

**Files:**
- Create: `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datagrid/display-selector.tsx`
- Delete: `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datagrid/display-selector.js`

Same module path minus extension — the alias `pim/datagrid/display-selector` keeps resolving (`.tsx` proven by `ProductGalleryRow.tsx` in the same dir). **Zero YAML change.**

- [ ] **Step 1: Write the host**

```tsx
import React from 'react';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import Routing from 'pim/router';
import {DisplaySelector} from './DisplaySelector';

type DisplayTypeConfig = {[name: string]: {label: string}};

/**
 * Thin Backbone host for the React DisplaySelector.
 * Keeps the legacy contracts intact:
 * - listens to grid_load:start on the root form (bridged from the mediator
 *   by the forwarded-events config in form_extensions/product/index.yml)
 * - stores the chosen type in localStorage under `display-selector:<gridName>`
 *   (read by pim/grid/table applyDisplayType at bootstrap)
 * - full page reload on change (same behavior as the legacy implementation)
 */
class DisplaySelectorView extends (BaseForm as any) {
  public className = 'AknDropdown AknDropdown--left AknTitleContainer-displaySelector';
  private gridName: string | null = null;

  initialize(options: {config: {gridName: string}}) {
    this.gridName = options.config.gridName;

    if (null === this.gridName) {
      throw new Error('You must specify gridName for the display-selector');
    }

    return BaseForm.prototype.initialize.apply(this, arguments);
  }

  configure() {
    this.listenTo(this.getRoot(), 'grid_load:start', this.collectDisplayOptions.bind(this));

    return BaseForm.prototype.configure.apply(this, arguments);
  }

  collectDisplayOptions(_collection: unknown, gridView: {options: {displayTypes?: DisplayTypeConfig}}) {
    const displayTypes = gridView.options.displayTypes;

    if (undefined === displayTypes) {
      return;
    }

    const types: DisplayTypeConfig = {};
    for (const name in displayTypes) {
      types[name] = {...displayTypes[name], label: __(displayTypes[name].label)};
    }

    this.renderDisplayTypes(types);
  }

  getStoredType(): string | null {
    return localStorage.getItem(`display-selector:${this.gridName}`);
  }

  setDisplayType(type: string) {
    localStorage.setItem(`display-selector:${this.gridName}`, type);

    return Routing.reloadPage();
  }

  renderDisplayTypes(types: DisplayTypeConfig) {
    const firstType = Object.keys(types)[0];
    let selectedType = this.getStoredType();

    if (null === selectedType || undefined === types[selectedType]) {
      selectedType = firstType;
    }

    this.renderReact(
      DisplaySelector,
      {
        types,
        selectedType,
        displayLabel: __('pim_datagrid.display_selector.label'),
        onChange: this.setDisplayType.bind(this),
      },
      this.el
    );

    return this;
  }
}

export = DisplaySelectorView;
```

Implementer notes:
- Check how `BaseForm`/`BaseView` are actually composed in TS hosts — mirror `locale-switcher.tsx` (extends `BaseView`, applies `BaseForm.prototype` methods) if `extends (BaseForm as any)` misbehaves. The existing `column-selector.ts` is also a TS BaseForm subclass — follow whichever pattern it uses (check its imports/extends first).
- `renderReact` lives on `BaseView` (`base.ts:279-287`) and wraps ThemeProvider+DependenciesProvider automatically; unmount handled by `BaseView.remove()`.
- Delete the legacy template import usage; check whether `pim/template/datagrid/display-selector` is referenced elsewhere (`grep -rn "datagrid/display-selector" src --include="*.yml" --include="*.js"`) before deleting the template file itself — if unreferenced, delete `display-selector.html` too.

- [ ] **Step 2: Delete the legacy file**

```bash
git rm src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datagrid/display-selector.js
```

- [ ] **Step 3: Commit**

```bash
git add -A src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datagrid/
git commit -m "feat(datagrid): migrate display-selector to React (C1 wave 1)"
```

---

## Task 6: Audit report amendment

**Files:**
- Modify: `docs/plans/2026-06-10-product-grid-audit.md` (§2, bus-mismatch note)

- [ ] **Step 1:** Replace the bus-mismatch paragraph 1 with:

```markdown
1. `grid_load:complete`/`grid_load:start` are fired on the **mediator** by `grid.js`, while `display-selector.js` and `grid/mass-actions.js:43` subscribe via `listenTo(getRoot(), …)` (the form-root bus). **Correction (wave 1):** on the product index page these listeners ARE alive — `form_extensions/product/index.yml:7-9` declares `forwarded-events: {grid_load:start, grid_load:complete}` on the root form `pim-product-index`. The mismatch remains a real risk for any page whose root form lacks that bridge, and any migration must replicate the bridge explicitly.
```

- [ ] **Step 2: Commit**

```bash
git add docs/plans/2026-06-10-product-grid-audit.md
git commit -m "docs: amend audit — grid_load listeners bridged via product index forwarded-events"
```

---

## Task 7: PR

- [ ] **Step 1:**

```bash
git push -u origin c1/wave1-toolbar
gh pr create --title "feat(datagrid): C1 wave 1 — display-selector to React, toolbar prerequisites" --body "..."
gh pr merge <N> --auto --squash
```

PR body summarizes: Playwright contract spec, LocaleSwitcher race fix, GridViewsView no-op removal, display-selector migration (same module path, localStorage contract intact), audit amendment.

- [ ] **Step 2: Monitor CI** — `test-playwright` and `test-front-unit` are the gating jobs; Behat must stay green.

---

## Self-Review Notes

- Spec coverage: Playwright (T1) ✓, race fix (T2) ✓, no-op removal (T3) ✓, migration (T4+T5) ✓, audit amendment (T6) ✓.
- Type consistency: `DisplaySelectorProps.onChange(type)` = host `setDisplayType(type)` ✓; `displayLabel` prop threaded ✓.
- Contracts: localStorage key format unchanged ✓; `.display-selector-item[data-type]` hooks preserved in component ✓; module path unchanged ✓.
- Open uncertainty flagged to implementer: exact BaseForm-in-TS extends pattern (mirror column-selector.ts / locale-switcher.tsx), and real `displayTypes` keys for the Playwright spec (verify in YAML).
