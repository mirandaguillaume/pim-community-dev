# Select-family datagrid filters → React (DSM `MultiSelectInput`) — Design

**Goal:** Replace the legacy `jquery.multiselect` jQuery-UI widget that powers the `select` / `multiselect` / `select-row` datagrid filters (and their downstream consumers) with the in-house React `akeneo-design-system` `SelectInput` / `MultiSelectInput` components, migrated **incrementally and CI-safely** via a staged, bi-markup Behat strategy.

**This spec covers sub-project #1 (the foundation slice):** the shared React component + the `select-filter-react` bridge base + the bi-markup Behat decorators + the first consumer (`select`) migrated end-to-end. Later consumers (`multiselect`, `select-row`, `product_scope`/`product_completeness`/`ajax-choice`, `filters-manager`) each get their own spec/plan/PR reusing this foundation, and a final PR deletes the dead legacy widget + the legacy Behat branch.

**Non-goals:**
- No behaviour change visible to the user beyond the widget's look becoming the DSM look (the filter's value semantics, the grid reload, the enabled/disabled lifecycle are unchanged).
- No migration of `multiselect` / `select-row` / `product_scope` / `product_completeness` / `ajax-choice` / `filters-manager` in this sub-project (they stay on the legacy jQuery widget; the bi-markup decorators keep them green).
- No deletion of `jquery.multiselect.js` / `jquery.multiselect.filter.js` / `multiselect-decorator.js` / the `.less`/`.css` in this sub-project — they are still used by the not-yet-migrated consumers. Deletion is the final roadmap PR.
- No new external dependency (no `react-select` / `downshift`): the DSM component already ships in-repo and is already imported by the datagrid bundle.

## Context

### Strangler Fig, and why this wave is different

Every prior C1 filter wave (`text`/`choice`/`number`/`identifier`/`uuid`/`parent`/`date`/`datetime`/`select2-choice`/`select2-rest-choice`/`price`/`metric`) shipped a React `*-filter-react.ts` bridge added ALONGSIDE the legacy `.js`, with only the `FilterTypeRegistry` alias re-pointed and byte-identical markup so the legacy Behat suite validated the swap. Each of those filters has a React-managed popup (operator dropdown + value input + update button), so React genuinely owns live UI.

The `select` family is structurally different: **there is no operator, no popup, no update button.** The entire live UI *is* a jQuery-UI widget (`jquery.multiselect`, wrapped by `oro/multiselect-decorator`): it consumes a seed `<select>`, hides it, and portals a `.ui-multiselect-menu.pimmultiselect` checkbox menu to `<body>`. A byte-identical "shield" migration would therefore leave React managing nothing (an inert seed `<select>` handed straight to jQuery) and would NOT remove the jQuery dependency. So instead of a shield, we **replace** the widget with a real controlled React component.

### The in-house component

`front-packages/akeneo-design-system` ships production, controlled React components:
- **`SelectInput`** — single select. `value: string | null`, `onChange: (v: string | null) => void` (when `clearable`, default `true`) or `(v: string) => void` (when `clearable: false`). Children are `<SelectInput.Option value="x">Label</SelectInput.Option>`. **Already stamps `data-testid={value}` on every rendered option** (SelectInput.tsx:435).
- **`MultiSelectInput`** — multi select. `value: string[]`, `onChange: (v: string[]) => void`. Children are `<MultiSelectInput.Option value="x">Label</MultiSelectInput.Option>`; `Option = ({children, ...rest}) => <span {...rest}>{children}</span>`, so a `data-testid` prop lands on the option `<span>`.

Both take `placeholder`, `emptyResultLabel`, `openLabel`, `removeLabel` (multi only); both spread `...rest` onto their container; both render the option list inside a `common/Overlay` that **`createPortal`s to `document.body`** (only one overlay open at a time). The datagrid bundle already imports `akeneo-design-system` (e.g. `DisplaySelector.tsx`).

Because the overlay is a **React portal** (not a jQuery `<body>`-append), `ReactDOM.unmountComponentAtNode` on the bridge root tears the menu down automatically — the `<body>`-orphan leak class that plagued the Select2/Datepicker waves (the "D5 leak") does not exist here.

### The constraint that shapes the whole roadmap: shared Behat decorators

Behat does not call JS APIs; it drives the widget by querying its **exact markup**. That markup is hard-coded in **shared** Behat decorators/page-objects:

| File | Selector it queries |
|---|---|
| `tests/legacy/features/Behat/Decorator/Field/MultiSelectDecorator.php` | `.select-filter-widget` (visible widget) → optional `input[type="search"]` → `li label:contains("<value>")` (click) |
| `tests/legacy/features/Behat/Decorator/Grid/Filter/ChoiceDecorator.php` | `.filter-select` (entry) → `MultiSelectDecorator`; `getAvailableValues()` → `.ui-multiselect-menu.select-filter-widget` → `li span` |
| `tests/legacy/features/Context/Page/Product/Index.php` | `.filter-list.select-filter-widget .ui-multiselect-checkboxes li label span` (Manage-filters options) |

These decorators are shared by `choice` / `product_scope` / `product_completeness` / `ajax-choice` **and** the `filters-manager` add-filter dropdown. So changing the markup of only some consumers would break Behat for the rest. This forces a choice: big-bang all consumers at once, or **keep the shared decorators tolerant of both markups during a staged migration.** We choose staged bi-markup: safer, incremental, each PR independently CI-green.

### Environment constraints

Jest cannot be run locally (it OOM-crashes the machine) and the Behat env is too heavy for routine local runs. Confidence therefore comes from careful review + CI (the 10 legacy Behat shards + `test-front-unit` + `mutation-testing-front`). Every new source `*.unit.tsx` must be added to the hand-maintained `stryker.jest.js` `testMatch` allowlist or its mutants all survive (MSI 0 → break at 50%).

## Roadmap (decomposition — each is its own spec/plan/PR)

1. **Foundation (this spec):** `SelectFilterCriteria.tsx` + `select-filter-react.ts` bridge + bi-markup Behat decorators + `select` re-pointed.
2. `multiselect-filter-react.ts` (extends the bridge; `MultiSelectInput`; the legacy "All" selection logic).
3. `select-row-filter-react.ts` (extends the bridge; single `SelectInput`; keeps the inherited backgrid row-selection value plumbing).
4. `product_scope` / `product_completeness` / `ajax-choice` re-pointed to React bridges (they `SelectFilter.extend` / `MultiSelectFilter.extend`).
5. `filters-manager` add-filter dropdown → DSM component (the riskiest consumer: it is a filter-*picker*, not a value filter).
6. **Cleanup:** delete `jquery.multiselect.js` (737) + `jquery.multiselect.filter.js` (192) + `multiselect-decorator.js` (163) + `jquery.multiselect.less` (322) + `jquery.multiselect.css`, and remove the legacy branch from the bi-markup Behat decorators.

(PRs 2–6 are sized during their own brainstorm; the count is indicative.)

## Sub-project #1 architecture

### Files

| File | Create/Modify | Responsibility |
|---|---|---|
| `.../datafilter/filter/SelectFilterCriteria.tsx` | **Create** | Shared controlled React view: renders `.filter-select` wrapper + `SelectInput`/`MultiSelectInput` + `.disable-filter`, with stable Behat hooks. The `ChoiceFilterCriteria` analogue for this wave. |
| `.../datafilter/filter/select-filter-react.ts` | **Create** | Bridge: `SelectFilter.extend`; owns React mount/unmount, the React-state value source-of-truth, and the `_readDOMValue`/`_writeDOMValue`/`setValue` mapping. Base class for the wave-2/3 bridges. |
| `.../datafilter/FilterTypeRegistry.ts` | Modify | Re-point `select` → `oro/datafilter/select-filter-react`. |
| `.../Resources/config/requirejs.yml` | Modify | Add the AMD alias `oro/datafilter/select-filter-react`. |
| `tests/front/unit/jest/stryker.jest.js` | Modify | Allowlist the two new `*.unit.tsx`. |
| `Behat/Decorator/Field/MultiSelectDecorator.php` | Modify | Bi-markup `setValue`: DSM path, legacy fallback. |
| `Behat/Decorator/Grid/Filter/ChoiceDecorator.php` | Modify | Bi-markup `getAvailableValues`: DSM path, legacy fallback. |
| `.../SelectFilterCriteria.unit.tsx` | **Create** (test) | Renders single/multi, options, disable, onChange. |
| `.../select-filter-react.unit.tsx` | **Create** (test) | Bridge overrides: value mapping, mount/unmount, `_formatRawValue`, `_onReactChange`. |
| `tests/front/unit/FilterTypeRegistry.unit.ts` | Modify (test) | Update the `select` module-id assertion. |

### `SelectFilterCriteria.tsx`

A single controlled React view, deliberately generic over single/multi so waves 2–3 reuse it unchanged.

```tsx
type Choice = {value: string; label: string};

type Props = {
  multiple: boolean;
  value: string[];                    // internal representation is ALWAYS an array (single = [] or [v])
  choices: Choice[];                  // already sorted + "All"-prepended by the bridge (see Value semantics)
  showLabel: boolean;
  label: string;
  canDisable: boolean;
  placeholder: string;                // i18n'd by the bridge
  emptyResultLabel: string;           // i18n'd by the bridge
  openLabel: string;                  // i18n'd by the bridge
  removeLabel: string;                // i18n'd by the bridge (multi only; harmless for single)
  onChange: (values: string[]) => void;
  onDisable: () => void;
};
```

Renders:
- `<div className="AknFilterBox-filter filter-select filter-criteria-selector" data-testid="select-filter-widget">` — keeps `.filter-select` (the `ChoiceDecorator.filter()` entry point) and adds `data-testid="select-filter-widget"` as the DSM widget hook.
- optional `<span className="AknFilterBox-filterLabel">{label}</span>` when `showLabel`.
- when `multiple`: `<MultiSelectInput value={value} onChange={onChange} …>` with `{choices.map(c => <MultiSelectInput.Option key={c.value} value={c.value} data-testid={c.value}>{c.label}</MultiSelectInput.Option>)}`.
- when single: `<SelectInput value={value[0] ?? null} onChange={v => onChange(v === null ? [] : [v])} clearable …>` with `{choices.map(c => <SelectInput.Option key={c.value} value={c.value}>{c.label}</SelectInput.Option>)}` (SelectInput stamps `data-testid={value}` itself).
- `<a href={nullLink} className="AknFilterBox-disableFilter AknIconButton AknIconButton--remove disable-filter" onClick={onDisable}/>` when `canDisable`.

The DSM overlay portals to `<body>`; option click targets are `[data-testid="<value>"]`. Only one overlay is open at a time.

### `select-filter-react.ts` bridge

`SelectFilter.extend({...})` — same proven pattern as the `price`/`select2` bridges: **React state is the single source of truth**, the DOM `<select>` and the jQuery widget init are dropped.

State: `_selectedValues: string[]` (declared bare — never a class-field initializer, per the class-field-clobber lesson; seeded in a `constructor`/`initialize` override from the model value).

Overrides:
- `render()`: call `AbstractFilter.prototype.render` for the base wiring, then `this._renderReact()`. **Do NOT** call `_initializeSelectWidget()` / build the `<select>` template.
- `_renderReact()`: `ReactDOM.render(<Provider…?>` not needed — no store; just `ReactDOM.render(<SelectFilterCriteria multiple={!!this.widgetOptions.multiple} value={this._selectedValues} choices={this._reactChoices()} onChange={this._onReactChange.bind(this)} onDisable={() => this.disable()} …i18n props… />, this.el)`.
- `_reactChoices()`: replicate the legacy `render()` choice prep — `this.choices` sorted by translated label, with `{value:'', label: this.placeholder}` prepended when `this.populateDefault`.
- `_onReactChange(values)`: `this._selectedValues = values; this._renderReact(); this.setValue(this._formatRawValue(this._readDOMValue()));`.
- `_readDOMValue()`: read from state, not the DOM — single: `{value: this._selectedValues[0] ?? ''}`; the multi override (wave 2) returns `{value: this._selectedValues}`.
- `_writeDOMValue(value)`: external model → state: set `this._selectedValues` from `value.value` (normalize string→`[string]`, `''`→`[]`), then `this._renderReact()`. Keep it a no-op-safe re-render (idempotent).
- `_onValueUpdated(newValue, oldValue)`: call `AbstractFilter.prototype._onValueUpdated`, then sync `this._selectedValues` from `newValue` and `this._renderReact()` (replaces the legacy `selectWidget.multiselect('refresh')`); update the hint label as the legacy did.
- `remove()` / `dispose()`: `ReactDOM.unmountComponentAtNode(this.el[0])`, then the base `remove`.

The bridge inherits everything else (`disable`, `enable`, `_onClickDisableFilter`, `getValue`, `_formatRawValue`, `_formatDisplayValue`, the `events` hash minus the now-unused `change select` / `click .filter-select` widget handlers, which are harmless since the elements no longer exist).

### Bi-markup Behat decorators

**`MultiSelectDecorator::setValue($value)`** — try DSM first, fall back to legacy:
1. Spin for a visible DSM widget: `.filter-select[data-testid="select-filter-widget"]` (or the `.select-filter-widget` legacy class). If a DSM widget is present:
   - click it to open the overlay (click the `data-testid="select-filter-widget"` container / its input);
   - for each comma-split value: find `[data-testid="<value>"]` in the body overlay (fallback: `span:contains("<value>")` in the visible overlay) and click it.
2. Else run the existing legacy logic unchanged (`.select-filter-widget` → `input[type=search]` → `li label:contains`).

**`ChoiceDecorator::getAvailableValues()`** — DSM path: open the overlay, read `[data-testid]` option `<span>` texts from the visible portal; legacy path unchanged (`.ui-multiselect-menu.select-filter-widget` → `li span`). Select the branch by which markup is present/visible.

The DSM and legacy branches coexist until the roadmap's final PR removes the legacy branch.

## Value semantics (the subtle part)

- Internal bridge representation is **always** `string[]`. Single (`select`, `select-row`): "All"/empty is represented as either `[]` or `['']` — the bridge treats them identically (see the read rule below), so the implementer need not collapse one into the other. `[v]` = one selection. Multi (`multiselect`): the array of selected codes.
- The legacy `select` "All" option: `render()` prepends `{value:'', label: placeholder}` when `populateDefault`. We reproduce it in `_reactChoices()`. Selecting "All" makes `SelectInput` emit `''`, so the component calls `onChange([''])`; the bridge stores `['']`. **The single-select read rule is `{value: this._selectedValues[0] ?? ''}`**, so both `[]` and `['']` ⇒ `{value:''}` (= "All"), matching the legacy model shape exactly. Re-rendering with `value=['']` shows the "All" option selected, which is the correct UI.
- `getValue()` / `_formatRawValue()` / `_formatDisplayValue()` are inherited unchanged, so downstream (URL state, grid reload, hint) sees the identical value object it did with the jQuery widget.
- `select-row` (wave 3) overrides `getValue`/`_formatRawValue`/`_getSelection`/`_initialSelection` (backgrid coupling) and inherits the React UI unchanged; nothing in the row-selection plumbing touches the widget.

## Testing / CI

- **`SelectFilterCriteria.unit.tsx`**: renders single (`SelectInput`) and multi (`MultiSelectInput`) with the right options + `data-testid`s; `showLabel`/`canDisable` conditionals; `onChange` maps single `SelectInput` `null`→`[]` and `v`→`[v]`; `onDisable` fires. Mock `akeneo-design-system` at the module boundary (`__esModule: true` default-export guard per the jest lesson) OR render the real DSM component (preferred if it renders cleanly under ts-jest) — decided in the plan.
- **`select-filter-react.unit.tsx`**: `_readDOMValue` reads state (single mapping); `_onReactChange` sets state + `setValue` + re-renders (spy `_renderReact` to avoid a real DSM render in a state-only test, per the price-bridge lesson); `_writeDOMValue`/`_onValueUpdated` sync state; `render` mounts and `remove` unmounts (`ReactDOM.unmountComponentAtNode` called); `_reactChoices` sort + "All" prepend. Every method the mutation run touches (`_renderReact`, `_getCriteriaHint` if overridden) must be executed by a test or its mutants survive.
- Add both new `*.unit.tsx` to `stryker.jest.js` `testMatch`.
- **`FilterTypeRegistry.unit.ts`**: update the `select` assertion to `oro/datafilter/select-filter-react` (the alias `choice → select` resolution test also updates).
- **Behat**: the 10 legacy shards validate the bi-markup swap end-to-end for the `select`-type grids (e.g. the User grid `frontend_type: select`) and — crucially — must stay green for the *unmigrated* `choice`/`product_scope`/`ajax-choice`/`filters-manager` consumers, proving the bi-markup decorators didn't regress them. No new Behat scenarios.
- **No local runs** (Jest OOM, Behat heavy). Confidence = review + CI.

## Risks / open items (resolve during implementation)

1. **DSM overlay open-trigger for Behat.** Confirm the exact element Selenium must click to open the overlay (the `data-testid` container vs the inner input vs the chevron `IconButton[title=openLabel]`), and whether a `search` type input exists to type into. Nail it in the plan's Behat task; the DSM markup is stable enum-free so this is a lookup, not a design risk.
2. **i18n props.** `placeholder`/`emptyResultLabel`/`openLabel`/`removeLabel` need translation keys. Reuse the legacy `this.placeholder`; source the three others from existing translation keys (e.g. `pim_datagrid.filters.*` / `pim_common.*`) — pick concrete keys in the plan, do not invent copy.
3. **Optional hidden `<select>` compatibility.** Decide in the plan whether to also render a hidden `<select>` synced from state for any external DOM reader. Default: **state-only** (no hidden select), matching the price/select2 bridges; only add one if a concrete external reader is found (grep shows none beyond the filter's own inherited methods, which we override).
4. **`events` hash.** The inherited `events` reference `change select` / `click .filter-select` handlers whose elements no longer exist; confirm they are inert (Backbone delegates, no matching node = no-op) rather than removing them, to keep the diff minimal — or override `events` to drop them. Decide in the plan.
5. **`FilterTypeRegistry` alias `choice → select`.** `FILTER_TYPE_ALIASES.choice = 'select'`, so re-pointing `select` also re-points the `choice`-metadata filters (the option-attribute filters) to React. Confirm that is intended (it is — that is the whole `select`-family) and that the Behat `filter_products_per_option_fields.feature` shard exercises exactly this path.
