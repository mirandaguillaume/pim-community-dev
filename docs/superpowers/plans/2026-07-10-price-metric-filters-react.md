# Price & Metric Filters → React — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Render the `price` and `metric` datagrid filters via React (C1 Wave 5 Strangler-Fig bridges), reusing a shared criteria component + a generic option dropdown.

**Architecture:** Two new presentational components (`FilterOptionDropdown` generic currency/unit AknDropdown; `NumberUnitFilterCriteria` shared popup) + two thin bridges (`price-filter-react.ts`, `metric-filter-react.ts`) that `extend number-filter-react` and re-add only the currency/unit layer copied from the legacy `price-filter.js`/`metric-filter.js`. Only the `price`/`metric` FilterTypeRegistry aliases are re-pointed; the legacy `.js` stay. Markup is byte-identical for the Behat contract.

**Tech Stack:** React 17, TypeScript, Backbone `.extend`, requirejs, Jest (ts-jest) unit tests, Stryker mutation, legacy Behat.

## Global Constraints

- CI cannot be run locally: **never run Jest locally** (crashes the machine); the behat env is heavy. Rely on careful review + CI.
- Every new `*.unit.tsx` MUST be added to `tests/front/unit/jest/stryker.jest.js` `testMatch` (else `mutation-testing-front` → MSI 0, break at 50).
- Bridges are `.ts`, components `.tsx`. Re-pointing a `FilterTypeRegistry` entry breaks `FilterTypeRegistry.unit.ts` (1 assertion per type) — update it.
- Do NOT modify `OperatorDropdown.tsx` or `ChoiceFilterCriteria.tsx` (shared by merged filters).
- Byte-identity target: dropdowns keep `data-toggle="dropdown"` + `span.label.<currency|unit>_choice[data-value]` + hidden `input[name="currency_currency"|"metric_unit"]`; value input keeps `input[name="value"].AknTextField.select-field`.

---

### Task 1: `FilterOptionDropdown.tsx` — generic currency/unit AknDropdown

**Files:**
- Create: `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/filter/FilterOptionDropdown.tsx`
- Test: `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/FilterOptionDropdown.unit.tsx`

**Interfaces:**
- Produces: `FilterOptionDropdown` default export. Props: `{dropdownClass: 'currency'|'unit'; choiceClass: 'currency_choice'|'unit_choice'; hiddenInputName: 'currency_currency'|'metric_unit'; choices: {value: string; label: string}[]; selected: string; label: string}`. Renders a fragment: `.AknFilterChoice-<dropdownClass>` > `.AknDropdown.<dropdownClass>` (button highlight = selected choice's label; menu of `.AknDropdown-menuLink` with `span.label.<choiceClass>[data-value]`, `--active active` on selected) FOLLOWED BY a sibling `input.name_input[type=hidden][name=<hiddenInputName>][value=selected][readOnly]`.

- [ ] **Step 1: Write the failing test**

```tsx
import React from 'react';
import {render} from '@testing-library/react';
import FilterOptionDropdown from '../../../Resources/public/js/datafilter/filter/FilterOptionDropdown';

const choices = [
  {value: 'USD', label: 'USD'},
  {value: 'EUR', label: 'EUR'},
];

describe('FilterOptionDropdown', () => {
  test('renders the AknDropdown with the variant class, the choices, active state, and the hidden input', () => {
    const {container} = render(
      <FilterOptionDropdown
        dropdownClass="currency"
        choiceClass="currency_choice"
        hiddenInputName="currency_currency"
        choices={choices}
        selected="EUR"
        label="Currency"
      />
    );

    expect(container.querySelector('.AknFilterChoice-currency')).not.toBeNull();
    expect(container.querySelector('.AknDropdown.currency [data-toggle="dropdown"]')).not.toBeNull();
    expect(container.querySelector('.AknActionButton-highlight')!.textContent).toBe('EUR');

    const links = container.querySelectorAll('.AknDropdown-menuLink');
    expect(links).toHaveLength(2);
    const usd = container.querySelector('.currency_choice[data-value="USD"]')!;
    const eur = container.querySelector('.currency_choice[data-value="EUR"]')!;
    expect(usd.textContent).toBe('USD');
    expect(eur.closest('.AknDropdown-menuLink')!.className).toContain('AknDropdown-menuLink--active active');
    expect(usd.closest('.AknDropdown-menuLink')!.className).not.toContain('active');

    const hidden = container.querySelector('input[type="hidden"][name="currency_currency"]') as HTMLInputElement;
    expect(hidden).not.toBeNull();
    expect(hidden.value).toBe('EUR');
    expect(hidden.className).toBe('name_input');
  });

  test('falls back to the raw selected value when it is not among the choices (metric before units load)', () => {
    const {container} = render(
      <FilterOptionDropdown
        dropdownClass="unit"
        choiceClass="unit_choice"
        hiddenInputName="metric_unit"
        choices={[{value: 'GRAM', label: 'Gram'}]}
        selected="KILOGRAM"
        label="Unit"
      />
    );
    expect(container.querySelector('.AknActionButton-highlight')!.textContent).toBe('KILOGRAM');
    expect(container.querySelector('.AknDropdown-menu.unit')).toBeNull(); // menu has no variant class (matches price)
  });
});
```

- [ ] **Step 2: Run test to verify it fails** — CI only (do not run Jest locally). Expected: FAIL, module not found.

- [ ] **Step 3: Write the implementation**

```tsx
import React from 'react';

type Choice = {value: string; label: string};

type Props = {
  dropdownClass: 'currency' | 'unit';
  choiceClass: 'currency_choice' | 'unit_choice';
  hiddenInputName: 'currency_currency' | 'metric_unit';
  choices: Choice[];
  selected: string;
  label: string;
};

/**
 * Generic native AknDropdown for the currency (price) / unit (metric) option of a number filter
 * (C1 Wave 5). A parametrized sibling of `OperatorDropdown` (which stays operator-specific, untouched).
 *
 * React owns the *active* state (the `--active`/`active` class + the highlight label) and the hidden
 * input value; the Bootstrap `data-toggle` plugin owns the menu open/close. Rendered INLINE (never
 * portaled): the Behat decorator clicks the `[data-toggle="dropdown"]` and walks
 * `getClosest(…, 'AknDropdown')` to match a `.<choiceClass>` by text — a portal would break that walk.
 *
 * The hidden `input[name=…]` is a sibling of the `.AknFilterChoice-<variant>` block (both live inside
 * the criteria's `.AknFilterChoice-inputContainer`), mirroring `price-filter.html`/`metric-filter.html`.
 */
const FilterOptionDropdown = ({dropdownClass, choiceClass, hiddenInputName, choices, selected, label}: Props) => {
  const selectedChoice = choices.find(choice => choice.value === selected);
  const selectedLabel = selectedChoice ? selectedChoice.label : selected;

  return (
    <>
      <div className={`AknFilterChoice-${dropdownClass}`}>
        <div className={`AknDropdown ${dropdownClass}`}>
          <div className="AknActionButton AknActionButton--withoutBorder" data-toggle="dropdown">
            <span className="AknActionButton-highlight">{selectedLabel}</span>
            <span className="AknActionButton-caret" />
          </div>
          <div className="AknDropdown-menu">
            <div className="AknDropdown-menuTitle">{label}</div>
            {choices.map(choice => (
              <div
                key={choice.value}
                className={`AknDropdown-menuLink${
                  selected === choice.value ? ' AknDropdown-menuLink--active active' : ''
                }`}
              >
                <span className={`label ${choiceClass}`} data-value={choice.value}>
                  {choice.label}
                </span>
              </div>
            ))}
          </div>
        </div>
      </div>
      <input className="name_input" type="hidden" name={hiddenInputName} value={selected} readOnly />
    </>
  );
};

export default FilterOptionDropdown;
```

- [ ] **Step 4: Add the test to the Stryker allowlist**

In `tests/front/unit/jest/stryker.jest.js`, in the `testMatch` array (near the other DataGrid `*.unit.tsx` entries, ~line 175), add:
```js
    '<rootDir>/src/Oro/Bundle/PimDataGridBundle/tests/front/unit/FilterOptionDropdown.unit.tsx',
```

- [ ] **Step 5: Commit**

```bash
git add src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/filter/FilterOptionDropdown.tsx \
        src/Oro/Bundle/PimDataGridBundle/tests/front/unit/FilterOptionDropdown.unit.tsx \
        tests/front/unit/jest/stryker.jest.js
git commit -m "feat(datagrid): FilterOptionDropdown — generic currency/unit AknDropdown (C1 Wave 5)"
```

---

### Task 2: `NumberUnitFilterCriteria.tsx` — shared price/metric criteria popup

**Files:**
- Create: `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/filter/NumberUnitFilterCriteria.tsx`
- Test: `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/NumberUnitFilterCriteria.unit.tsx`

**Interfaces:**
- Consumes: `FilterOptionDropdown` (Task 1); `OperatorDropdown` + `useFilterPopupPosition` (existing, unchanged).
- Produces: `NumberUnitFilterCriteria` default export. Props = ChoiceFilterCriteria's minus `emptyChoice` PLUS the option props: `{showLabel, label, criteriaHint, canDisable, updateLabel, isOpen, operatorChoices, selectedOperator, operatorLabel, variantClass: 'currencyfilter'|'unitfilter', optionDropdownClass: 'currency'|'unit', optionChoiceClass: 'currency_choice'|'unit_choice', optionHiddenInputName: 'currency_currency'|'metric_unit', optionChoices: {value,label}[], selectedOption: string, optionLabel: string}`. Renders the chip (`.filter-criteria-selector`) + popup (`.filter-criteria.dropdown-menu` > `.AknFilterChoice.<variantClass>.choicefilter`): header (title + always-shown `OperatorDropdown`), `.AknFilterChoice-inputContainer` (memoized `input[name=value].AknTextField.select-field` + `FilterOptionDropdown`), `.AknFilterChoice-button` (`.filter-update`), and the `disable-filter` when `canDisable`.

- [ ] **Step 1: Write the failing test**

```tsx
import React from 'react';
import {render} from '@testing-library/react';
import NumberUnitFilterCriteria from '../../../Resources/public/js/datafilter/filter/NumberUnitFilterCriteria';

jest.mock('../../../Resources/public/js/datafilter/filter/useFilterPopupPosition', () => ({
  useFilterPopupPosition: jest.fn(),
}));

const baseProps = {
  showLabel: true,
  label: 'Price',
  criteriaHint: 'All',
  canDisable: true,
  updateLabel: 'Update',
  isOpen: false,
  operatorChoices: {'1': '=', '2': '>'},
  selectedOperator: '1',
  operatorLabel: 'Operator',
  variantClass: 'currencyfilter' as const,
  optionDropdownClass: 'currency' as const,
  optionChoiceClass: 'currency_choice' as const,
  optionHiddenInputName: 'currency_currency' as const,
  optionChoices: [{value: 'USD', label: 'USD'}],
  selectedOption: 'USD',
  optionLabel: 'Currency',
};

describe('NumberUnitFilterCriteria', () => {
  test('renders the chip, the variant wrapper, operator dropdown, value input, option dropdown and update button', () => {
    const {container} = render(<NumberUnitFilterCriteria {...baseProps} />);

    expect(container.querySelector('.filter-criteria-selector .filter-criteria-hint')!.textContent).toBe('All');
    expect(container.querySelector('.AknFilterChoice.currencyfilter.choicefilter')).not.toBeNull();
    expect(container.querySelector('.AknFilterChoice-header .AknDropdown.operator')).not.toBeNull();

    const inputContainer = container.querySelector('.AknFilterChoice-inputContainer')!;
    expect(inputContainer.querySelector('input[name="value"].AknTextField.select-field')).not.toBeNull();
    expect(inputContainer.querySelector('.AknFilterChoice-currency .AknDropdown.currency')).not.toBeNull();
    expect(inputContainer.querySelector('input[type="hidden"][name="currency_currency"]')).not.toBeNull();

    expect(container.querySelector('.AknFilterChoice-button .filter-update')!.textContent).toBe('Update');
    expect(container.querySelector('.disable-filter')).not.toBeNull();
  });

  test('omits the label span and the disable-filter when showLabel/canDisable are false', () => {
    const {container} = render(<NumberUnitFilterCriteria {...baseProps} showLabel={false} canDisable={false} />);
    expect(container.querySelector('.AknFilterBox-filterLabel')).toBeNull();
    expect(container.querySelector('.disable-filter')).toBeNull();
  });
});
```

- [ ] **Step 2: Run test to verify it fails** — CI only. Expected: FAIL, module not found.

- [ ] **Step 3: Write the implementation**

```tsx
import React, {useRef} from 'react';
import {useFilterPopupPosition} from './useFilterPopupPosition';
import OperatorDropdown from './OperatorDropdown';
import FilterOptionDropdown from './FilterOptionDropdown';

type Choice = {value: string; label: string};

type Props = {
  showLabel: boolean;
  label: string;
  criteriaHint: string;
  canDisable: boolean;
  updateLabel: string;
  isOpen: boolean;
  operatorChoices: Record<string, string>;
  selectedOperator: string;
  operatorLabel: string;
  variantClass: 'currencyfilter' | 'unitfilter';
  optionDropdownClass: 'currency' | 'unit';
  optionChoiceClass: 'currency_choice' | 'unit_choice';
  optionHiddenInputName: 'currency_currency' | 'metric_unit';
  optionChoices: Choice[];
  selectedOption: string;
  optionLabel: string;
};

/**
 * The value input of a price/metric filter, isolated behind `React.memo` (always-equal comparator) so
 * it renders ONCE. The number input is uncontrolled (no value/onChange) — jQuery `_get/_setInputValue`
 * own the value path, and never re-reconciling mirrors the legacy underscore template. Bare `<input>`
 * (no wrapping `<div>`), matching `price-filter.html`/`metric-filter.html` (unlike ChoiceFilterCriteria,
 * which wraps it to shield the jQuery Select2 sibling — price/metric have no Select2).
 */
const ValueField = React.memo(
  () => <input type="text" name="value" className="AknTextField select-field" />,
  () => true
);
ValueField.displayName = 'ValueField';

/**
 * Presentational chip + criteria popup shared by the price and metric datagrid filters (C1 Wave 5).
 *
 * Reproduces the legacy `price-filter.html` / `metric-filter.html` popup body: an always-shown operator
 * `AknDropdown` (in `.AknFilterChoice-header`), then `.AknFilterChoice-inputContainer` holding the value
 * input + the currency/unit `FilterOptionDropdown` + its hidden input, then the update button. React owns
 * popup POSITION (`useFilterPopupPosition`) and the operator/option ACTIVE state; jQuery keeps owning
 * popup visibility, the value path, and the input-container show/hide for empty operators.
 */
const NumberUnitFilterCriteria = ({
  showLabel,
  label,
  criteriaHint,
  canDisable,
  updateLabel,
  isOpen,
  operatorChoices,
  selectedOperator,
  operatorLabel,
  variantClass,
  optionDropdownClass,
  optionChoiceClass,
  optionHiddenInputName,
  optionChoices,
  selectedOption,
  optionLabel,
}: Props) => {
  const popupRef = useRef<HTMLDivElement>(null);
  useFilterPopupPosition(popupRef, isOpen);

  return (
    <>
      <div className="AknFilterBox-filter filter-criteria-selector oro-drop-opener">
        {showLabel && <span className="AknFilterBox-filterLabel">{label}</span>}
        <span className="AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited filter-criteria-hint">
          {criteriaHint}
        </span>
        <span className="AknFilterBox-filterCaret" />
      </div>
      <div ref={popupRef} className="filter-criteria dropdown-menu">
        <div className={`AknFilterChoice ${variantClass} choicefilter`}>
          <div className="AknFilterChoice-header">
            <div className="AknFilterChoice-title">{label}</div>
            <OperatorDropdown
              operatorChoices={operatorChoices}
              selectedOperator={selectedOperator}
              operatorLabel={operatorLabel}
            />
          </div>
          <div className="AknFilterChoice-inputContainer">
            <ValueField />
            <FilterOptionDropdown
              dropdownClass={optionDropdownClass}
              choiceClass={optionChoiceClass}
              hiddenInputName={optionHiddenInputName}
              choices={optionChoices}
              selected={selectedOption}
              label={optionLabel}
            />
          </div>
          <div className="AknFilterChoice-button">
            <button type="button" className="AknButton AknButton--apply filter-update">
              {updateLabel}
            </button>
          </div>
        </div>
      </div>
      {canDisable && <div className="AknFilterBox-disableFilter AknIconButton AknIconButton--remove disable-filter" />}
    </>
  );
};

export default NumberUnitFilterCriteria;
```

- [ ] **Step 4: Add the test to the Stryker allowlist** — add to `tests/front/unit/jest/stryker.jest.js` `testMatch`:
```js
    '<rootDir>/src/Oro/Bundle/PimDataGridBundle/tests/front/unit/NumberUnitFilterCriteria.unit.tsx',
```

- [ ] **Step 5: Commit**

```bash
git add src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/filter/NumberUnitFilterCriteria.tsx \
        src/Oro/Bundle/PimDataGridBundle/tests/front/unit/NumberUnitFilterCriteria.unit.tsx \
        tests/front/unit/jest/stryker.jest.js
git commit -m "feat(datagrid): NumberUnitFilterCriteria — shared price/metric criteria popup (C1 Wave 5)"
```

---

### Task 3: `price-filter-react.ts` bridge + registry re-point

**Files:**
- Create: `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/filter/price-filter-react.ts`
- Create: `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/price-filter-react.unit.tsx`
- Modify: `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/FilterTypeRegistry.ts` (price → `oro/datafilter/price-filter-react`)
- Modify: `src/Oro/Bundle/PimDataGridBundle/tests/front/unit/FilterTypeRegistry.unit.ts` (price assertion)
- Modify: `src/Oro/Bundle/PimDataGridBundle/Resources/config/requirejs.yml` (alias)
- Modify: `tests/front/unit/jest/stryker.jest.js` (allowlist)

**Interfaces:**
- Consumes: `number-filter-react` (base, provides render/_renderReact/_showCriteria/_onSelectOperator), `NumberUnitFilterCriteria` (Task 2). The legacy `price-filter.js` `_readDOMValue`/`_writeDOMValue`/`_getCriteriaHint`/`_onSelectCurrency`/`_firstCurrency` are the reference to copy.
- Produces: default export `NumberFilterReact.extend({...})`, re-adding the currency layer; state field `_selectedCurrency` (source of truth). Overrides `_renderReact` to render `NumberUnitFilterCriteria` with `variantClass:'currencyfilter'`, currency option props.

- [ ] **Step 1: Write the failing test** (mirror `select2-rest-choice-filter-react.unit.tsx` — mock the base + NumberUnitFilterCriteria, assert the currency overrides)

```tsx
// Mock the React base so the bridge's inherited render/operator machinery is stubbed.
jest.mock('oro/datafilter/number-filter-react', () => {
  function NumberFilterReact(this: any) {
    this.el = document.createElement('div');
    this.label = 'Price';
    this.currencies = {USD: 'USD', EUR: 'EUR'};
    this._selectedOperator = '1';
    this._value = {type: '1', value: '10', currency: 'EUR'};
    this.criteriaValueSelectors = {currency: 'input[name="currency_currency"]', value: 'input[name="value"]'};
    const $el: any = {val: jest.fn(() => 'EUR')};
    this.$ = jest.fn(() => $el);
    this._$el = $el;
  }
  const proto = (NumberFilterReact as any).prototype;
  proto._renderReact = jest.fn();
  proto._getOperatorChoices = jest.fn(() => ({'1': '=', '2': '>'}));
  proto._getDisplayValue = function (this: any) { return this._value; };
  proto.getValue = function (this: any) { return this._value; };
  proto._readDOMValue = jest.fn(() => ({type: '1', value: '10'}));
  proto._writeDOMValue = jest.fn();
  proto._getInputValue = jest.fn(() => 'EUR');
  proto._highlightDropdown = jest.fn();
  function backboneExtend(this: any, o: any) {
    const P = this; function S(this: any) { P.apply(this, arguments); }
    S.prototype = Object.create(P.prototype); Object.assign(S.prototype, o);
    (S as any).extend = backboneExtend; return S;
  }
  (NumberFilterReact as any).extend = backboneExtend;
  return NumberFilterReact;
}, {virtual: true});

jest.mock('oro/translator', () => (k: string) => k, {virtual: true});
jest.mock('../../../Resources/public/js/datafilter/filter/NumberUnitFilterCriteria', () => {
  const React = require('react');
  return {__esModule: true, default: (props: any) =>
    React.createElement('div', {'data-variant': props.variantClass, 'data-selected-option': props.selectedOption})};
});

import Bridge from '../../../Resources/public/js/datafilter/filter/price-filter-react';

beforeEach(() => jest.clearAllMocks());

describe('price-filter-react', () => {
  test('_onSelectCurrency records the currency in state and re-renders', () => {
    const filter: any = new (Bridge as any)();
    const e = {currentTarget: null, preventDefault: jest.fn()};
    // stub jQuery target lookup: $(e.currentTarget).find('.currency_choice').attr('data-value')
    const attr = jest.fn(() => 'USD');
    (filter as any).$ = jest.fn(() => ({find: () => ({attr})}));
    const spy = jest.spyOn(filter, '_renderReact');
    filter._onSelectCurrency(e);
    expect(filter._selectedCurrency).toBe('USD');
    expect(spy).toHaveBeenCalled();
    expect(e.preventDefault).toHaveBeenCalled();
  });

  test('_readDOMValue augments the inherited number value with the currency from state', () => {
    const filter: any = new (Bridge as any)();
    filter._selectedCurrency = 'EUR';
    expect(filter._readDOMValue()).toEqual({type: '1', value: '10', currency: 'EUR'});
  });
});
```

- [ ] **Step 2: Run test to verify it fails** — CI only. Expected: FAIL, module not found.

- [ ] **Step 3: Write the implementation**

```ts
import $ from 'jquery';
import _ from 'underscore';
import React from 'react';
import __ from 'oro/translator';
import NumberFilterReact from 'oro/datafilter/number-filter-react';
import NumberUnitFilterCriteria from './NumberUnitFilterCriteria';
import ReactDOM from 'react-dom';

/**
 * React inner-render of the `price` datagrid filter (C1 Wave 5). Extends the React `number-filter-react`
 * base to reuse ALL its React machinery (render, operator AknDropdown, popup positioning, show/hide) and
 * re-adds ONLY the currency layer copied from the legacy `price-filter.js` (`_onSelectCurrency`, currency
 * in `_readDOMValue`, `_renderReact` renders the shared `NumberUnitFilterCriteria`). `this._selectedCurrency`
 * is the single source of truth (read directly, not via the hidden DOM input — anti React/jQuery desync).
 *
 * Added ALONGSIDE `price-filter.js`; only the `price` FilterTypeRegistry alias is re-pointed.
 */
export default NumberFilterReact.extend({
  events: {
    'keyup input': '_onReadCriteriaInputKey',
    'keydown [type="text"]': '_preventEnterProcessing',
    'click .filter-update': '_onClickUpdateCriteria',
    'click .filter-criteria-selector': '_onClickCriteriaSelector',
    'click .operator .AknDropdown-menuLink': '_onSelectOperator',
    'click .currency .AknDropdown-menuLink': '_onSelectCurrency',
    'click .disable-filter': '_onClickDisableFilter',
  },

  /**
   * {@inheritdoc}
   *
   * Render the shared price/metric criteria (operator + value + currency dropdown) into `this.el`.
   */
  _renderReact: function () {
    if (_.isUndefined(this._selectedCurrency)) {
      this._selectedCurrency = this._getDisplayValue().currency || _.first(_.keys(this.currencies));
    }

    ReactDOM.render(
      React.createElement(NumberUnitFilterCriteria, {
        showLabel: this.showLabel,
        label: this.label,
        criteriaHint: this._getCriteriaHint(),
        canDisable: this.canDisable,
        updateLabel: __('pim_common.update'),
        isOpen: this._criteriaOpen === true,
        operatorChoices: this._getOperatorChoices(),
        selectedOperator: '' + this._selectedOperator,
        operatorLabel: __('pim_common.operator'),
        variantClass: 'currencyfilter',
        optionDropdownClass: 'currency',
        optionChoiceClass: 'currency_choice',
        optionHiddenInputName: 'currency_currency',
        optionChoices: _.keys(this.currencies).map((currency: string) => ({value: currency, label: currency})),
        selectedOption: this._selectedCurrency,
        optionLabel: __('pim_datagrid.filters.price_filter.label'),
      }),
      this.el
    );
  },

  /**
   * {@inheritdoc}
   *
   * Record the clicked currency in React state (source of truth) then re-render.
   */
  _onSelectCurrency: function (e: JQuery.TriggeredEvent) {
    this._selectedCurrency = $(e.currentTarget).find('.currency_choice').attr('data-value');
    this._renderReact();

    e.preventDefault();
  },

  /**
   * {@inheritdoc}
   *
   * Augment the inherited number read with the currency from state (not the DOM hidden input).
   */
  _readDOMValue: function () {
    const value = NumberFilterReact.prototype._readDOMValue.apply(this, arguments);
    value.currency = this._selectedCurrency;

    return value;
  },

  /**
   * {@inheritdoc}
   *
   * Keep the legacy hint ("operator value currency") reading the model value.
   */
  _getCriteriaHint: function () {
    const value = this._getDisplayValue();
    if (_.contains(['empty', 'not empty'], value.type)) {
      return this._getChoiceOption(value.type).label;
    }
    if (!value.value) {
      return this.placeholder;
    }

    return this._getChoiceOption(value.type).label + ' ' + value.value + ' ' + value.currency;
  },
});
```

- [ ] **Step 4: Re-point the registry + alias + allowlist + registry test**

`FilterTypeRegistry.ts` — change the `price` line:
```ts
  // C1 Wave 5: `price` renders via React (number + a currency AknDropdown; shared NumberUnitFilterCriteria).
  price: 'oro/datafilter/price-filter-react',
```
`FilterTypeRegistry.unit.ts` — change the `price` assertion:
```ts
  test('price', () => expect(FILTER_MODULE_IDS['price']).toBe('oro/datafilter/price-filter-react'));
```
Also update the `resolveFilterModuleId('price')` direct-type test if present (search for `product_scope → product_scope-filter` neighbourhood; there is a `price → price-filter` test) to `'oro/datafilter/price-filter-react'`.
`requirejs.yml` — add under the datafilter aliases block (next to the other `*-filter-react`):
```yaml
        oro/datafilter/price-filter-react:          pimdatagrid/js/datafilter/filter/price-filter-react
```
`stryker.jest.js` `testMatch` — add:
```js
    '<rootDir>/src/Oro/Bundle/PimDataGridBundle/tests/front/unit/price-filter-react.unit.tsx',
```

- [ ] **Step 5: Commit**

```bash
git add src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/filter/price-filter-react.ts \
        src/Oro/Bundle/PimDataGridBundle/tests/front/unit/price-filter-react.unit.tsx \
        src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/FilterTypeRegistry.ts \
        src/Oro/Bundle/PimDataGridBundle/tests/front/unit/FilterTypeRegistry.unit.ts \
        src/Oro/Bundle/PimDataGridBundle/Resources/config/requirejs.yml \
        tests/front/unit/jest/stryker.jest.js
git commit -m "feat(datagrid): C1 Wave 5 — price filter renders via React"
```

---

### Task 4: `metric-filter-react.ts` bridge + registry re-point

**Files:** (same shape as Task 3, for metric)
- Create: `.../filter/metric-filter-react.ts`
- Create: `.../tests/front/unit/metric-filter-react.unit.tsx`
- Modify: `FilterTypeRegistry.ts`, `FilterTypeRegistry.unit.ts`, `requirejs.yml`, `stryker.jest.js`

**Interfaces:**
- Consumes: `number-filter-react` base, `NumberUnitFilterCriteria` (Task 2), `pim/i18n`, `pim/user-context`, `pim/fetcher-registry`. Reference: legacy `metric-filter.js`.
- Produces: default export bridge; state field `_selectedUnit`; async `measurementFamily` loaded in `initialize`; `_renderReact` renders `NumberUnitFilterCriteria` with `variantClass:'unitfilter'`, unit option props built from `measurementFamily.units` with i18n labels.

- [ ] **Step 1: Write the failing test**

```tsx
jest.mock('oro/datafilter/number-filter-react', () => {
  function NumberFilterReact(this: any) {
    this.el = document.createElement('div');
    this.label = 'Weight';
    this.family = 'weight';
    this._selectedOperator = '1';
    this._value = {type: '1', value: '10', unit: 'KILOGRAM'};
    this.measurementFamily = {code: 'weight', units: [
      {code: 'GRAM', labels: {en_US: 'Gram'}},
      {code: 'KILOGRAM', labels: {en_US: 'Kilogram'}},
    ]};
    this.criteriaValueSelectors = {unit: 'input[name="metric_unit"]', value: 'input[name="value"]'};
  }
  const proto = (NumberFilterReact as any).prototype;
  proto.initialize = jest.fn();
  proto._renderReact = jest.fn();
  proto._getOperatorChoices = jest.fn(() => ({'1': '=', '2': '>'}));
  proto._getDisplayValue = function (this: any) { return this._value; };
  proto.getValue = function (this: any) { return this._value; };
  proto._readDOMValue = jest.fn(() => ({type: '1', value: '10'}));
  proto.render = jest.fn();
  function backboneExtend(this: any, o: any) {
    const P = this; function S(this: any) { P.apply(this, arguments); }
    S.prototype = Object.create(P.prototype); Object.assign(S.prototype, o);
    (S as any).extend = backboneExtend; return S;
  }
  (NumberFilterReact as any).extend = backboneExtend;
  return NumberFilterReact;
}, {virtual: true});

jest.mock('oro/translator', () => (k: string) => k, {virtual: true});
jest.mock('pim/i18n', () => ({getLabel: (labels: any, locale: string, code: string) => labels[locale] || code}), {virtual: true});
jest.mock('pim/user-context', () => ({get: () => 'en_US'}), {virtual: true});
jest.mock('pim/fetcher-registry', () => ({getFetcher: () => ({fetchAll: () => Promise.resolve([])})}), {virtual: true});
jest.mock('../../../Resources/public/js/datafilter/filter/NumberUnitFilterCriteria', () => {
  const React = require('react');
  return {__esModule: true, default: (props: any) =>
    React.createElement('div', {'data-variant': props.variantClass, 'data-selected-option': props.selectedOption,
      'data-options': JSON.stringify(props.optionChoices)})};
});

import Bridge from '../../../Resources/public/js/datafilter/filter/metric-filter-react';

beforeEach(() => jest.clearAllMocks());

describe('metric-filter-react', () => {
  test('_onSelectUnit records the unit in state and re-renders', () => {
    const filter: any = new (Bridge as any)();
    const attr = jest.fn(() => 'GRAM');
    (filter as any).$ = jest.fn(() => ({find: () => ({attr})}));
    const spy = jest.spyOn(filter, '_renderReact');
    const e = {currentTarget: null, preventDefault: jest.fn()};
    filter._onSelectUnit(e);
    expect(filter._selectedUnit).toBe('GRAM');
    expect(spy).toHaveBeenCalled();
    expect(e.preventDefault).toHaveBeenCalled();
  });

  test('_readDOMValue augments the inherited number value with the unit from state', () => {
    const filter: any = new (Bridge as any)();
    filter._selectedUnit = 'KILOGRAM';
    expect(filter._readDOMValue()).toEqual({type: '1', value: '10', unit: 'KILOGRAM'});
  });
});
```

- [ ] **Step 2: Run test to verify it fails** — CI only.

- [ ] **Step 3: Write the implementation**

```ts
import $ from 'jquery';
import _ from 'underscore';
import React from 'react';
import ReactDOM from 'react-dom';
import __ from 'oro/translator';
import * as i18n from 'pim/i18n';
import UserContext from 'pim/user-context';
import NumberFilterReact from 'oro/datafilter/number-filter-react';
import NumberUnitFilterCriteria from './NumberUnitFilterCriteria';

/**
 * React inner-render of the `metric` datagrid filter (C1 Wave 5). Sibling of `price-filter-react` — same
 * shape, but the option is the measurement UNIT (i18n labels) and the units are fetched async in
 * `initialize` (kept from the legacy `metric-filter.js`). `this._selectedUnit` is the source of truth.
 *
 * Added ALONGSIDE `metric-filter.js`; only the `metric` FilterTypeRegistry alias is re-pointed.
 */
export default NumberFilterReact.extend({
  events: {
    'keyup input': '_onReadCriteriaInputKey',
    'keydown [type="text"]': '_preventEnterProcessing',
    'click .filter-update': '_onClickUpdateCriteria',
    'click .filter-criteria-selector': '_onClickCriteriaSelector',
    'click .operator .AknDropdown-menuLink': '_onSelectOperator',
    'click .unit .AknDropdown-menuLink': '_onSelectUnit',
    'click .disable-filter': '_onClickDisableFilter',
  },

  /**
   * {@inheritdoc}
   *
   * The React base does not render until units are loaded (`_renderReact` guards on measurementFamily).
   */
  _renderReact: function () {
    if (!this.measurementFamily) return;

    const locale = UserContext.get('uiLocale');
    if (_.isUndefined(this._selectedUnit)) {
      this._selectedUnit = this._getDisplayValue().unit || (this.measurementFamily.units[0] || {}).code;
    }

    ReactDOM.render(
      React.createElement(NumberUnitFilterCriteria, {
        showLabel: this.showLabel,
        label: this.label,
        criteriaHint: this._getCriteriaHint(),
        canDisable: this.canDisable,
        updateLabel: __('pim_common.update'),
        isOpen: this._criteriaOpen === true,
        operatorChoices: this._getOperatorChoices(),
        selectedOperator: '' + this._selectedOperator,
        operatorLabel: __('pim_common.operator'),
        variantClass: 'unitfilter',
        optionDropdownClass: 'unit',
        optionChoiceClass: 'unit_choice',
        optionHiddenInputName: 'metric_unit',
        optionChoices: this.measurementFamily.units.map((unit: {code: string; labels: Record<string, string>}) => ({
          value: unit.code,
          label: i18n.getLabel(unit.labels, locale, unit.code),
        })),
        selectedOption: this._selectedUnit,
        optionLabel: __('pim_datagrid.filters.metric_filter.label'),
      }),
      this.el
    );
  },

  /**
   * {@inheritdoc}
   */
  _onSelectUnit: function (e: JQuery.TriggeredEvent) {
    this._selectedUnit = $(e.currentTarget).find('.unit_choice').attr('data-value');
    this._renderReact();

    e.preventDefault();
  },

  /**
   * {@inheritdoc}
   */
  _readDOMValue: function () {
    const value = NumberFilterReact.prototype._readDOMValue.apply(this, arguments);
    value.unit = this._selectedUnit;

    return value;
  },

  /**
   * {@inheritdoc}
   *
   * Legacy hint: "operator value i18n(unit)".
   */
  _getCriteriaHint: function () {
    const value = this._getDisplayValue();
    if (_.contains(['empty', 'not empty'], value.type)) {
      return this._getChoiceOption(value.type).label;
    }
    if (!value.value || !this.measurementFamily) {
      return this.placeholder;
    }
    const unit = this.measurementFamily.units.find((u: {code: string}) => u.code === value.unit);

    return `${this._getChoiceOption(value.type).label} ${value.value} ${i18n.getLabel(
      unit.labels,
      UserContext.get('uiLocale'),
      value.unit
    )}`;
  },
});
```

NOTE: the async units fetch (`FetcherRegistry.getFetcher('measure').fetchAll().then(... this.measurementFamily = ...; this.render())`) is inherited from the legacy `metric-filter.js` behaviour — but the bridge extends `number-filter-react`, NOT the legacy metric filter, so `initialize` does NOT run that fetch. **Re-add it in the bridge's `initialize`** (copy from `metric-filter.js:26-45`): call `NumberFilterReact.prototype.initialize.apply(this, arguments)`, set `this.emptyValue`, then `FetcherRegistry.getFetcher('measure').fetchAll().then(measures => { this.measurementFamily = measures.find(f => f.code === this.family); this.render(); })`. Add `import FetcherRegistry from 'pim/fetcher-registry'`. (The Task-4 test mocks `fetchAll` to resolve `[]`; extend the test with an `initialize` case that asserts `measurementFamily` is set after the promise resolves.)

- [ ] **Step 4: Re-point the registry + alias + allowlist + registry test** (metric)

`FilterTypeRegistry.ts`: `metric: 'oro/datafilter/metric-filter-react'`.
`FilterTypeRegistry.unit.ts`: `expect(FILTER_MODULE_IDS['metric']).toBe('oro/datafilter/metric-filter-react')` (+ the `resolveFilterModuleId('metric')` test if present).
`requirejs.yml`: `oro/datafilter/metric-filter-react: pimdatagrid/js/datafilter/filter/metric-filter-react`.
`stryker.jest.js` `testMatch`: add `metric-filter-react.unit.tsx`.

- [ ] **Step 5: Commit**

```bash
git add src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/filter/metric-filter-react.ts \
        src/Oro/Bundle/PimDataGridBundle/tests/front/unit/metric-filter-react.unit.tsx \
        src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/FilterTypeRegistry.ts \
        src/Oro/Bundle/PimDataGridBundle/tests/front/unit/FilterTypeRegistry.unit.ts \
        src/Oro/Bundle/PimDataGridBundle/Resources/config/requirejs.yml \
        tests/front/unit/jest/stryker.jest.js
git commit -m "feat(datagrid): C1 Wave 5 — metric filter renders via React"
```

---

## Post-implementation

- Push the branch, open the PR, arm auto-merge (`gh pr merge --auto --squash`).
- Watch CI: `test-front-unit`, `mutation-testing-front` (MSI ≥ 50 on the 4 new files), 10 `test-behat` shards (byte-identity of the price/metric filters — the `edit_common_attributes`/product-grid feature exercises them). Known infra flakes: rerun `test-playwright` browser-install / behat image-upload; do not hammer.
- If a behat shard fails on price/metric specifically, suspect the hidden-input ownership (open item 1) or `_getDisplayValueOrDefault` defaulting — reproduce via the live behat env before shipping.
