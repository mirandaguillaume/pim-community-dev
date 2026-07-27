# `multiselect` datagrid filter → React DSM (Vague B, PR2) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Re-point the `multiselect` datagrid filter from the legacy `jquery.multiselect` widget to the React DSM `MultiSelectInput`, by adding a thin `multiselect-filter-react.ts` bridge that extends the already-merged `select-filter-react` (PR1) and carries only the multiselect-specific "All" mutual-exclusion logic.

**Architecture:** Strangler Fig, wave 2 of the select-family roadmap. PR1 (#333) already shipped the shared `SelectFilterCriteria.tsx` React view (already multi-capable via its `multiple` prop), the `select-filter-react` bridge base, and the bi-markup Behat decorators (`MultiSelectDecorator`/`ChoiceDecorator` already try the DSM markup then fall back to legacy). PR2 therefore adds ONE new source module + re-points the registry alias; it creates no new React component and needs no Behat decorator change.

**Tech Stack:** Backbone (`SelectFilter.extend` chain), React 17 + `react-dom`, `akeneo-design-system` `MultiSelectInput`, TypeScript, Jest (ts-jest) unit + Stryker mutation, legacy Behat (bi-markup, unchanged).

## Global Constraints

- **No new external dependency.** `akeneo-design-system` already ships in-repo and is imported by the datagrid bundle.
- **DSM theme wrapper is inherited, not re-declared.** The DSM `MultiSelectInput` reads the theme via styled-components context; the mount MUST be wrapped in `ThemeProvider theme={pimTheme}` + `DependenciesProvider`. PR1's `select-filter-react._renderReact` already does this, and PR2's bridge inherits `_renderReact` unchanged — do NOT re-implement `_renderReact`.
- **Byte-compatible Behat contract.** No new Behat scenarios; the existing 10 legacy shards validate the swap through the bi-markup decorators. The `MultiSelectDecorator`/`ChoiceDecorator`/`BaseDecorator` files are NOT modified in this PR.
- **Class-field-clobber lesson.** Never declare `_selectedValues` with a class-field initializer; it is seeded in the inherited `render()` and stays a bare (undeclared) instance property.
- **Stryker allowlist.** Every new source `*.unit.tsx` MUST be added to the hand-maintained `stryker.jest.js` `testMatch` array or all mutants of the new source survive → MSI 0 → the `mutation-testing-front` job breaks at 50%.
- **No local runs.** Jest OOM-crashes the machine and the Behat env is too heavy for routine local runs. Confidence = careful review + CI (`test-front-unit`, `mutation-testing-front`, the 10 legacy Behat shards). Do NOT run `yarn unit`/Behat locally.
- **Internal representation is always `string[]`.** `[]`/`['']` both mean "All" (the read rule collapses them); `[v, w, …]` are concrete selections.

---

## File Structure

| File | Create/Modify | Responsibility |
|---|---|---|
| `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/filter/multiselect-filter-react.ts` | **Create** | The bridge: `SelectFilterReact.extend` with `widgetOptions.multiple=true`, array `_readDOMValue`, and the "All" mutual-exclusion in `_onReactChange` (ported from legacy `multiselect-filter._onSelectChange`). |
| `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/multiselect-filter-react.unit.tsx` | **Create** (test) | Multi render (`MultiSelectInput` branch), array `_readDOMValue`, all branches of the "All" exclusion, `_onReactChange` store+setValue. |
| `tests/front/unit/jest/stryker.jest.js` | Modify | Allowlist the new `multiselect-filter-react.unit.tsx`. |
| `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/FilterTypeRegistry.ts` | Modify | Re-point `multiselect` → `oro/datafilter/multiselect-filter-react` (+ update the `select`-family comment). |
| `src/Oro/Bundle/PimDataGridBundle/Resources/config/requirejs.yml` | Modify | Add the AMD alias `oro/datafilter/multiselect-filter-react`. |
| `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/FilterTypeRegistry.unit.ts` | Modify (test) | Update the `multiselect` module-id + `multichoice` alias-resolution assertions. |

**Not touched (intentional):** `SelectFilterCriteria.tsx` (already multi-capable), `select-filter-react.ts` (base, inherited), `MultiSelectDecorator.php` / `ChoiceDecorator.php` / `BaseDecorator.php` (already bi-markup from PR1), the legacy `multiselect-filter.js` (kept alongside per Strangler Fig; deleted only in the roadmap's final cleanup PR).

---

## Task 1: `multiselect-filter-react.ts` bridge + unit test

**Files:**
- Create: `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/filter/multiselect-filter-react.ts`
- Test: `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/multiselect-filter-react.unit.tsx`
- Modify: `tests/front/unit/jest/stryker.jest.js` (allowlist the new test)

**Interfaces:**
- Consumes (from PR1 `select-filter-react`, already on master — inherited unchanged): `render` (seeds `this._selectedValues` via `_seedSelectedValues`→`_normalizeToArray`, then `_renderReact`), `_renderReact` (reads `this.widgetOptions.multiple`, wraps in `ThemeProvider`+`DependenciesProvider`, renders `SelectFilterCriteria`), `_reactChoices`, `_normalizeToArray`, `_writeDOMValue`, `_onValueUpdated`, `getCriteria`, `_updateCriteriaSelectorPosition`, `remove`, `events:{}`.
- Consumes (from legacy `SelectFilter`, via the PR1 base): `getValue`, `setValue`, `_formatRawValue`, `disable`.
- Produces (later waves may extend this): a multi bridge whose value read is the full `string[]` and whose change handler enforces the "All" (`''`) exclusion.

### The "All" mutual-exclusion (the only real logic in this PR)

Ported verbatim in behaviour from legacy `multiselect-filter.js._onSelectChange` (lines 43–62). Given the incoming selection `values` and the current model value:
- **Selecting "All" (`''`) collapses to `['']`** — clears every concrete selection.
- **Selecting a concrete option while "All" was active removes "All".**
- **Deselecting everything falls back to `['']`** ("All").

- [ ] **Step 1: Write the failing test**

Create `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/multiselect-filter-react.unit.tsx`:

```tsx
// Mirrors select-filter-react.unit.tsx: mock the legacy base (SelectFilter) + AbstractFilter so the
// inherited machinery is stubbed; render the child to a prop-capturing div via a real react-dom mount.
// The multi bridge extends the REAL select-filter-react (PR1), so only SelectFilter/AbstractFilter/DSM
// are mocked — select-filter-react and its overrides run for real.
jest.mock(
  'oro/datafilter/select-filter',
  () => {
    function SelectFilter(this: any) {
      this.el = document.createElement('div');
      this.choices = [
        {value: 'red', label: 'Red'},
        {value: 'blue', label: 'Blue'},
      ];
      this.placeholder = 'All';
      this.populateDefault = true;
      this.showLabel = true;
      this.label = 'Color';
      this.canDisable = true;
      this.nullLink = '#null';
      // NOTE: do NOT set this.widgetOptions here — the multi bridge declares widgetOptions:{multiple:true}
      // on its prototype; an instance property set in this constructor would SHADOW it and force single.
      this._value = {value: ['red']};
      this.setValue = jest.fn();
    }
    const proto = (SelectFilter as any).prototype;
    proto.getValue = function (this: any) {
      return this._value;
    };
    proto._formatRawValue = jest.fn((v: any) => ({...v, raw: true}));
    proto.disable = jest.fn();
    function backboneExtend(this: any, o: any) {
      const P = this;
      function S(this: any) {
        P.apply(this, arguments);
      }
      S.prototype = Object.create(P.prototype);
      Object.assign(S.prototype, o);
      (S as any).extend = backboneExtend;
      return S;
    }
    (SelectFilter as any).extend = backboneExtend;
    return SelectFilter;
  },
  {virtual: true}
);

jest.mock(
  'oro/datafilter/abstract-filter',
  () => {
    function AbstractFilter() {}
    (AbstractFilter as any).prototype.render = function (this: any) {
      return this;
    };
    (AbstractFilter as any).prototype.remove = jest.fn(function (this: any) {
      return this;
    });
    (AbstractFilter as any).prototype._onValueUpdated = jest.fn();
    return AbstractFilter;
  },
  {virtual: true}
);

jest.mock('oro/translator', () => (k: string) => k, {virtual: true});

// The inherited _renderReact wraps its mount in ThemeProvider + DependenciesProvider (DSM theme context).
// Stub them to pass-through providers so the mounted SelectFilterCriteria stand-in lands in `filter.el`.
jest.mock('styled-components', () => ({ThemeProvider: ({children}: any) => children}));
jest.mock('akeneo-design-system', () => ({pimTheme: {}}));
jest.mock('@akeneo-pim-community/legacy-bridge', () => ({DependenciesProvider: ({children}: any) => children}));

jest.mock('../../../Resources/public/js/datafilter/filter/SelectFilterCriteria', () => {
  const React = require('react');
  return {
    __esModule: true,
    default: (props: any) =>
      React.createElement('div', {
        'data-multiple': String(props.multiple),
        'data-value': (props.value || []).join(','),
        'data-choices': JSON.stringify(props.choices),
      }),
  };
});

import Bridge from '../../../Resources/public/js/datafilter/filter/multiselect-filter-react';

beforeEach(() => jest.clearAllMocks());

describe('multiselect-filter-react', () => {
  test('render seeds _selectedValues from the model and mounts the MULTI React view', () => {
    const filter: any = new (Bridge as any)();
    filter.render();

    expect(filter._selectedValues).toEqual(['red']);
    const rendered = filter.el.querySelector('[data-multiple="true"]');
    expect(rendered).not.toBeNull();
    expect(rendered!.getAttribute('data-value')).toBe('red');
  });

  test('_readDOMValue returns the FULL array (not just the first element)', () => {
    const filter: any = new (Bridge as any)();
    filter._selectedValues = ['red', 'blue'];
    expect(filter._readDOMValue()).toEqual({value: ['red', 'blue']});
    filter._selectedValues = [];
    expect(filter._readDOMValue()).toEqual({value: []});
  });

  describe('_applyAllExclusion (the "All"/`\'\'` mutual-exclusion)', () => {
    test('adding a second concrete option keeps both', () => {
      const filter: any = new (Bridge as any)();
      filter._value = {value: ['red']};
      expect(filter._applyAllExclusion(['red', 'blue'])).toEqual(['red', 'blue']);
    });

    test('clicking "All" while concrete options were selected collapses to [""]', () => {
      const filter: any = new (Bridge as any)();
      filter._value = {value: ['red']};
      expect(filter._applyAllExclusion(['red', 'blue', ''])).toEqual(['']);
    });

    test('adding a concrete option while "All" was active removes "All"', () => {
      const filter: any = new (Bridge as any)();
      filter._value = {value: ['']};
      expect(filter._applyAllExclusion(['', 'red'])).toEqual(['red']);
    });

    test('deselecting everything falls back to [""]', () => {
      const filter: any = new (Bridge as any)();
      filter._value = {value: ['red']};
      expect(filter._applyAllExclusion([])).toEqual(['']);
    });

    test('initial empty-string model is treated as ["" ] previous value', () => {
      const filter: any = new (Bridge as any)();
      filter._value = {value: ''};
      expect(filter._applyAllExclusion(['red'])).toEqual(['red']);
    });
  });

  test('_onReactChange applies the exclusion, stores state, re-renders, and pushes the formatted value', () => {
    const filter: any = new (Bridge as any)();
    filter._value = {value: ['red']};
    const renderSpy = jest.spyOn(filter, '_renderReact').mockImplementation(() => {});
    // user clicks "All" (''), which should collapse to ['']
    filter._onReactChange(['red', '']);

    expect(filter._selectedValues).toEqual(['']);
    expect(renderSpy).toHaveBeenCalled();
    expect(filter.setValue).toHaveBeenCalledWith({value: [''], raw: true});
  });
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run (CI only — do NOT run locally): the `test-front-unit` job executes `jest` over `*.unit.tsx`.
Expected: FAIL — `Cannot find module '.../multiselect-filter-react'` (source does not exist yet).

- [ ] **Step 3: Write the minimal implementation**

Create `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/filter/multiselect-filter-react.ts`:

```ts
import _ from 'underscore';
import SelectFilterReact from 'oro/datafilter/select-filter-react';

/**
 * React inner-render of the `multiselect` datagrid filter (Vague B, wave 2). Extends the PR1
 * `select-filter-react` bridge — inheriting the React mount/unmount, the `this._selectedValues`
 * (string[]) source of truth, and the whole value plumbing — and adds only the two multiselect
 * specifics:
 *  1. `widgetOptions.multiple = true`, so the inherited `_renderReact` renders the DSM
 *     `MultiSelectInput` (via `SelectFilterCriteria`'s `multiple` prop) instead of `SelectInput`.
 *  2. the "All" (`''`) mutual-exclusion previously in legacy `multiselect-filter._onSelectChange`.
 *
 * Added ALONGSIDE `multiselect-filter.js`; only the `multiselect` FilterTypeRegistry alias is
 * re-pointed. The legacy `classes` string drove the (now removed) jQuery widget and is intentionally
 * dropped — the inherited `render()` never calls `_initializeSelectWidget`, so only `multiple` is read.
 */
export default SelectFilterReact.extend({
  /**
   * Multi mode: the inherited `_renderReact` reads `this.widgetOptions.multiple` to pick
   * `MultiSelectInput` over `SelectInput`.
   *
   * @property
   */
  widgetOptions: {
    multiple: true,
  },

  /**
   * {@inheritdoc}
   *
   * The multi value is the WHOLE selection array (the single base returns only the first element).
   */
  _readDOMValue: function () {
    return {value: this._selectedValues};
  },

  /**
   * Enforce the "All" (`''`) mutual-exclusion, mirroring legacy `multiselect-filter._onSelectChange`:
   * selecting "All" clears every concrete value; selecting a concrete value clears "All"; an empty
   * selection falls back to "All" (`['']`).
   *
   * @param {string[]} values the raw new selection from `MultiSelectInput`
   * @return {string[]} the normalised selection
   * @protected
   */
  _applyAllExclusion: function (values: string[]): string[] {
    // At initialization the model value is `''` (meaning "All"); treat it as `['']` for the diff.
    const previousValue = '' === this.getValue().value ? [''] : this.getValue().value;

    // Did the user just add "All" to remove every previous selection?
    const addAll = _.contains(_.difference(values, previousValue), '');

    values = _.contains(values, '') ? _.without(values, '') : values;
    values = _.isEmpty(values) ? [''] : values;
    values = addAll ? [''] : values;

    return values;
  },

  /**
   * {@inheritdoc}
   *
   * Apply the "All" exclusion before storing, then re-render and push the formatted value (same
   * store→render→setValue shape as the single base).
   */
  _onReactChange: function (values: string[]) {
    this._selectedValues = this._applyAllExclusion(values);
    this._renderReact();
    this.setValue(this._formatRawValue(this._readDOMValue()));
  },
});
```

- [ ] **Step 4: Add the new test to the Stryker allowlist**

In `tests/front/unit/jest/stryker.jest.js`, immediately after the `select-filter-react.unit.tsx` line (currently line ~196), add:

```js
    // DataGrid multiselect filter (Vague B wave 2) — extends select-filter-react, adds the "All"
    // mutual-exclusion + array value read; renders SelectFilterCriteria's MultiSelectInput branch.
    '<rootDir>/src/Oro/Bundle/PimDataGridBundle/tests/front/unit/multiselect-filter-react.unit.tsx',
```

(No `SelectFilterCriteria.unit.tsx` entry to add — that component is unchanged from PR1 and already allowlisted.)

- [ ] **Step 5: Run the test to verify it passes**

Run (CI only): `test-front-unit` (jest) → all `multiselect-filter-react` tests PASS; `mutation-testing-front` mutates the new source and the 4 override members (`widgetOptions`, `_readDOMValue`, `_applyAllExclusion`, `_onReactChange`) are all executed by the tests above (MSI stays ≥ 50%).
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/filter/multiselect-filter-react.ts \
        src/Oro/Bundle/PimDataGridBundle/tests/front/unit/multiselect-filter-react.unit.tsx \
        tests/front/unit/jest/stryker.jest.js
git commit -m "feat(datagrid): multiselect-filter-react bridge — extends select-filter-react + the All exclusion"
```

---

## Task 2: Re-point the `multiselect` filter type to the React bridge

**Files:**
- Modify: `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/FilterTypeRegistry.ts:36` (+ comment at 48–50)
- Modify: `src/Oro/Bundle/PimDataGridBundle/Resources/config/requirejs.yml` (add alias)
- Modify: `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/FilterTypeRegistry.unit.ts` (2 assertions)

**Interfaces:**
- Consumes: `resolveFilterModuleId(metadataType)` (unchanged), the `oro/datafilter/multiselect-filter-react` module produced by Task 1, and the `multichoice → multiselect` alias (unchanged).
- Produces: `FILTER_MODULE_IDS['multiselect'] === 'oro/datafilter/multiselect-filter-react'`, so any grid whose filter metadata resolves to `multiselect` (directly, or via the `multichoice` alias) loads the React bridge.

- [ ] **Step 1: Update the failing registry unit assertions**

In `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/FilterTypeRegistry.unit.ts`:

Change the direct module-id assertion (currently line 27):

```ts
  test('multiselect', () => expect(FILTER_MODULE_IDS['multiselect']).toBe('oro/datafilter/multiselect-filter-react'));
```

Change the alias-resolution assertion (currently lines 83–84):

```ts
    test('multichoice → multiselect-filter-react', () =>
      expect(resolveFilterModuleId('multichoice')).toBe('oro/datafilter/multiselect-filter-react'));
```

- [ ] **Step 2: Run the registry test to verify it fails**

Run (CI only): `test-front-unit` → the two `multiselect`/`multichoice` assertions FAIL (registry still maps to `oro/datafilter/multiselect-filter`).
Expected: FAIL with `Expected "oro/datafilter/multiselect-filter-react" received "oro/datafilter/multiselect-filter"`.

- [ ] **Step 3: Re-point the registry**

In `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/FilterTypeRegistry.ts`, change line 36:

```ts
  multiselect: 'oro/datafilter/multiselect-filter-react',
```

Update the `select`-family comment (currently lines 48–50) so it no longer claims the legacy module backs `multiselect`:

```ts
  // Vague B: `select` and `multiselect` render via React (select-filter-react / multiselect-filter-react
  // extend the legacy filters — the jquery.multiselect widget replaced by the controlled DSM
  // SelectInput/MultiSelectInput). The legacy `oro/datafilter/select-filter` module stays for
  // select-row/product_scope/product_completeness.
  select: 'oro/datafilter/select-filter-react',
```

- [ ] **Step 4: Add the requirejs alias**

In `src/Oro/Bundle/PimDataGridBundle/Resources/config/requirejs.yml`, add — immediately after the `oro/datafilter/select-filter-react:` line, matching its column alignment:

```yaml
        oro/datafilter/multiselect-filter-react:    pimdatagrid/js/datafilter/filter/multiselect-filter-react
```

(The legacy `oro/datafilter/multiselect-filter:` alias stays — the legacy module remains on disk for the not-yet-deleted consumers.)

- [ ] **Step 5: Run the registry test to verify it passes**

Run (CI only): `test-front-unit` → the two updated assertions PASS; every other registry test is unaffected.
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/FilterTypeRegistry.ts \
        src/Oro/Bundle/PimDataGridBundle/Resources/config/requirejs.yml \
        src/Oro/Bundle/PimDataGridBundle/tests/front/unit/FilterTypeRegistry.unit.ts
git commit -m "feat(datagrid): re-point multiselect filter to multiselect-filter-react (registry + requirejs)"
```

---

## Risks / CI watch items

1. **`MultiSelectInput` decorator path is the only novel Behat surface.** PR1 validated the bi-markup `MultiSelectDecorator::setReactValue` against the SINGLE `SelectInput`; `multiselect` is the first grid to drive the multi `MultiSelectInput`. It is expected to work because `MultiSelectInput.handleOptionClick` calls `closeOverlay()` after every option click (verified in `MultiSelectInput.tsx:264`), so the decorator's per-value loop (click input → overlay opens → click option → overlay closes) re-opens cleanly each iteration — exactly the single-select rhythm. **Do NOT pre-emptively change the shared decorator** (it would risk the proven single path). If a `multiselect`/`multichoice` grid shard goes red on `Cannot find the React select widget input` / `Cannot find option`, the decorator's multi-value loop is the fix locus — but only then, with the live artifact in hand.
2. **`widgetOptions` shadowing.** The multi bridge declares `widgetOptions:{multiple:true}` on its prototype. In the unit test the mocked `SelectFilter` constructor must NOT set an instance `this.widgetOptions` (it would shadow the prototype and force single) — Task 1's mock omits it deliberately. In production the real `SelectFilter` sets `widgetOptions` on its PROTOTYPE (not per-instance), and Backbone's `extend` overrides it on the child prototype, so the real chain yields `multiple:true` correctly.
3. **`['']` vs `[]` for "All".** The inherited read rule and `_applyAllExclusion` both treat empty selections as "All"; `_applyAllExclusion` always normalises an empty result to `['']`, matching the legacy model shape (`_onSelectChange` produced `['']`). Downstream `getValue`/`_formatRawValue`/grid-reload are inherited unchanged, so the URL/state/hint see the identical value object.
4. **Which grids exercise this.** Any grid whose filter metadata type is `multiselect` or resolves via the `multichoice` alias (multi-select option / multi-select reference-data attribute filters on the product grids). No new scenarios; the existing product-grid shards cover it through the bi-markup decorators.

## Self-review checklist (run before final whole-branch review)

- [ ] Spec coverage: roadmap item #2 (`multiselect-filter-react.ts` extending the bridge, `MultiSelectInput`, the "All" logic) — implemented in Task 1; the re-point + wiring in Task 2. ✓
- [ ] No placeholders: every code block is complete and copy-paste-ready. ✓
- [ ] Type consistency: the bridge overrides (`widgetOptions`, `_readDOMValue`, `_applyAllExclusion`, `_onReactChange`) match the members the tests exercise and the PR1 base's names (`_selectedValues`, `_renderReact`, `_formatRawValue`, `getValue`, `setValue`). ✓
- [ ] Mutation coverage: all four override members are executed by a Task 1 test (render→multi, `_readDOMValue`, all `_applyAllExclusion` branches, `_onReactChange`). ✓
- [ ] Stryker allowlist updated for the one new `*.unit.tsx`. ✓
