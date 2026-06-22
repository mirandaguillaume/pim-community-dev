# Grounding — Slice C: text/choice popup filter archetype → React

> 7-agent Workflow (2026-06-19). The hardest filter slice (popup + operator dropdown + positioning + 4 Behat decorators).

## Product-Grid text/choice popup filter archetype — Backbone→React grounding

### Scope reality check (read this first)
The 6 filters in scope are **not** a clean linear chain. The real inheritance is:

```
AbstractFilter (abstract-filter.js)   — Backbone.View root, value contract, popup positioning hack
  └─ TextFilter (text-filter.js)      — chip+popup lifecycle, {value:''}; criteriaHint
       └─ ChoiceFilter (choice-filter.js) — adds operator type {type,value}; Select2 'in'-mode; AknDropdown
            ├─ NumberFilter   (number-filter.js)     — 1 override: isNaN guard in _onClickUpdateCriteria
            ├─ IdentifierFilter (identifier-filter.js) — config only: 7 operators, emptyValue {type:'in'}
            ├─ ParentFilter   (parent-filter.js)      — 2 operators, _showCriteria/_focusCriteria/_readDOMValue overrides (Select2 focus)
            └─ UuidFilter     (uuid-filter.js)        — config only: 1 operator 'in'
```

**Critical constraint the prompt's "text<-choice<-{4}" framing hides:** `text-filter.js` and `choice-filter.js` are also the base of **three other filters NOT in scope** — `select2-choice-filter`, `select2-rest-choice-filter`, and `ajax-choice-filter` — which call `TextFilter.prototype._renderCriteria.apply(this,...)`, `ChoiceFilter.prototype._showCriteria.apply(...)`, etc. (confirmed: choice-filter.js itself does this at lines 158, 240). **Therefore the `text-filter.js`/`choice-filter.js` prototype surface (the `_renderCriteria`/`_readDOMValue`/`_writeDOMValue`/`_showCriteria`/`_hideCriteria`/`_enableListSelection` methods) cannot be deleted or moved into a React-only base while those 3 out-of-scope filters still inherit from them.** A React migration must either (a) fork new React subclasses (`*-filter-react.js` extending `ReactFilterBase`) and re-point only the 6 in-scope registry aliases, leaving the legacy classes intact for the 3 select2 filters; or (b) migrate all of choice-filter's children at once. Option (a) is the strangler-safe path and matches how `ReactFilterBase`/`SearchFilterInput` were introduced in Wave 4 (new class alongside the old).

---

### The chip + popup + operator UI to reproduce

**Chip** (currently the inline `template` string in text-filter.js lines 11–21):
- `div.AknFilterBox-filter.filter-criteria-selector.oro-drop-opener` (the click target — Behat `BaseDecorator::open()`)
  - `span.AknFilterBox-filterLabel` (when `showLabel`)
  - `span.AknFilterBox-filterCriteria.AknFilterBox-filterCriteria--limited.filter-criteria-hint` (the live hint — Behat `getCriteriaHint()`, DataGridContext `:contains()` assertion)
  - `span.AknFilterBox-filterCaret`
- `div.filter-criteria.dropdown-menu` (the popup container — visibility drives Behat Update-click loop)
- `div.AknFilterBox-disableFilter.AknIconButton.AknIconButton--remove.disable-filter` (when `canDisable` — Behat `remove()`)

**Popup** (currently `templates/filter/text-filter.html`, shared by text + choice):
- `div.AknFilterChoice.choicefilter` wrapper
- `div.AknFilterChoice-header` > `div.AknFilterChoice-title` (label) + **operator block guarded by `emptyChoice`**:
  - `div.AknDropdown.operator`
    - `div.AknActionButton.AknActionButton--withoutBorder[data-toggle="dropdown"]` > `span.AknActionButton-highlight` (selected op label) + `span.AknActionButton-caret`
    - `div.AknDropdown-menu` > `div.AknDropdown-menuTitle` + per-op `div.AknDropdown-menuLink[.AknDropdown-menuLink--active.active]` > `span.label.operator_choice[data-value="{op}"]`
- `input[type=text][name="value"].AknTextField.select-field` (value field — Behat `.select-field` / `input[name="value"]`)
- `div.AknFilterChoice-button` > `button.AknButton.AknButton--apply.filter-update` (Behat clicks to commit)

**The operator dropdown is the single hardest UI element.** `OperatorDecorator::setValue()` (lines 27–43) does: `click()` the `[data-toggle="dropdown"]` element, then `getClosest($this, 'AknDropdown')` (walks DOM ancestors for the literal class `AknDropdown`), then `findAll('css', '.label, .AknDropdown-menu .choice_value, .AknDropdown-menu .operator_choice')` and matches by **exact case-insensitive text**, then `click()` the matching span. The text match is exact-trim (comment explicitly notes `">="` contains `">"`). This means a DSM `SelectInput` for the operator is **incompatible without rewriting OperatorDecorator** — DSM emits none of `AknDropdown`, `operator_choice`, `[data-toggle="dropdown"]`. Two roads:
- **A (legacy-HTML operator):** Build a custom React `OperatorDropdown` that emits the *exact* AknDropdown markup above (no DSM Overlay, plain conditional render). Behat untouched. Lower risk, more bespoke code. This is the same trade-off ViewSelectorCombobox made (it kept `select2-container`/`select2-result-label` classnames).
- **B (DSM SelectInput + rewrite OperatorDecorator):** Use DSM SelectInput, rewrite OperatorDecorator to a `data-testid` pattern. Cleaner long-term but is a Behat-iterated PR (HIGH risk, cannot validate locally — same profile as Slice C 2/2).

Also note `choice-filter._writeDOMValue` calls `_highlightDropdown(value.type, '.operator')` which queries `.operator .AknDropdown-menuLink` and `*[data-value=...]` and writes `.AknActionButton-highlight` text; `_readDOMValue` reads `.active .operator_choice` `data-value`. So the **operator state is read back out of the DOM**, not React state. A React operator widget must either keep that hidden-input + active-class DOM contract or the migration must also rewrite `_readDOMValue`/`_writeDOMValue`.

---

### The positioning hack and the `useFilterPopupPosition` hook

The hack (abstract-filter.js `_updateCriteriaSelectorPosition`, lines 444–470): because `.AknColumn` has `overflow:hidden` while `.AknColumn-inner` has `overflow-x:auto`, the browser promotes `.AknColumn-inner` to a clip+scroll context that clips `position:absolute` descendants. Workaround: force `position:fixed` on `.filter-criteria`, compute `left`/`top` from `this.$el.offset()` with manual boundary checks (right-overflow flips left; bottom-overflow clamps up or to 0). A namespaced `scroll.filterCriteria-{cid}` handler is bound to **every** `.column-inner` in the document (render() line 145) to re-sync on scroll. `choice-filter` also re-fires it on Select2 `change` (line 215) because tag additions resize the popup.

**`ReactFilterBase.render()` deliberately does NOT call `AbstractFilter.prototype.render`** (documented lines 15–19), so React filters get **no scroll handler at all** — popup positioning is explicitly deferred to "a dedicated React hook in a later slice." That slice is this work.

The repo has **no floating-ui** (confirmed: only Overlay.tsx matches `getBoundingClientRect`). The DSM `Dropdown.Overlay` already implements exactly the needed primitive: `createPortal` into a `div#dropdown-root` on `document.body` (escapes the clip context), `position:fixed`, `top/left` from `parentRef.getBoundingClientRect()`, `useVerticalPosition`/`useHorizontalPosition` flip hooks, `useWindowResize`, a `Backdrop` (z-index 1900) for outside-click, and `useShortcut(Escape, onClose)`. **What Overlay is missing vs. the legacy hack:** it does NOT subscribe to `.column-inner` *scroll* (only window resize), and it does NOT re-measure on popup content resize (Select2 grow). So the hook is: either (1) wrap DSM `Dropdown`/`Overlay` and add a `scroll` listener on `anchorRef.current.closest('.column-inner')` + a `ResizeObserver` on the popup, OR (2) write a thin standalone `useFilterPopupPosition(anchorRef, popupRef, isOpen)` returning `{top,left}` that: measures on open via `getBoundingClientRect()`, subscribes to `scroll` on `closest('.column-inner')` and `window`, replicates the flip/clamp math, and registers `document.addEventListener('mousedown', …)` closing when `!anchor.contains(target) && !popup.contains(target)` (the portal puts the popup outside the anchor subtree, so both checks are needed — matches text-filter's own mousedown strategy at lines 156–167).

**Behat-critical side effect:** `BaseDecorator::open()` polls `hasClass('open-filter')` on the filter item. That `open-filter` class is toggled by `_setButtonPressed` on the **container** (Backbone `this.$el`). If the popup open-state moves into React, the Backbone shell must still `this.$el.toggleClass('open-filter', isOpen)` on every open/close, or `open()` loops forever.

---

### Behat selector inventory (the in-scope decorators)

The archetype is driven by **4 decorators** (NOT ChoiceDecorator — that one targets `.filter-select`/jQuery-UI multiselect, a *different* archetype for multichoice/boolean, out of scope):

| Decorator | Selectors it depends on |
|---|---|
| `BaseDecorator` | `.filter-criteria-selector` (open click), `.open-filter` class on item (open-confirm poll), `.disable-filter` (remove), `.filter-criteria-hint` (hint text) |
| `StringDecorator` | `*[data-toggle="dropdown"]` (→OperatorDecorator), `.select-field` (value; `Select2Decorator` for `in list`), `.filter-criteria` visibility, `.filter-update` |
| `NumberDecorator` | `*[data-toggle="dropdown"]` (→OperatorDecorator), `input[name="value"]`, `.filter-criteria` visibility, `.filter-update` |
| `OperatorDecorator` | `[data-toggle="dropdown"]` (click target), ancestor class `AknDropdown` (getClosest), `.AknDropdown-menu .operator_choice` / `.choice_value` / `.label` (exact text match) |

Grid page-object also needs `.filter-item[data-name="<name>"]` + `data-type` on the root (selects the decorator chain) — these are set by `abstract-filter.initialize()` from options and must be preserved on the Backbone shell `this.$el`.

**Two timing traps:** (1) String/Number decorators loop on `.filter-criteria` *visibility* — a React popup that closes via portal unmount must make `.filter-criteria` actually disappear/hide, not just empty its content; (2) `OperatorDecorator` uses `getClosest(…, 'AknDropdown')` walking *up* — if a React portal renders the operator menu **outside** the filter item, getClosest never finds `AknDropdown` and operator selection fails. So for the operator sub-widget, **inline (non-portal) AknDropdown markup is mandatory** (Approach A); a portalled DSM operator dropdown breaks this even if classnames matched.

---

### DSM reuse verdict

| Piece | Verdict |
|---|---|
| Popup panel positioning | **Reuse the pattern, not necessarily the component.** DSM `Dropdown.Overlay` has the exact portal+fixed+getBoundingClientRect engine but lacks `.column-inner` scroll + content-resize sync. Either extend it or write `useFilterPopupPosition` mirroring its math. No new dependency (no floating-ui). |
| Operator dropdown | **Custom React `OperatorDropdown` (Approach A).** DSM `SelectInput` cannot satisfy OperatorDecorator (`AknDropdown`/`operator_choice`/`data-toggle` + getClosest + exact-text) without a Behat-iterated decorator rewrite. Keep legacy AknDropdown markup, inline (non-portal). |
| Value field (text) | **DSM `TextInput`, uncontrolled (`defaultValue`), `name="value"` + `className="AknTextField select-field"`.** Follows `SearchFilterInput` precedent: Backbone jQuery keeps owning `.val()` reads via `_get/_setInputValue`. |
| Value field (number) | **DSM `NumberInput` uncontrolled** OR keep `<input type=text name=value>` — NumberDecorator only needs `input[name="value"]`. Note: NumberFilter's NaN guard lives in Backbone `_onClickUpdateCriteria` and reads via `_getInputValue`, so the input must stay jQuery-readable. |
| Value field (Select2 `in`-mode) | **Keep legacy Select2 v3 inside the popup for now (hybrid).** `_enableListSelection`/`_disableListSelection`/`_toggleListSelection` + `.select2('close')` on hide + ParentFilter's `input.select2-input` focus are deeply wired. Replacing the tag input is its own slice; for first migration, render the React chip+popup but mount Select2 into the React-rendered `input[name=value]` exactly as today. |
| Chip host | **Custom `FilterChip` styled/JSX** reproducing the chip markup byte-for-byte. DSM has no single-line chip-with-hint component (ChipInput is multi-tag, wrong shape). |
| React↔Backbone bridge | **Reuse `ReactFilterBase`** (the merged Wave-4 base; `ReactDOM.render`/`unmountComponentAtNode`). |

**Per-filter React cost:** Number, Identifier, Uuid need **zero per-filter React** if the React ChoiceBase exposes (a) a `validate`/`onBeforeApply` hook (Number's NaN guard), (b) operator config as props (Identifier 7, Uuid 1), (c) a single-operator mode that still renders a usable/hidden-but-present operator widget (Uuid — `emptyChoice` defaults true so the dropdown renders with one item). **ParentFilter is the only one needing bespoke React** (its `_focusCriteria` targets `input.select2-input`, its `_showCriteria` toggles Select2 by operator) — resolve last, against whatever tag-input API the React base settles on. ParentFilter's `_readDOMValue` is a verbatim copy of the base and can be dropped.

---

### Hard-truth summary
The text/value field is genuinely low-risk (uncontrolled, precedent exists). The **operator dropdown** and the **5→4 Behat decorators** force an Approach-A decision (keep AknDropdown HTML inline) on the very first slice, because the operator state round-trips through the DOM (`_readDOMValue`/`_highlightDropdown`) AND is read by a getClosest-based Behat decorator that cannot survive a portal. The **positioning hook** is net-new infra (no floating-ui, ReactFilterBase deliberately unbound the scroll handler) but DSM's Overlay is a strong copy-from source. And the **shared-base risk** (3 out-of-scope select2 filters inheriting text/choice prototypes) means this must be done as *new React subclasses alongside the legacy classes*, not an in-place rewrite of text-filter.js/choice-filter.js.

## Sub-slices (least → most risky)

### C1: Plain text-filter → React (no operator) — **MEDIUM**
- **Scope:** New ReactTextFilter (extends ReactFilterBase) used for the `text`/`pim/filter/text` registry aliases only. Renders FilterChip (chip markup byte-identical: .AknFilterBox-filter.filter-criteria-selector, .filter-criteria-hint, .AknFilterBox-filterCaret, .filter-criteria.dropdown-menu, .disable-filter) + popup body with DSM TextInput (uncontrolled, name=value, .AknTextField.select-field, .filter-update). emptyChoice=false so NO operator dropdown. Backbone shell keeps _getInputValue/_setInputValue/_readDOMValue/_writeDOMValue, getValue/setValue, _getCriteriaHint, enable/disable, and toggles .open-filter on this.$el. Popup still positioned by the legacy _updateCriteriaSelectorPosition initially (call AbstractFilter.prototype.render from this subclass's render to restore the scroll handler) so positioning is NOT changed in this slice.
- **Rationale:** Exercises the FilterChip + value-field + chip/hint/disable Behat contract (BaseDecorator + StringDecorator value path) in isolation, with zero operator-dropdown and zero positioning-hook risk. text-filter has only {value} so no type/Select2. Reuses the proven SearchFilterInput uncontrolled pattern. If anything in the chip markup is off, only the plain text filter breaks, and StringDecorator with operator path still works because emptyChoice=false means OperatorDecorator is never invoked for it.

### C2: useFilterPopupPosition hook + React portal popup — **MEDIUM**
- **Scope:** Introduce useFilterPopupPosition(anchorRef, popupRef, isOpen) (portal to document.body, position:fixed, getBoundingClientRect on open, scroll listener on closest('.column-inner')+window, flip/clamp math mirroring _updateCriteriaSelectorPosition, outside-mousedown close with both anchor+popup containment checks). Wire the C1 ReactTextFilter popup to render through it. Backbone shell toggles .open-filter on open/close. Stop relying on AbstractFilter.prototype.render scroll handler for this filter.
- **Rationale:** Net-new infra with no operator complexity yet. Isolated to the already-migrated plain text filter so a positioning regression is contained. DSM Overlay is a strong copy-source. The .filter-criteria visibility contract (StringDecorator/NumberDecorator loop) and .open-filter (BaseDecorator) are the things to prove here. Splitting this OUT of C1 means C1 can ship even if the hook needs Behat iteration.

### C3: ChoiceBase + custom OperatorDropdown (legacy AknDropdown HTML) — **HIGH**
- **Scope:** ReactChoiceFilter (extends the React text base) adding the operator sub-widget as a custom React OperatorDropdown that emits EXACT legacy markup: .AknDropdown.operator, [data-toggle=dropdown] trigger, .AknActionButton-highlight, .AknDropdown-menu, per-op .AknDropdown-menuLink[.active]>.operator_choice[data-value]. Inline (NOT portalled) so OperatorDecorator getClosest('AknDropdown') works. Keep hidden input[type=hidden] for type; keep _readDOMValue/_writeDOMValue/_highlightDropdown DOM round-trip OR rewrite both to React state (decision point). Keep legacy Select2 'in'-mode (hybrid: mount Select2 onto the React-rendered input[name=value] via _enableListSelection unchanged). Operator config (choices, emptyValue.type) passed as props. Used for nothing in registry yet — landed but not pointed at any filter, or pointed only at a low-traffic one behind a flag.
- **Rationale:** This is THE hard part: OperatorDecorator (getClosest + exact-text + data-toggle), _highlightDropdown DOM contract, Select2 'in'-mode lifecycle, and the emptyChoice=false→hardcoded 'in' fallback in _readDOMValue. Behat-iterated, cannot fully validate locally. Approach A (legacy HTML) is mandatory because a portalled/DSM operator breaks getClosest. Must stay alongside legacy choice-filter.js since 3 out-of-scope select2 filters still inherit it.

### C4: Repoint number + identifier + uuid filters to ChoiceBase — **MEDIUM**
- **Scope:** Re-point the `number`/`identifier`/`uuid` (and the equivalent registry aliases) to React subclasses of the C3 ChoiceBase. NumberFilter: pass a validate/onBeforeApply prop for the isNaN guard (the guard currently lives in _onClickUpdateCriteria reading _getInputValue — keep it in the Backbone shell). Identifier: pass the 7-operator config. Uuid: pass single-operator 'in' config; verify the single-item operator dropdown renders without breaking OperatorDecorator. No new React components.
- **Rationale:** Pure config-diff filters once C3 is stable — the maps confirm Number/Identifier/Uuid have zero DOM overrides (Number = one validation hook, Identifier/Uuid = choices+emptyValue only). Risk is mostly Behat surface multiplication (more filter types exercising the C3 operator path), not new code. Uuid's single-operator dropdown is the one edge to watch.

### C5: ParentFilter → ChoiceBase (Select2 focus contract) — **HIGH**
- **Scope:** Migrate parent-filter: its _showCriteria toggles Select2 by operator, _focusCriteria targets input.select2-input, _readDOMValue is a verbatim base copy (drop it). Resolve the Select2 tag-input focus against whatever the React base exposes; if Select2 stays hybrid (C3 decision), this is mostly wiring _showCriteria's enable/disableListSelection through the React operator-change callback and ref-focusing the .select2-input.
- **Rationale:** Explicitly the last/hardest per-filter item in the maps: hard-codes Select2 v3 DOM (input.select2-input) and eager 'in'-mode activation on open. Its keyboard-focus UX is the contract most likely to silently break. Do last so the React base's tag-input API is fully settled.

### ALT-bigbatch: all 6 filters + hook + operator in one PR — **HIGH**
- **Scope:** Everything (C1–C5) atomically in a single PR.
- **Rationale:** Only justified if Behat atomicity forces it (the maps note Slice C view-selector had to be atomic so decorators and markup flip together). Here the chip/value path (C1/C2) is decoupled from the operator/decorator path, so atomicity is NOT required across the whole thing — bundling them multiplies the HIGH-risk operator+Behat surface with the medium-risk chip+positioning surface, making a green/red signal impossible to localize.

## Recommended scope
SUB-SPLIT — do NOT do one batch. Recommended first step: ship C1 (plain text-filter → React chip + DSM TextInput value field) WITHOUT touching positioning or the operator dropdown, by having the new ReactTextFilter call AbstractFilter.prototype.render to keep the legacy scroll-positioning handler intact.

Why sub-split (vs the view-selector Slice-C atomic precedent): the archetype cleanly separates into a LOW/MEDIUM-risk axis (chip markup + uncontrolled value field + chip/hint/disable Behat = BaseDecorator + StringDecorator value path) and a HIGH-risk axis (operator dropdown DOM round-trip + OperatorDecorator getClosest/exact-text + Select2 in-mode + the positioning hook). Unlike view-selector, these two axes do NOT have to flip together for Behat to stay green: plain text-filter uses emptyChoice=false, so OperatorDecorator is never invoked and the whole operator decorator surface is untouched by C1. That decoupling is exactly what makes a green/red CI signal localizable. Bundling them (ALT-bigbatch) would force the HIGH-risk operator+Behat iteration to gate the easy chip win.

Recommended order: C1 (text chip+value) → C2 (positioning hook, still text-only) → C3 (ChoiceBase + custom legacy-HTML OperatorDropdown — the hard, Behat-iterated PR) → C4 (number/identifier/uuid config repoint) → C5 (parent, Select2 focus). C1+C2 could even be one PR if the team prefers, but keep C3 strictly separate and expect to iterate it on CI like Slice C 2/2.

Hard prerequisite for the whole effort: new React subclasses must be added ALONGSIDE the legacy text-filter.js/choice-filter.js (re-pointing only the 6 in-scope registry aliases), NOT an in-place rewrite — because select2-choice-filter, select2-rest-choice-filter and ajax-choice-filter (out of scope) still inherit TextFilter/ChoiceFilter prototype methods.

## Open questions
- Operator dropdown: Approach A (custom React OperatorDropdown emitting exact legacy AknDropdown/operator_choice/data-toggle HTML, OperatorDecorator untouched) or Approach B (DSM SelectInput + rewrite OperatorDecorator to data-testid, Behat-iterated like Slice C 2/2)? Recommendation is A because OperatorDecorator's getClosest('AknDropdown') breaks on any portalled menu — but B is the cleaner long-term endpoint. Which do we commit to before C3?
- Operator state source of truth: keep the current DOM round-trip (_readDOMValue reads `.active .operator_choice` data-value, _writeDOMValue calls _highlightDropdown + hidden input[type=hidden]) so the Backbone shell is unchanged, OR move operator state into React and rewrite _readDOMValue/_writeDOMValue? The DOM round-trip is lower-risk for Behat but keeps two state owners.
- Select2 'in'-mode value field: keep the legacy Select2 v3 tag input hybrid-mounted inside the React popup (as choice-filter does today via _enableListSelection on input[name=value]) for the first migration, or replace it with a React tag input now? Replacing it is a separate large effort and would also force ParentFilter's input.select2-input focus contract to change in the same slice.
- Positioning hook: extend DSM Dropdown.Overlay (add .column-inner scroll + ResizeObserver) and reuse it, or write a standalone useFilterPopupPosition that mirrors _updateCriteriaSelectorPosition's exact flip/clamp math? Reusing Overlay gives Escape-close/window-resize/flip for free but its current top/left math differs from the legacy offset()-based math.
- Confirm scope excludes ChoiceDecorator/multichoice/boolean filters: ChoiceDecorator targets .filter-select + jQuery-UI .ui-multiselect-menu (a different archetype). The 4 in-scope decorators are Base/String/Number/Operator. Is select2-choice-filter / select2-rest-choice-filter explicitly OUT of this wave (they share the text/choice prototype but use Select2ChoiceDecorator)?
- ParentFilter _readDOMValue is a verbatim copy of the ChoiceFilter base — confirm it can be deleted during migration rather than re-implemented (the maps flag it as dead/redundant).
- Should C1 and C2 (positioning hook) be one PR or two? They are decoupled enough to split, but C1's popup needs SOME positioning — the proposal keeps the legacy handler for C1 and swaps to the hook in C2. Acceptable, or do we want the hook in from the first React popup?
