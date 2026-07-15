# Select-family filters → React DSM — Foundation (sub-project #1) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the legacy `jquery.multiselect` widget for the `select` datagrid filter with the in-house React `akeneo-design-system` `SelectInput`/`MultiSelectInput`, via a reusable `SelectFilterCriteria.tsx` component + a `select-filter-react.ts` bridge, plus bi-markup Behat decorators so the not-yet-migrated consumers stay green.

**Architecture:** Strangler-Fig bridge added ALONGSIDE the legacy `select-filter.js`; only the `select` `FilterTypeRegistry` alias is re-pointed. The bridge is a Backbone `SelectFilter.extend` whose React state (`_selectedValues: string[]`) is the single source of truth (same pattern as `price`/`date` bridges): `render` calls `AbstractFilter.prototype.render` then mounts React (no legacy `<select>`/widget build), `remove` unmounts (the DSM overlay is a React portal, so no jQuery-orphan cleanup needed). Behat drives the DSM markup via `data-testid` hooks with a legacy fallback.

**Tech Stack:** React 17 (`ReactDOM.render`/`unmountComponentAtNode`), TypeScript, Backbone `.extend`, `akeneo-design-system` (`SelectInput`/`MultiSelectInput`), Jest + `@testing-library/react`, Stryker (per-PR mutation), Behat (PHP Mink decorators).

## Global Constraints

- **NEVER run Jest locally** — it OOM-crashes the machine. Do NOT run `yarn unit` / `jest` / Stryker locally. Local verification is `yarn lint` (ESLint+Prettier, safe) for TS/TSX and `docker-compose run --rm php php -l <file>` for PHP. Jest, `mutation-testing-front`, and Behat are validated in CI only.
- Bridge added ALONGSIDE the legacy `.js`; do NOT modify `select-filter.js`, `multiselect-filter.js`, `select-row-filter.js`, `multiselect-decorator.js`, or `jquery.multiselect*`.
- React state is the single source of truth; `_readDOMValue` reads state, never a DOM `<select>` (there is none). Do NOT re-introduce a `<select>` element.
- Every NEW `*.unit.tsx` MUST be added to `tests/front/unit/jest/stryker.jest.js` `testMatch` or all its mutants survive (MSI 0 → CI break at 50%). Every method DEFINED in the bridge `.ts` must be executed by a test or its mutants survive.
- Markup contract: keep the `.filter-select` wrapper class (Behat entry point) and stamp `data-testid="select-filter-widget"` on it; `MultiSelectInput.Option`s carry `data-testid={value}` (`SelectInput` stamps it automatically).
- i18n: the bridge (`.ts`) translates via `__` from `'oro/translator'` and passes plain strings to the component. Keys: placeholder = `this.placeholder` (per-instance), `openLabel = __('pim_common.open')`, `emptyResultLabel = __('pim_common.no_result')`, `removeLabel = __('pim_common.remove')`.
- No new external dependency. Commit messages: no `Co-Authored-By`, no mention of AI/Claude.
- Worktree: all work happens in `/home/gumiranda/claude-worktrees/pim-community-dev/select-filter-foundation` on branch `c1/select-filter-react-foundation`.

---

### Task 1: `SelectFilterCriteria.tsx` (shared controlled React view)

**Files:**
- Create: `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/filter/SelectFilterCriteria.tsx`
- Test: `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/SelectFilterCriteria.unit.tsx`

**Interfaces:**
- Consumes: `akeneo-design-system` `SelectInput` (`value: string|null`, `onChange: (v:string|null)=>void`, `clearable`, children `SelectInput.Option value=…`), `MultiSelectInput` (`value: string[]`, `onChange: (v:string[])=>void`, `placeholder/emptyResultLabel/openLabel/removeLabel`, children `MultiSelectInput.Option value=… data-testid=…`).
- Produces (consumed by Task 2): default export React component `SelectFilterCriteria` with `Props = { multiple: boolean; value: string[]; choices: {value:string;label:string}[]; showLabel: boolean; label: string; canDisable: boolean; nullLink: string; placeholder: string; emptyResultLabel: string; openLabel: string; removeLabel: string; onChange: (values: string[]) => void; onDisable: () => void; }`. Single-select maps `SelectInput` `null`→`[]` and `v`→`[v]`; multi passes `value`/`onChange` straight through. Renders `.filter-select[data-testid="select-filter-widget"]` wrapper + sibling `.disable-filter` when `canDisable`.

- [ ] **Step 1: Write the failing test**

Create `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/SelectFilterCriteria.unit.tsx`:

```tsx
// Mock the DSM inputs to lightweight stand-ins that capture the onChange the component wires to them,
// so the test verifies SelectFilterCriteria's OWN logic (single/multi split, data-testid, value mapping,
// disable) without pulling styled-components/theme into ts-jest. `mockCaptured` is mock-prefixed so the
// jest hoist allows it; it is only dereferenced at render time (after module init), so no TDZ.
const mockCaptured: {single?: (v: string | null) => void; multi?: (v: string[]) => void} = {};

jest.mock('akeneo-design-system', () => {
  const React = require('react');
  const SelectInput: any = (props: any) => {
    mockCaptured.single = props.onChange;
    return React.createElement('div', {'data-dsm': 'select', 'data-value': props.value ?? ''}, props.children);
  };
  SelectInput.Option = (props: any) => React.createElement('span', {'data-testid': props.value}, props.children);
  const MultiSelectInput: any = (props: any) => {
    mockCaptured.multi = props.onChange;
    return React.createElement('div', {'data-dsm': 'multi', 'data-value': (props.value || []).join(',')}, props.children);
  };
  MultiSelectInput.Option = (props: any) => React.createElement('span', {'data-testid': props.value}, props.children);
  return {__esModule: true, SelectInput, MultiSelectInput};
});

import React from 'react';
import {render} from '@testing-library/react';
import SelectFilterCriteria from '../../../Resources/public/js/datafilter/filter/SelectFilterCriteria';

const choices = [
  {value: 'red', label: 'Red'},
  {value: 'blue', label: 'Blue'},
];

const baseProps = {
  choices,
  showLabel: true,
  label: 'Color',
  canDisable: true,
  nullLink: '#null',
  placeholder: 'All',
  emptyResultLabel: 'No result',
  openLabel: 'Open',
  removeLabel: 'Remove',
  onDisable: jest.fn(),
};

describe('SelectFilterCriteria', () => {
  test('single: renders SelectInput with the wrapper hook, options, label and disable link', () => {
    const onChange = jest.fn();
    const {container} = render(
      <SelectFilterCriteria {...baseProps} multiple={false} value={['red']} onChange={onChange} />
    );

    expect(container.querySelector('.filter-select.filter-criteria-selector[data-testid="select-filter-widget"]')).not.toBeNull();
    expect(container.querySelector('[data-dsm="select"]')).not.toBeNull();
    expect(container.querySelector('[data-dsm="select"]')!.getAttribute('data-value')).toBe('red');
    expect(container.querySelector('.AknFilterBox-filterLabel')!.textContent).toBe('Color');
    expect(container.querySelectorAll('[data-dsm="select"] span')).toHaveLength(2);
    const disable = container.querySelector('a.disable-filter') as HTMLAnchorElement;
    expect(disable).not.toBeNull();
    expect(disable.getAttribute('href')).toBe('#null');
  });

  test('single: onChange maps SelectInput null→[] and value→[value]', () => {
    const onChange = jest.fn();
    render(<SelectFilterCriteria {...baseProps} multiple={false} value={[]} onChange={onChange} />);

    mockCaptured.single!('blue');
    expect(onChange).toHaveBeenLastCalledWith(['blue']);
    mockCaptured.single!(null);
    expect(onChange).toHaveBeenLastCalledWith([]);
  });

  test('multi: renders MultiSelectInput and passes value/onChange straight through', () => {
    const onChange = jest.fn();
    const {container} = render(
      <SelectFilterCriteria {...baseProps} multiple={true} value={['red', 'blue']} onChange={onChange} />
    );

    expect(container.querySelector('[data-dsm="multi"]')).not.toBeNull();
    expect(container.querySelector('[data-dsm="multi"]')!.getAttribute('data-value')).toBe('red,blue');
    mockCaptured.multi!(['red']);
    expect(onChange).toHaveBeenLastCalledWith(['red']);
  });

  test('hides the label and the disable link when disabled', () => {
    const {container} = render(
      <SelectFilterCriteria {...baseProps} multiple={false} value={[]} showLabel={false} canDisable={false} onChange={jest.fn()} />
    );

    expect(container.querySelector('.AknFilterBox-filterLabel')).toBeNull();
    expect(container.querySelector('a.disable-filter')).toBeNull();
  });

  test('the disable link prevents default and calls onDisable', () => {
    const onDisable = jest.fn();
    const {container} = render(
      <SelectFilterCriteria {...baseProps} multiple={false} value={[]} onDisable={onDisable} onChange={jest.fn()} />
    );
    const disable = container.querySelector('a.disable-filter') as HTMLAnchorElement;
    const event = new MouseEvent('click', {bubbles: true, cancelable: true});
    disable.dispatchEvent(event);
    expect(onDisable).toHaveBeenCalled();
    expect(event.defaultPrevented).toBe(true);
  });
});
```

- [ ] **Step 2: Verify the test would fail (module missing)**

Do NOT run Jest (OOM). Confirm by inspection: `SelectFilterCriteria.tsx` does not exist yet, so the import resolves to nothing → the suite cannot pass. Proceed.

- [ ] **Step 3: Implement `SelectFilterCriteria.tsx`**

Create `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/filter/SelectFilterCriteria.tsx`:

```tsx
import React from 'react';
import {SelectInput, MultiSelectInput} from 'akeneo-design-system';

type Choice = {value: string; label: string};

type Props = {
  multiple: boolean;
  value: string[];
  choices: Choice[];
  showLabel: boolean;
  label: string;
  canDisable: boolean;
  nullLink: string;
  placeholder: string;
  emptyResultLabel: string;
  openLabel: string;
  removeLabel: string;
  onChange: (values: string[]) => void;
  onDisable: () => void;
};

/**
 * Controlled React view of the `select` family of datagrid filters (Vague B). Replaces the legacy
 * `jquery.multiselect` widget with the in-house DSM `SelectInput` (single) / `MultiSelectInput` (multi).
 *
 * The internal value is ALWAYS `string[]` so the multi bridge reuses this unchanged; the single branch
 * maps `SelectInput`'s `null`↔`[]` and `v`↔`[v]`. The `.filter-select` wrapper keeps the Behat entry
 * class and adds `data-testid="select-filter-widget"`; `MultiSelectInput.Option`s carry
 * `data-testid={value}` (`SelectInput` stamps it itself). The DSM overlay is a React portal, so the
 * bridge's `ReactDOM.unmountComponentAtNode` tears it down — no jQuery-orphan cleanup.
 *
 * The `.disable-filter` link is a SIBLING of `.filter-select` (mirrors the legacy `select-filter` template).
 */
const SelectFilterCriteria = ({
  multiple,
  value,
  choices,
  showLabel,
  label,
  canDisable,
  nullLink,
  placeholder,
  emptyResultLabel,
  openLabel,
  removeLabel,
  onChange,
  onDisable,
}: Props) => (
  <>
    <div className="AknFilterBox-filter filter-select filter-criteria-selector" data-testid="select-filter-widget">
      {showLabel && <span className="AknFilterBox-filterLabel">{label}</span>}
      {multiple ? (
        <MultiSelectInput
          value={value}
          onChange={onChange}
          placeholder={placeholder}
          emptyResultLabel={emptyResultLabel}
          openLabel={openLabel}
          removeLabel={removeLabel}
        >
          {choices.map(choice => (
            <MultiSelectInput.Option key={choice.value} value={choice.value} data-testid={choice.value}>
              {choice.label}
            </MultiSelectInput.Option>
          ))}
        </MultiSelectInput>
      ) : (
        <SelectInput
          clearable
          value={value[0] ?? null}
          onChange={(newValue: string | null) => onChange(newValue === null ? [] : [newValue])}
          placeholder={placeholder}
          emptyResultLabel={emptyResultLabel}
          openLabel={openLabel}
        >
          {choices.map(choice => (
            <SelectInput.Option key={choice.value} value={choice.value}>
              {choice.label}
            </SelectInput.Option>
          ))}
        </SelectInput>
      )}
    </div>
    {canDisable && (
      <a
        href={nullLink}
        className="AknFilterBox-disableFilter AknIconButton AknIconButton--remove disable-filter"
        onClick={event => {
          event.preventDefault();
          onDisable();
        }}
      />
    )}
  </>
);

export default SelectFilterCriteria;
```

- [ ] **Step 4: Lint the new files**

Run: `cd /home/gumiranda/claude-worktrees/pim-community-dev/select-filter-foundation && yarn lint 2>&1 | tail -20`
Expected: no errors for the two new files (Prettier/ESLint clean). Fix any reported issue in these files only. Do NOT run Jest.

- [ ] **Step 5: Commit**

```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/select-filter-foundation
git add src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/filter/SelectFilterCriteria.tsx \
        src/Oro/Bundle/PimDataGridBundle/tests/front/unit/SelectFilterCriteria.unit.tsx
git commit -m "feat(datagrid): SelectFilterCriteria — controlled React view over DSM SelectInput/MultiSelectInput"
```

---

### Task 2: `select-filter-react.ts` (bridge)

**Files:**
- Create: `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/filter/select-filter-react.ts`
- Test: `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/select-filter-react.unit.tsx`

**Interfaces:**
- Consumes: legacy `oro/datafilter/select-filter` (base to extend: instance `choices`, `placeholder`, `populateDefault`, `showLabel`, `label`, `canDisable`, `nullLink`, `widgetOptions.multiple`, `el`; prototype `getValue()`, `setValue(v)`, `_formatRawValue(v)`, `disable()`); `oro/datafilter/abstract-filter` (prototype `render`, `remove`, `_onValueUpdated`); `oro/translator` default `__`; `react`, `react-dom`; `./SelectFilterCriteria` (Task 1).
- Produces (consumed by Task 3 registry + wave-2/3 bridges): default export = `SelectFilter.extend({...})`. Public overrides: `render()`, `_renderReact()`, `_seedSelectedValues()`, `_normalizeToArray(v)`, `_reactChoices()`, `_onReactChange(values)`, `_readDOMValue()`, `_writeDOMValue(value)`, `_onValueUpdated(newValue, oldValue)`, `remove()`, and `events: {}`. State: `this._selectedValues: string[]` (seeded lazily, never a shared prototype array).

- [ ] **Step 1: Write the failing test**

Create `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/select-filter-react.unit.tsx`:

```tsx
// Mock the legacy base + AbstractFilter so the bridge's inherited machinery is stubbed; render the child
// component to a prop-capturing div via a real react-dom mount (asserted through filter.el).
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
      this.widgetOptions = {multiple: false};
      this._value = {value: 'red'};
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

import AbstractFilter from 'oro/datafilter/abstract-filter';
import Bridge from '../../../Resources/public/js/datafilter/filter/select-filter-react';

beforeEach(() => jest.clearAllMocks());

describe('select-filter-react', () => {
  test('render seeds _selectedValues from the model and mounts the React view', () => {
    const filter: any = new (Bridge as any)();
    filter.render();

    expect(filter._selectedValues).toEqual(['red']);
    const rendered = filter.el.querySelector('[data-multiple="false"]');
    expect(rendered).not.toBeNull();
    expect(rendered!.getAttribute('data-value')).toBe('red');
  });

  test('_normalizeToArray maps empty/string/array consistently', () => {
    const filter: any = new (Bridge as any)();
    expect(filter._normalizeToArray('')).toEqual([]);
    expect(filter._normalizeToArray(null)).toEqual([]);
    expect(filter._normalizeToArray('red')).toEqual(['red']);
    expect(filter._normalizeToArray(['red', 'blue'])).toEqual(['red', 'blue']);
  });

  test('_reactChoices sorts by label and prepends the All option when populateDefault', () => {
    const filter: any = new (Bridge as any)();
    const result = filter._reactChoices();
    expect(result[0]).toEqual({value: '', label: 'All'});
    // Blue < Red alphabetically
    expect(result.slice(1).map((c: any) => c.value)).toEqual(['blue', 'red']);
  });

  test('_readDOMValue returns the first selected value (single), empty when none', () => {
    const filter: any = new (Bridge as any)();
    filter._selectedValues = ['blue'];
    expect(filter._readDOMValue()).toEqual({value: 'blue'});
    filter._selectedValues = [];
    expect(filter._readDOMValue()).toEqual({value: ''});
  });

  test('_onReactChange stores the values, re-renders, and pushes the formatted value', () => {
    const filter: any = new (Bridge as any)();
    const renderSpy = jest.spyOn(filter, '_renderReact').mockImplementation(() => {});
    filter._onReactChange(['blue']);

    expect(filter._selectedValues).toEqual(['blue']);
    expect(renderSpy).toHaveBeenCalled();
    expect(filter.setValue).toHaveBeenCalledWith({value: 'blue', raw: true});
  });

  test('_writeDOMValue syncs state from an external value and re-renders', () => {
    const filter: any = new (Bridge as any)();
    const renderSpy = jest.spyOn(filter, '_renderReact').mockImplementation(() => {});
    filter._writeDOMValue({value: 'red'});

    expect(filter._selectedValues).toEqual(['red']);
    expect(renderSpy).toHaveBeenCalled();
  });

  test('_onValueUpdated syncs state, defers to the base, and re-renders', () => {
    const filter: any = new (Bridge as any)();
    const renderSpy = jest.spyOn(filter, '_renderReact').mockImplementation(() => {});
    const newValue = {value: 'blue'};
    filter._onValueUpdated(newValue, {value: 'red'});

    expect(filter._selectedValues).toEqual(['blue']);
    expect((AbstractFilter as any).prototype._onValueUpdated).toHaveBeenCalledWith(newValue, {value: 'red'});
    expect(renderSpy).toHaveBeenCalled();
  });

  test('remove unmounts React then defers to AbstractFilter.remove', () => {
    const filter: any = new (Bridge as any)();
    filter.render();
    expect(filter.el.childNodes.length).toBeGreaterThan(0);

    filter.remove();
    expect(filter.el.childNodes.length).toBe(0);
    expect((AbstractFilter as any).prototype.remove).toHaveBeenCalled();
  });
});
```

- [ ] **Step 2: Verify the test would fail (module missing)**

Do NOT run Jest. `select-filter-react.ts` does not exist → import unresolved → suite cannot pass. Proceed.

- [ ] **Step 3: Implement `select-filter-react.ts`**

Create `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/filter/select-filter-react.ts`:

```ts
import _ from 'underscore';
import React from 'react';
import ReactDOM from 'react-dom';
import __ from 'oro/translator';
import AbstractFilter from 'oro/datafilter/abstract-filter';
import SelectFilter from 'oro/datafilter/select-filter';
import SelectFilterCriteria from './SelectFilterCriteria';

/**
 * React inner-render of the `select` datagrid filter (Vague B — the first `jquery.multiselect`-bearing
 * filter migrated to React). Extends the legacy `SelectFilter` to inherit its value shape
 * (`getValue`/`setValue`/`_formatRawValue`/`_formatDisplayValue`, `disable`) and replaces ONLY the markup:
 * the underscore `<select>` template + `MultiselectDecorator` widget are swapped for the controlled DSM
 * `SelectInput`/`MultiSelectInput` rendered by `SelectFilterCriteria`.
 *
 * `this._selectedValues` (string[]) is the single source of truth — `_readDOMValue` reads it (there is no
 * `<select>`). The DSM overlay is a React portal, so `remove` just unmounts (no jQuery-orphan cleanup).
 *
 * Added ALONGSIDE `select-filter.js`; only the `select` FilterTypeRegistry alias is re-pointed.
 */
export default SelectFilter.extend({
  // React owns every interaction; the legacy `change select`/`click .filter-select` handlers referenced
  // DOM that no longer exists, and `.disable-filter` is handled by the React onClick — drop them all.
  events: {},

  /**
   * {@inheritdoc}
   *
   * Base wiring (AbstractFilter, NOT the legacy `<select>`+widget build), seed state, mount React.
   */
  render: function () {
    AbstractFilter.prototype.render.apply(this, arguments);

    if (_.isUndefined(this._selectedValues)) {
      this._selectedValues = this._seedSelectedValues();
    }
    this._renderReact();

    return this;
  },

  /**
   * Seed the React value from the current model value.
   *
   * @protected
   */
  _seedSelectedValues: function () {
    return this._normalizeToArray(this.getValue().value);
  },

  /**
   * Normalize a model value (empty string / null / string / array) to the internal `string[]`.
   *
   * @protected
   */
  _normalizeToArray: function (value: unknown): string[] {
    if (value === '' || _.isNull(value) || _.isUndefined(value)) {
      return [];
    }

    return _.isArray(value) ? (value as string[]) : [String(value)];
  },

  /**
   * Replicate the legacy `render()` choice prep: translate labels, sort, prepend the "All" option.
   *
   * @protected
   */
  _reactChoices: function () {
    const options = this.choices.map((choice: {value: string; label: string}) => ({
      value: choice.value,
      label: __(choice.label),
    }));
    options.sort((a: {label: string}, b: {label: string}) => a.label.toString().localeCompare(b.label.toString()));

    if (this.populateDefault) {
      options.unshift({value: '', label: this.placeholder});
    }

    return options;
  },

  /**
   * Render (or reconcile) the controlled DSM view into `this.el`.
   *
   * @protected
   */
  _renderReact: function () {
    ReactDOM.render(
      React.createElement(SelectFilterCriteria, {
        multiple: !!this.widgetOptions.multiple,
        value: this._selectedValues,
        choices: this._reactChoices(),
        showLabel: this.showLabel,
        label: __(this.label),
        canDisable: this.canDisable,
        nullLink: this.nullLink,
        placeholder: this.placeholder,
        emptyResultLabel: __('pim_common.no_result'),
        openLabel: __('pim_common.open'),
        removeLabel: __('pim_common.remove'),
        onChange: this._onReactChange.bind(this),
        onDisable: this.disable.bind(this),
      }),
      this.el
    );
  },

  /**
   * Store the new selection (source of truth), re-render, then push the model value.
   *
   * @protected
   */
  _onReactChange: function (values: string[]) {
    this._selectedValues = values;
    this._renderReact();
    this.setValue(this._formatRawValue(this._readDOMValue()));
  },

  /**
   * {@inheritdoc}
   *
   * Read from state (single: first value or empty). The multi bridge (wave 2) overrides this.
   */
  _readDOMValue: function () {
    return {value: this._selectedValues[0] || ''};
  },

  /**
   * {@inheritdoc}
   *
   * External value → state, then re-render.
   */
  _writeDOMValue: function (value: {value: unknown}) {
    this._selectedValues = this._normalizeToArray(value.value);
    this._renderReact();

    return this;
  },

  /**
   * {@inheritdoc}
   *
   * Sync state from the new value, defer to the base, then re-render (replaces the legacy
   * `selectWidget.multiselect('refresh')`).
   */
  _onValueUpdated: function (newValue: {value: unknown}, oldValue: unknown) {
    this._selectedValues = this._normalizeToArray(newValue.value);
    AbstractFilter.prototype._onValueUpdated.apply(this, arguments);
    this._renderReact();

    return this;
  },

  /**
   * {@inheritdoc}
   *
   * Unmount the React tree (tears down the portaled DSM overlay) before Backbone removes the element.
   */
  remove: function () {
    ReactDOM.unmountComponentAtNode(this.el);

    return AbstractFilter.prototype.remove.call(this);
  },
});
```

- [ ] **Step 4: Lint**

Run: `cd /home/gumiranda/claude-worktrees/pim-community-dev/select-filter-foundation && yarn lint 2>&1 | tail -20`
Expected: clean for the two new files. Fix issues in these files only. Do NOT run Jest.

- [ ] **Step 5: Commit**

```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/select-filter-foundation
git add src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/filter/select-filter-react.ts \
        src/Oro/Bundle/PimDataGridBundle/tests/front/unit/select-filter-react.unit.tsx
git commit -m "feat(datagrid): select-filter-react bridge — SelectFilter.extend, React-state source of truth"
```

---

### Task 3: Wire `select` → React (registry + requirejs + stryker)

**Files:**
- Modify: `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/FilterTypeRegistry.ts:48`
- Modify: `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/FilterTypeRegistry.unit.ts` (3 assertions)
- Modify: `src/Oro/Bundle/PimDataGridBundle/Resources/config/requirejs.yml` (after line 148)
- Modify: `tests/front/unit/jest/stryker.jest.js` (testMatch array, after the price/metric block ~line 192)

**Interfaces:**
- Consumes: `select-filter-react.ts` (Task 2, AMD id `oro/datafilter/select-filter-react`), `SelectFilterCriteria.unit.tsx` + `select-filter-react.unit.tsx` (Tasks 1–2).
- Produces: `resolveFilterModuleId('select' | 'choice' | 'boolean')` → `'oro/datafilter/select-filter-react'`.

- [ ] **Step 1: Update the failing registry assertions**

In `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/FilterTypeRegistry.unit.ts`, change the three assertions that reference the legacy `select-filter` module id to the React module id:

- The `FILTER_MODULE_IDS['select']` test (currently `.toBe('oro/datafilter/select-filter')`) → `.toBe('oro/datafilter/select-filter-react')`.
- The `resolveFilterModuleId('choice')` test (currently `.toBe('oro/datafilter/select-filter')`) → `.toBe('oro/datafilter/select-filter-react')`.
- The `resolveFilterModuleId('boolean')` test (currently `.toBe('oro/datafilter/select-filter')`) → `.toBe('oro/datafilter/select-filter-react')`.

Leave `select-row` and `multiselect` assertions unchanged (still legacy in sub-project #1).

- [ ] **Step 2: Verify (inspection)**

Do NOT run Jest. The assertions now expect `-react` but `FILTER_MODULE_IDS['select']` still points to the legacy id → they would fail. Proceed.

- [ ] **Step 3: Re-point the registry**

In `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/FilterTypeRegistry.ts`, change line 48 from:

```ts
  select: 'oro/datafilter/select-filter',
```

to (keep the comment style of the other React entries):

```ts
  // Vague B: `select` renders via React (select-filter-react extends the legacy select-filter — the
  // jquery.multiselect widget replaced by the controlled DSM SelectInput/MultiSelectInput). The legacy
  // `oro/datafilter/select-filter` module stays for select-row/multiselect/product_scope/product_completeness.
  select: 'oro/datafilter/select-filter-react',
```

- [ ] **Step 4: Add the requirejs alias**

In `src/Oro/Bundle/PimDataGridBundle/Resources/config/requirejs.yml`, add after the existing `oro/datafilter/select-filter:` line (138) a new alias mirroring the price/metric format (aligned columns):

```yaml
        oro/datafilter/select-filter-react:         pimdatagrid/js/datafilter/filter/select-filter-react
```

- [ ] **Step 5: Add both new tests to the stryker allowlist**

In `tests/front/unit/jest/stryker.jest.js`, in the `testMatch` array, after the `metric-filter-react.unit.tsx` entry (~line 192), add:

```js
    // DataGrid select filter (Vague B) — SelectFilter bridge replacing jquery.multiselect with the
    // controlled DSM SelectInput/MultiSelectInput via SelectFilterCriteria.
    '<rootDir>/src/Oro/Bundle/PimDataGridBundle/tests/front/unit/SelectFilterCriteria.unit.tsx',
    '<rootDir>/src/Oro/Bundle/PimDataGridBundle/tests/front/unit/select-filter-react.unit.tsx',
```

- [ ] **Step 6: Lint + commit**

Run: `cd /home/gumiranda/claude-worktrees/pim-community-dev/select-filter-foundation && yarn lint 2>&1 | tail -20`
Expected: clean.

```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/select-filter-foundation
git add src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/FilterTypeRegistry.ts \
        src/Oro/Bundle/PimDataGridBundle/tests/front/unit/FilterTypeRegistry.unit.ts \
        src/Oro/Bundle/PimDataGridBundle/Resources/config/requirejs.yml \
        tests/front/unit/jest/stryker.jest.js
git commit -m "feat(datagrid): re-point select filter to select-filter-react (registry + requirejs + stryker allowlist)"
```

---

### Task 4: Bi-markup Behat decorators (DSM + legacy fallback)

**Files:**
- Modify: `tests/legacy/features/Behat/Decorator/Field/MultiSelectDecorator.php` (`setValue`)
- Modify: `tests/legacy/features/Behat/Decorator/Grid/Filter/ChoiceDecorator.php` (`getAvailableValues`)

**Interfaces:**
- Consumes: the DSM markup from Task 1 (`.filter-select[data-testid="select-filter-widget"]` wrapper opens the overlay; options are `[data-testid="<value>"]` `<span>`s in a `document.body` portal; option text = label). The legacy markup (`.select-filter-widget`, `.ui-multiselect-menu.select-filter-widget`, `li label:contains`, `li span`) still used by the unmigrated consumers.
- Produces: `setValue`/`getAvailableValues` that work against BOTH markups so the `select` grid (React) and the choice/product_scope/ajax-choice/filters-manager grids (legacy) all pass. No behavioural change to callers.

- [ ] **Step 1: Implement bi-markup `MultiSelectDecorator::setValue`**

Replace the body of `setValue` in `tests/legacy/features/Behat/Decorator/Field/MultiSelectDecorator.php` with a DSM-first, legacy-fallback implementation. The new method (full class shown for clarity):

```php
<?php

namespace Pim\Behat\Decorator\Field;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class MultiSelectDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Set the given value to the multi select (comma-separated for multi-value).
     *
     * Supports both the React DSM widget (Vague B: `.filter-select[data-testid="select-filter-widget"]`
     * opening a `document.body` overlay whose options are `[data-testid="<value>"]`) and the legacy
     * `jquery.multiselect` widget (`.select-filter-widget` → `li label:contains`).
     *
     * @throws \Exception
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $values = '' !== $value ? explode(',', $value) : [];

        if ($this->isReactWidget()) {
            $this->setReactValue($values);

            return;
        }

        $this->setLegacyValue($values, $value);
    }

    /**
     * @return bool
     */
    private function isReactWidget()
    {
        return null !== $this->find('css', '[data-testid="select-filter-widget"]');
    }

    /**
     * @param string[] $values
     */
    private function setReactValue(array $values)
    {
        foreach ($values as $value) {
            $value = trim($value);

            // Open the overlay (portaled to <body>) then click the option by its stable data-testid.
            $widget = $this->spin(function () {
                return $this->find('css', '[data-testid="select-filter-widget"]');
            }, 'Cannot find the React select widget');
            $widget->click();

            $option = $this->spin(function () use ($value) {
                return $this->getBody()->find('css', sprintf('[data-testid="%s"]', $value));
            }, sprintf('Cannot find option "%s"', $value));
            $option->click();
        }
    }

    /**
     * @param string[] $values
     * @param string   $rawValue
     *
     * @throws \Exception
     */
    private function setLegacyValue(array $values, $rawValue)
    {
        // The multiselect plugin can put many widgets in the DOM.
        // We have to find the one that is visible and active.
        $multiSelectWidgets = $this->spin(function () {
            return $this->getBody()->findAll('css', '.select-filter-widget');
        }, sprintf('Could not find any multiselect widget for filter "%s"', $rawValue));

        $visibleWidgets = array_filter($multiSelectWidgets, function ($widget) {
            return $widget->isVisible();
        });

        if (empty($visibleWidgets)) {
            throw new \Exception(
                sprintf('Could not find the multiselect widget for filter "%s"', $rawValue)
            );
        }
        $widget = end($visibleWidgets);

        // The search input for a multiselect is optional
        $search = $widget->find('css', 'input[type="search"]');
        foreach ($values as $value) {
            $value = trim($value);
            if (null !== $search) {
                $search->setValue($value);
            }

            $option = $this->spin(function () use ($widget, $value) {
                return $widget->find('css', sprintf('li label:contains("%s")', $value));
            }, sprintf('Cannot find option "%s"', $value));
            $option->click();
        }
    }
}
```

- [ ] **Step 2: Syntax-check the PHP**

Run: `cd /home/gumiranda/pim-community-dev && docker-compose run --rm php php -l tests/legacy/features/Behat/Decorator/Field/MultiSelectDecorator.php`
Expected: `No syntax errors detected`.
(Note: run from the MAIN repo dir — the Docker php service mounts it. The worktree file is a copy; if the main dir lacks the edit, copy the file content or run `php -l` on the worktree path if the container mounts it. If Docker is unavailable, skip and rely on CI + review.)

- [ ] **Step 3: Implement bi-markup `ChoiceDecorator::getAvailableValues`**

In `tests/legacy/features/Behat/Decorator/Grid/Filter/ChoiceDecorator.php`, replace `getAvailableValues` with a DSM-first, legacy-fallback version. `filter()` and `close()` are unchanged (the `.filter-select` entry + `MultiSelectDecorator` decoration still hold). New method:

```php
    /**
     * Get all available values in this filter (React DSM overlay or legacy jquery.multiselect menu).
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getAvailableValues()
    {
        // React DSM: open the widget, read the option <span> texts from the <body> portal.
        $reactWidget = $this->find('css', '[data-testid="select-filter-widget"]');
        if (null !== $reactWidget) {
            $reactWidget->click();
            $options = $this->spin(function () {
                return $this->getBody()->findAll('css', '[data-testid]');
            }, 'Cannot find options');

            $values = [];
            foreach ($options as $option) {
                // Skip the widget wrapper itself; keep the option spans.
                if ('select-filter-widget' !== $option->getAttribute('data-testid')) {
                    $values[] = $option->getText();
                }
            }

            return array_filter($values);
        }

        // Legacy: find the visible/active multiselect menu, read its `li span` texts.
        $multiSelectWidgets = $this->spin(function () {
            return $this->getBody()->findAll('css', '.ui-multiselect-menu.select-filter-widget');
        }, 'Could not find any multiselect widget');

        $visibleWidgets = array_filter($multiSelectWidgets, function ($widget) {
            return $widget->isVisible();
        });

        if (empty($visibleWidgets)) {
            throw new \Exception('Could not find the multiselect widget');
        }
        $widget = end($visibleWidgets);

        $options = $this->spin(function () use ($widget) {
            return $widget->findAll('css', 'li span');
        }, 'Cannot find options');

        $values = [];
        foreach ($options as $option) {
            $values[] = $option->getText();
        }

        return array_filter($values);
    }
```

- [ ] **Step 4: Syntax-check the PHP**

Run: `cd /home/gumiranda/pim-community-dev && docker-compose run --rm php php -l tests/legacy/features/Behat/Decorator/Grid/Filter/ChoiceDecorator.php`
Expected: `No syntax errors detected`. (Same Docker caveat as Step 2.)

- [ ] **Step 5: Commit**

```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/select-filter-foundation
git add tests/legacy/features/Behat/Decorator/Field/MultiSelectDecorator.php \
        tests/legacy/features/Behat/Decorator/Grid/Filter/ChoiceDecorator.php
git commit -m "test(datagrid): bi-markup select-filter Behat decorators (DSM data-testid + legacy fallback)"
```

---

## Post-implementation (controller)

After all 4 tasks: push the branch, open the PR, enable auto-merge (`gh pr merge --auto --squash`), and watch CI — the decisive gates are `test-front-unit` (the two new unit suites), `mutation-testing-front` (the two new files must not crater MSI), and the 10 Behat shards (the bi-markup swap on the `select`/option grids AND no regression on the still-legacy choice/product_scope/ajax-choice/filters-manager grids). CI is the only validation (no local Jest/Behat). If a Behat shard fails on the DSM overlay interaction, that is open item #1 from the spec (the exact open-trigger / option-click) — fix in the decorator, not the component.

## Self-Review notes (author)

- **Spec coverage:** `SelectFilterCriteria.tsx` (§Architecture) → Task 1; `select-filter-react.ts` bridge + value semantics (§Data flow) → Task 2; registry/requirejs/stryker/registry-test (§Files) → Task 3; bi-markup decorators (§Behat) → Task 4. Open items #2 (i18n keys) resolved to `pim_common.*`; #3 (hidden `<select>`) resolved to state-only (no hidden select); #4 (`events`) resolved to `events: {}`; #5 (`choice→select` alias) covered by Task 3's `resolveFilterModuleId('choice')` assertion + the Behat option-filter shard; #1 (DSM open-trigger) deferred to CI per §Risks.
- **Placeholder scan:** none — every code step contains full code.
- **Type consistency:** `_selectedValues: string[]`, `_normalizeToArray`, `_reactChoices`, `_onReactChange`, `_readDOMValue`, `_writeDOMValue`, `_onValueUpdated`, `remove` are named identically in the bridge, its test, and the interfaces block; the component `Props` match the bridge's `React.createElement(SelectFilterCriteria, {...})` keys exactly.
