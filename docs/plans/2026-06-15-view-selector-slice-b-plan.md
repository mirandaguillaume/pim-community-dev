# View-Selector Slice B (CRUD buttons + create-modal fields → React) — Spec & Plan

> Part of the C1 product-grid view-selector wave. Grounded in
> `docs/plans/2026-06-12-view-selector-grounding.md` (§9 Slice B). Follows Slice A (#272) and the
> Strangler pattern. **Local constraint:** Jest is NOT run locally → validate via CI.

**Goal:** Render the three view-selector CRUD action links (Create / Save / Remove) and the *fields*
of the create-view modal with React, keeping each Backbone shell's behaviour and the
`Backbone.BootstrapModal` chrome.

## Scope decision (the modal)

A full React/DSM modal would replace `.modal`/`.modal-body`/`.ok` — the chrome that the **PIM-wide
shared Behat step** `"I fill in the following information in the popin"` (used by 6/8
`datagrid_views.feature` scenarios and across the whole suite) keys on. That is a suite-wide
refactor, not a slice. **Decision: hybrid** — keep the BootstrapModal chrome, render only the modal
*fields* (label input + type toggle) with React.

## Files

### Create `js/grid/ViewSelectorActionLink.tsx`
One presentational component for all three anchors. Reproduces
`view-selector-{create,save,remove}-view.html`:
`<a class="AknDropdown-menuLink {action}{hidden? ' AknDropdown-menuLink--hidden'}">{label}</a>`.
Props `{action: 'create'|'save'|'remove', label, hidden?}`.

### Create `js/grid/CreateViewFields.tsx`
Controlled component for the modal content. Reproduces `view-selector-create-view-inputs.html`:
`input[name="new-view-label"]` (`.AknTextField`) + the `.AknCreateView-typeSelector`
public/private toggle (`AknSelectButton--selected` when private, default true). Owns `label` +
`isPrivate`; lifts them via `onChange({label, isPrivate})`; calls `onSubmit()` on Enter when the
label is non-empty. Props `{labels:{chooseLabel, placeholder, chooseType}, onChange, onSubmit}`.

### Modify `js/grid/view-selector-create-view.js`
- `render()` → `renderReact(ViewSelectorActionLink, {action:'create', label}, this.el)`; the
  `currentViewType !== 'view'` branch `unmountReact()` + `$el.empty()`.
- `promptCreateView()` → opens the BootstrapModal with empty fields (`templateModalContent({fields:''})`),
  then `ReactDOM.render(CreateViewFields, …)` into `[data-drop-zone="fields"]`. `onChange` stores
  `this.viewToCreate` and toggles the modal `.ok` `AknButton--disabled`; `onSubmit` triggers `.ok`.
  Cancel/ok unmount the fields via `unmountModalFields()` (the modal lives outside `this.el`, so it
  is NOT covered by `BaseView.remove()` — hence a manual `ReactDOM.unmountComponentAtNode`).
- `saveView(modal)` builds the payload from `DatagridState` + `this.viewToCreate` and POSTs via
  `DatagridViewSaver` (unchanged otherwise).
- Drop the `template`/`templateInput` (`create-view`, `create-view-inputs`) imports; keep
  `templateModalContent`.

### Modify `js/grid/view-selector-save-view.js`
`render()` → `renderReact(ViewSelectorActionLink, {action:'save', label, hidden: !this.dirty}, this.el)`;
keep `configure()` + `onDatagridStateChange` (dirty computation) + `saveView` + the owner check.

### Modify `js/grid/view-selector-remove-view.js`
`render()` → `renderReact(ViewSelectorActionLink, {action:'remove', label}, this.el)`; keep
`promptDeletion` (`pim/dialog` confirm) + `removeView`. **Fix the `[object Object]` asymmetry**:
`removeView` failure now iterates an array `responseJSON` (like create/save) and falls back to the
raw value otherwise.

### Tests + Stryker
`ViewSelectorActionLink.unit.tsx` (action class, hidden modifier, label) + `CreateViewFields.unit.tsx`
(input + default-private toggle, label lift, type toggle, Enter-submit guard). Raw-DOM + `jest.fn()`
(Stryker-safe). Registered in `stryker.jest.js` `testMatch`.

## Behat contract preserved
`.create-button .create`, `.save-button .save` (+ `--hidden`), `.remove-button .remove`,
`.modal`/`.modal-body`/`.ok`/`AknButton--disabled` (BootstrapModal chrome kept),
`input[name="new-view-label"]`, `.AknCreateView-typeSelector`. The shared "fill in the popin" step
is untouched.

## Out of scope
Select2 replacement (Slice C), the inner selector, secondary-actions, DatagridState, the
forwarded-events bridge, the backend, and any full React/DSM modal (would break the shared popin step).
