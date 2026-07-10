# Price & Metric datagrid filters → React — Design

**Goal:** Migrate the `price` and `metric` datagrid filters from legacy Backbone/underscore rendering to React, following the established C1 Wave-4/5 Strangler-Fig bridge pattern, with shared (DRY) components since the two filters are structurally identical (a number filter + one native `AknDropdown` — currency for price, unit for metric).

**Non-goals:** No behaviour change (byte-identical markup preserves the existing Behat contract). The legacy `price-filter.js` / `metric-filter.js` stay in place (only the `FilterTypeRegistry` aliases are re-pointed). No change to `OperatorDropdown` or `ChoiceFilterCriteria` (isolation).

## Context

- Strangler Fig: each filter type ships a React `*-filter-react.ts` bridge added ALONGSIDE the legacy `.js`; only the `FilterTypeRegistry` alias is re-pointed. Markup stays byte-identical so the existing legacy Behat suite validates the swap.
- Already migrated to React: `text`, `choice`, `number`, `identifier`, `uuid`, `parent`, `date`, `datetime`, `select2-choice`, `select2-rest-choice`.
- `choice-filter-react.ts` is the React base (extends legacy `ChoiceFilter`): owns the React render, the operator `AknDropdown` (via `OperatorDropdown`), the popup-position hook, and the memoized value `<input name="value">`. `number-filter-react.ts` extends it and only re-adds the number NaN guard (`_onClickUpdateCriteria`).
- Legacy `price-filter.js` (197 loc) and `metric-filter.js` (257 loc) both `NumberFilter.extend` (and `NumberFilter` is `ChoiceFilter.extend`). Each adds ONE native `AknDropdown` (currency / unit) + a hidden input, and extends `_readDOMValue`/`_writeDOMValue` to carry the extra field.

## Markup contract (verified against the legacy templates)

Both templates wrap in `.AknFilterChoice.<variant>filter.choicefilter` (`currencyfilter` / `unitfilter`) and contain, in order:

1. `.AknDropdown.operator` — **byte-identical to `OperatorDropdown`** (`[data-toggle="dropdown"]` button + `.AknDropdown-menu` > `.AknDropdown-menuLink` > `span.label.operator_choice[data-value]`). Reuse `OperatorDropdown` unchanged.
2. `input[name="value"].AknTextField.select-field` — the number value input (plain input; not Select2 for the `=`/`>`/`<`/`between` operators).
3. The option dropdown:
   - price: `.AknFilterChoice-currency` > `.AknDropdown.currency` > button (highlight = `selectedCurrency`) + `.AknDropdown-menu` > `.AknDropdown-menuLink` > `span.label.currency_choice[data-value=<currency>]` (display = the currency code) + `input[type=hidden][name="currency_currency"]`.
   - metric: `.AknFilterChoice-unit` > `.AknDropdown.unit` (menu also carries `.unit`) > button (highlight = i18n label of `selectedUnit`) + links `span.label.unit_choice[data-value=<unit.code>]` (display = i18n label) + `input[type=hidden][name="metric_unit"]`.

Asymmetry: price currencies are plain string codes (`value === label === code`); metric units are `{code, labels}` objects needing `i18n.getLabel(labels, locale, code)`. The generic dropdown takes `choices: {value, label}[]` so the bridge normalizes both.

## Architecture — 4 new files + registry re-point

### 1. `FilterOptionDropdown.tsx` (generic, new)
A parametrized copy of `OperatorDropdown` (does NOT modify `OperatorDropdown`). Props:
- `dropdownClass: 'currency' | 'unit'` — the `.AknDropdown` variant class (also applied to the menu for metric parity).
- `choiceClass: 'currency_choice' | 'unit_choice'` — the `span.label` class carrying `data-value`.
- `hiddenInputName: 'currency_currency' | 'metric_unit'` — the hidden `<input>` name.
- `choices: {value: string; label: string}[]`, `selected: string`, `label: string` (menu title).
Renders `.AknDropdown.<dropdownClass>` (button highlight = the selected choice's label) + menu of links (`--active active` on the selected) + the hidden input `value={selected}`. Inline (never portaled) — same reason as `OperatorDropdown` (Behat `getClosest` walk).

### 2. `NumberUnitFilterCriteria.tsx` (shared price+metric, new)
Composes the popup body: `OperatorDropdown` (reused) + the memoized `ValueField` (`<input name="value" class="AknTextField select-field">`, `React.memo(()=>true)` — same shielding as `ChoiceFilterCriteria`) + `FilterOptionDropdown` + the `.filter-update` button + disable-filter, all inside the `useFilterPopupPosition` popup. Props = the `ChoiceFilterCriteria` prop set (showLabel/label/criteriaHint/canDisable/updateLabel/isOpen/operatorChoices/selectedOperator/operatorLabel) PLUS the `FilterOptionDropdown` props (dropdownClass/choiceClass/hiddenInputName/optionChoices/selectedOption/optionLabel) and the wrapper variant class (`currencyfilter`/`unitfilter`).

### 3-4. `price-filter-react.ts` / `metric-filter-react.ts` (bridges, new)
`NumberFilterReact.extend({...})` — reuse the React operator machinery (render, `_renderReact`, `_showCriteria`/`_hideCriteria`, `_onSelectOperator`, popup) from the base and re-add ONLY the option layer, copied from the legacy `price-filter.js`/`metric-filter.js`:
- `events`: add `'click .currency .AknDropdown-menuLink': '_onSelectCurrency'` (price) / `'click .unit .AknDropdown-menuLink': '_onSelectUnit'` (metric).
- `_onSelectCurrency`/`_onSelectUnit`: read `data-value`, set `this._selectedCurrency`/`this._selectedUnit` (React state, single source of truth), `this._renderReact()`, `preventDefault`.
- `_readDOMValue`: extend the inherited number read to add `currency: this._selectedCurrency` / `unit: this._selectedUnit` (read from state, NOT the DOM hidden input — anti React/jQuery desync).
- `_writeDOMValue`: keep inheriting the legacy writer for the model→hidden-input sync where needed (or rely on React rendering the hidden input from state — see Open items).
- `_renderReact`: render `NumberUnitFilterCriteria` with the operator props + the normalized option props (price: `currencies.map(c => ({value:c, label:c}))`; metric: `measurementFamily.units.map(u => ({value:u.code, label:i18n.getLabel(u.labels, locale, u.code)}))`).
- metric only: keep the async units fetch in `initialize` (FetcherRegistry `measure`.fetchAll → set `this.measurementFamily` → `this.render()`), unchanged.
- Registry: re-point `price` and `metric` in `FilterTypeRegistry.ts` to the new modules; add `requirejs.yml` aliases; the extension-less alias for the AMD id.

## Data flow

Bridge state: `_selectedOperator` (inherited from the React base) + `_selectedCurrency`/`_selectedUnit` (new). Any dropdown click → set the corresponding state field → `_renderReact()` re-renders the whole popup from state (memoized `ValueField` keeps the number input stable). On `.filter-update`, `_readDOMValue()` returns `{type, value, currency|unit}` from state + the input, `setValue()` applies it, the grid reloads.

## Testing / CI

- Unit tests (`tests/front/unit/`): `FilterOptionDropdown.unit.tsx` (renders the variant class + choice class + hidden input + active state), `NumberUnitFilterCriteria.unit.tsx` (composes operator + value + option dropdown + button), `price-filter-react.unit.tsx` + `metric-filter-react.unit.tsx` (bridge overrides: `_onSelectCurrency`/`_onSelectUnit` set state + re-render, `_readDOMValue` carries currency/unit, metric async units). Mock the legacy base + FetcherRegistry.
- Add every new `*.unit.tsx` to the hand-maintained `stryker.jest.js` `testMatch` allowlist (else the changed `.ts`/`.tsx` mutate to MSI 0).
- `FilterTypeRegistry.unit.ts`: update the `price` and `metric` assertions (2 each).
- Behat: the 10 legacy shards validate byte-identity end-to-end. No new Behat.

## Risks / open items (resolve during implementation)

1. **Hidden input ownership.** The legacy `_readDOMValue` reads `input[name="currency_currency"]`/`[name="metric_unit"]`. The bridge instead reads `_selectedCurrency`/`_selectedUnit` from state (anti-desync), so the hidden input becomes display-only (React renders it from state for any consumer that still reads the DOM). Confirm no other code reads the hidden input directly.
2. **Base extension.** Extend `number-filter-react` (reuse React machinery). Verify the inherited `render` side-effects (`_toggleListSelection`/`_toggleInput`) are harmless for price/metric operators (they are not `'in'`, so the Select2 list is never toggled). If a side-effect misbehaves, fall back to extending the legacy `price-filter`/`metric-filter` and re-implementing the React overrides (the Select2-bridge pattern).
3. **metric i18n locale.** The unit label needs `UserContext.get('catalogLocale')` at render time; pass it into the bridge's option normalization.
