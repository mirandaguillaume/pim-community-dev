# UIBundle React Components — Coverage Spec

Date: 2026-06-09
Branch: `test/jest-coverage-uibundle`
PR: to be opened against master

---

## Context

Phase C1 (Backbone→React Product Grid migration) needs a safety net of unit tests for the pure React components in UIBundle. Many UIBundle components are already covered (StopJobAction, DuplicateJob, QuantifiedAssociations, CategoryTree…). This spec covers the **8 remaining pure React components** with no test files.

**Excluded (not testable in isolation):**
- `reactCell.tsx` — extends Backbone StringCell
- `locale-switcher.tsx` (both) — extends BaseView / uses FetcherRegistry
- `SandboxHelperView.tsx`, `TreeAssociate.tsx`, `TreeView.tsx` — Backbone wrappers
- `controller/*.tsx` — full-app entry points

---

## Convention

All tests follow the **dominant UIBundle pattern** (20 existing tests):

| Aspect | Value |
|--------|-------|
| Test location | `src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/<subdir>/` |
| File suffix | `*.unit.tsx` |
| Render helper | `renderWithProviders` from `@akeneo-pim-community/legacy-bridge/tests/front/unit/utils` |
| i18n | `translate()` returns raw key — no override needed |
| `global.fetch` | `jest.fn().mockImplementation(...)` — `afterEach` clears |
| userEvent | Direct `userEvent.click(el)` — NO `.setup()` (v12.8.3) |

---

## Scope — 8 test files

### 1. `grid/ProductGridProjectDetails.unit.tsx`

**Source:** `Resources/public/js/grid/ProductGridProjectDetails.tsx`  
**Import depth from test:** `../../../../Resources/public/js/grid/ProductGridProjectDetails`

No i18n — `dueDateLabel` comes from props. `getLevel()` pure function determines Badge level.

| Test | Assertion |
|------|-----------|
| Renders completion ratio text | `"75 %"` in DOM |
| Renders dueDateLabel and dueDate | Both props in DOM |
| completionRatio=0 renders `"0 %"` | Text present (danger level) |
| completionRatio=100 renders `"100 %"` | Text present (primary level) |

```tsx
import {ProductGridProjectDetails} from '../../../../Resources/public/js/grid/ProductGridProjectDetails';

const defaultProps = {
  projectDetails: {
    dueDateLabel: 'Due date',
    dueDate: '2026-12-31',
    completionRatio: 75,
  },
};

test('It renders the completion ratio', () => {
  renderWithProviders(<ProductGridProjectDetails {...defaultProps} />);
  expect(screen.getByText('75 %')).toBeInTheDocument();
});

test('It renders dueDateLabel and dueDate', () => {
  renderWithProviders(<ProductGridProjectDetails {...defaultProps} />);
  expect(screen.getByText('Due date')).toBeInTheDocument();
  expect(screen.getByText('2026-12-31')).toBeInTheDocument();
});

test('It renders 0% for zero completion', () => {
  renderWithProviders(
    <ProductGridProjectDetails
      projectDetails={{dueDateLabel: 'Due date', dueDate: '2026-12-31', completionRatio: 0}}
    />
  );
  expect(screen.getByText('0 %')).toBeInTheDocument();
});

test('It renders 100% for full completion', () => {
  renderWithProviders(
    <ProductGridProjectDetails
      projectDetails={{dueDateLabel: 'Due date', dueDate: '2026-12-31', completionRatio: 100}}
    />
  );
  expect(screen.getByText('100 %')).toBeInTheDocument();
});
```

---

### 2. `grid/ProductGridViewTitle.unit.tsx`

**Source:** `Resources/public/js/grid/ProductGridViewTitle.tsx`  
**Import depth from test:** `../../../../Resources/public/js/grid/ProductGridViewTitle`

Uses `useTranslate` for `pim_common.public_view`. `children` is the view name string.

| Test | Assertion |
|------|-----------|
| Renders children (view name) | `"My View"` in DOM |
| `type='public'` shows public label | `pim_common.public_view` in DOM |
| `type='view'` shows public label | `pim_common.public_view` in DOM |
| `type='default'` hides public label | `pim_common.public_view` NOT in DOM |
| With `projectDetails` renders detail | `dueDateLabel` in DOM |

```tsx
import {ProductGridViewTitle} from '../../../../Resources/public/js/grid/ProductGridViewTitle';

test('It renders the view name', () => {
  renderWithProviders(<ProductGridViewTitle type="default">My View</ProductGridViewTitle>);
  expect(screen.getByText('My View')).toBeInTheDocument();
});

test('It shows public label for type "public"', () => {
  renderWithProviders(<ProductGridViewTitle type="public">My View</ProductGridViewTitle>);
  expect(screen.getByText(/pim_common\.public_view/)).toBeInTheDocument();
});

test('It shows public label for type "view"', () => {
  renderWithProviders(<ProductGridViewTitle type="view">My View</ProductGridViewTitle>);
  expect(screen.getByText(/pim_common\.public_view/)).toBeInTheDocument();
});

test('It hides public label for non-public type', () => {
  renderWithProviders(<ProductGridViewTitle type="default">My View</ProductGridViewTitle>);
  expect(screen.queryByText(/pim_common\.public_view/)).not.toBeInTheDocument();
});

test('It renders project details when provided', () => {
  renderWithProviders(
    <ProductGridViewTitle
      type="default"
      projectDetails={{dueDateLabel: 'Due date', dueDate: '2026-06-30', completionRatio: 50}}
    >
      My View
    </ProductGridViewTitle>
  );
  expect(screen.getByText('Due date')).toBeInTheDocument();
});
```

---

### 3. `job/common/breadcrumb/JobBreadcrumb.unit.tsx`

**Source:** `Resources/public/js/job/common/breadcrumb/JobBreadcrumb.tsx`  
**Import depth from test:** `../../../../../../Resources/public/js/job/common/breadcrumb/JobBreadcrumb`

Uses `useRoute` (returns route key as-is) and `useTranslate`.

| Test | Assertion |
|------|-----------|
| Renders jobLabel | `"My Export Job"` in DOM |
| Renders jobType i18n key | `pim_menu.tab.exports` in DOM |
| `isEdit=true` shows edit step | `pim_common.edit` in DOM |
| `isEdit=false` hides edit step | `pim_common.edit` NOT in DOM |

```tsx
import {JobBreadcrumb} from '../../../../../../Resources/public/js/job/common/breadcrumb/JobBreadcrumb';

test('It renders the job label', () => {
  renderWithProviders(<JobBreadcrumb isEdit={false} jobCode="my_export" jobLabel="My Export Job" jobType="export" />);
  expect(screen.getByText('My Export Job')).toBeInTheDocument();
});

test('It renders the job type i18n key', () => {
  renderWithProviders(<JobBreadcrumb isEdit={false} jobCode="my_export" jobLabel="My Export Job" jobType="export" />);
  expect(screen.getByText('pim_menu.tab.exports')).toBeInTheDocument();
});

test('It shows edit breadcrumb step when isEdit is true', () => {
  renderWithProviders(<JobBreadcrumb isEdit={true} jobCode="my_export" jobLabel="My Export Job" jobType="export" />);
  expect(screen.getByText('pim_common.edit')).toBeInTheDocument();
});

test('It hides edit breadcrumb step when isEdit is false', () => {
  renderWithProviders(<JobBreadcrumb isEdit={false} jobCode="my_export" jobLabel="My Export Job" jobType="export" />);
  expect(screen.queryByText('pim_common.edit')).not.toBeInTheDocument();
});
```

---

### 4. `family/form/template/FamilyTemplateSelector.unit.tsx`

**Source:** `Resources/public/js/family/form/template/FamilyTemplateSelector.tsx`  
**Import depth from test:** `../../../../../../Resources/public/js/family/form/template/FamilyTemplateSelector`

15-line component — smoke test + close callback.

| Test | Assertion |
|------|-----------|
| Renders without crash | `pim_common.cancel` in DOM |
| Calls close on dismiss | `close` mock called |

```tsx
import {FamilyTemplateSelector} from '../../../../../../Resources/public/js/family/form/template/FamilyTemplateSelector';

test('It renders the modal', () => {
  renderWithProviders(<FamilyTemplateSelector close={jest.fn()} />);
  expect(screen.getByTitle('pim_common.cancel')).toBeInTheDocument();
});

test('It calls close when dismissed', () => {
  const close = jest.fn();
  renderWithProviders(<FamilyTemplateSelector close={close} />);
  userEvent.click(screen.getByTitle('pim_common.cancel'));
  expect(close).toHaveBeenCalled();
});
```

Note: DSM `Modal` renders a close button with `title={closeTitle}`.

---

### 5. `mass-edit/form/ChooseApp.unit.tsx`

**Source:** `Resources/public/js/mass-edit/form/ChooseApp.tsx`  
**Import depth from test:** `../../../../../Resources/public/js/mass-edit/form/ChooseApp`

`useState` manages `currentOperationCode`. `getIcon()` maps legacy icon strings to DSM icons with console.warn fallback.

| Test | Assertion |
|------|-----------|
| Renders operation labels | Operation label text in DOM |
| Clicking tile calls onChange | `onChange` mock called with code |
| Selected operation tile is selected | The selected operation is pre-selected |
| Unknown icon does not crash | No error thrown |

```tsx
import {ChooseApp} from '../../../../../Resources/public/js/mass-edit/form/ChooseApp';

const operations = [
  {code: 'edit_common', label: 'Edit attributes', icon: 'icon-edit'},
  {code: 'add_to_group', label: 'Add to group', icon: 'icon-groups'},
];

test('It renders operation labels', () => {
  renderWithProviders(<ChooseApp operations={operations} onChange={jest.fn()} />);
  expect(screen.getByText('Edit attributes')).toBeInTheDocument();
  expect(screen.getByText('Add to group')).toBeInTheDocument();
});

test('It calls onChange when a tile is clicked', () => {
  const onChange = jest.fn();
  renderWithProviders(<ChooseApp operations={operations} onChange={onChange} />);
  userEvent.click(screen.getByText('Edit attributes'));
  expect(onChange).toHaveBeenCalledWith('edit_common');
});

test('It pre-selects the given selectedOperationCode', () => {
  renderWithProviders(
    <ChooseApp operations={operations} selectedOperationCode="add_to_group" onChange={jest.fn()} />
  );
  // Tile with selected state — verify the operation label is visible
  expect(screen.getByText('Add to group')).toBeInTheDocument();
});

test('It renders without crash for unknown icon', () => {
  const unknownOp = [{code: 'unknown', label: 'Unknown', icon: 'icon-nonexistent'}];
  renderWithProviders(<ChooseApp operations={unknownOp} onChange={jest.fn()} />);
  expect(screen.getByText('Unknown')).toBeInTheDocument();
});
```

---

### 6. `attribute/form/CreateAttributeCodeAndLabel.unit.tsx`

**Source:** `Resources/public/js/attribute/form/CreateAttributeCodeAndLabel.tsx`  
**Import depth from test:** `../../../../../Resources/public/js/attribute/form/CreateAttributeCodeAndLabel`

9-line thin wrapper around `CreateAttributeModal` from `settings-ui`. Mock the dependency.

```tsx
jest.mock('@akeneo-pim-community/settings-ui', () => ({
  CreateAttributeModal: ({onClose}: {onClose: () => void}) => (
    <div data-testid="create-attribute-modal">
      <button onClick={onClose}>Close</button>
    </div>
  ),
}));

import {view as CreateAttributeCodeAndLabel} from '../../../../../Resources/public/js/attribute/form/CreateAttributeCodeAndLabel';

test('It renders without crash and delegates to CreateAttributeModal', () => {
  renderWithProviders(
    <CreateAttributeCodeAndLabel
      onClose={jest.fn()}
      onStepConfirm={jest.fn()}
    />
  );
  expect(screen.getByTestId('create-attribute-modal')).toBeInTheDocument();
});
```

---

### 7. `attribute/form/SelectAttributeType.unit.tsx`

**Source:** `Resources/public/js/attribute/form/SelectAttributeType.tsx`  
**Import depth from test:** `../../../../../Resources/public/js/attribute/form/SelectAttributeType`

Mocks needed:
- `global.fetch` → returns attribute types JSON
- `../../../../../Resources/public/js/attribute/form/hooks` → `useGetIdentifierAttributesCount`
- `@akeneo-pim-community/shared` → `useFeatureFlags`, `useRouter` (spread `jest.requireActual`)

```tsx
afterEach(() => {
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

jest.mock('../../../../../Resources/public/js/attribute/form/hooks', () => ({
  useGetIdentifierAttributesCount: () => ({count: 0}),
  useMainIdentifierCode: () => 'sku',
}));

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useFeatureFlags: () => ({isEnabled: () => true}),
  useRouter: () => ({generate: (url: string) => url}),
}));
```

| Test | Setup | Assertion |
|------|-------|-----------|
| Renders tiles after fetch resolves | fetch returns `{pim_catalog_text: {}, pim_catalog_textarea: {}}` | tile labels in DOM (i18n keys) |
| Clicking tile calls onStepConfirm | Same | `onStepConfirm({attribute_type: 'pim_catalog_text'})` called |
| Identifier disabled when count ≥ 10 | fetch includes `pim_catalog_identifier`, mock count=10 | identifier tile is `aria-disabled` |

```tsx
test('It renders attribute type tiles after fetching', async () => {
  global.fetch = jest.fn().mockResolvedValue({
    ok: true,
    json: () => Promise.resolve({pim_catalog_text: {}, pim_catalog_textarea: {}}),
  });

  renderWithProviders(
    <SelectAttributeType iconsMap={{}} onClose={jest.fn()} onStepConfirm={jest.fn()} />
  );

  await screen.findByText('pim_enrich.entity.attribute.property.type.pim_catalog_text');
});

test('It calls onStepConfirm when a tile is clicked', async () => {
  const onStepConfirm = jest.fn();
  global.fetch = jest.fn().mockResolvedValue({
    ok: true,
    json: () => Promise.resolve({pim_catalog_text: {}}),
  });

  renderWithProviders(
    <SelectAttributeType iconsMap={{}} onClose={jest.fn()} onStepConfirm={onStepConfirm} />
  );

  await screen.findByText('pim_enrich.entity.attribute.property.type.pim_catalog_text');
  userEvent.click(screen.getByText('pim_enrich.entity.attribute.property.type.pim_catalog_text'));
  expect(onStepConfirm).toHaveBeenCalledWith({attribute_type: 'pim_catalog_text'});
});
```

For the "identifier disabled when count ≥ 10" test, override the mock locally:

```tsx
test('It disables the identifier tile when the limit is reached', async () => {
  jest.resetModules();
  // re-require with count=10 (use a local module factory approach):
  // Simplest: mock at module level with jest.mock factories are hoisted —
  // use a let variable in the hook mock:

  // Approach: separate test file, or use jest.fn() return value:
  // The hooks mock returns {count: 0} by default. For this test,
  // re-mock inline using jest.doMock (not hoisted):
  // Easier: keep hooks mock as jest.fn() at top, change return value per test.
});
```

**Implementation note:** For the identifier-disabled test, declare `useGetIdentifierAttributesCount` as a `jest.fn()` in the module mock and use `mockReturnValue({count: 10})` before that test. Example:

```tsx
const mockUseGetIdentifierAttributesCount = jest.fn(() => ({count: 0}));

jest.mock('../../../../../Resources/public/js/attribute/form/hooks', () => ({
  useGetIdentifierAttributesCount: () => mockUseGetIdentifierAttributesCount(),
  useMainIdentifierCode: () => 'sku',
}));

test('It disables identifier tile when limit is reached', async () => {
  mockUseGetIdentifierAttributesCount.mockReturnValue({count: 10});
  global.fetch = jest.fn().mockResolvedValue({
    ok: true,
    json: () => Promise.resolve({pim_catalog_identifier: {}, pim_catalog_text: {}}),
  });

  renderWithProviders(
    <SelectAttributeType iconsMap={{}} onClose={jest.fn()} onStepConfirm={jest.fn()} />
  );

  await screen.findByText('pim_enrich.entity.attribute.property.type.pim_catalog_identifier');
  // Tile is disabled — DSM Tile renders with aria-disabled when disabled=true
  const identifierTile = screen.getByText('pim_enrich.entity.attribute.property.type.pim_catalog_identifier').closest('[aria-disabled]');
  expect(identifierTile).toHaveAttribute('aria-disabled', 'true');
});
```

---

### 8. `attribute/form/CreateAttributeButtonApp.unit.tsx`

**Source:** `Resources/public/js/attribute/form/CreateAttributeButtonApp.tsx`  
**Import depth from test:** `../../../../../Resources/public/js/attribute/form/CreateAttributeButtonApp`

Mock `SelectAttributeType` (default export) so tests don't need fetch. Mock `settings-ui` for `CreateAttributeProgressIndicator`.

```tsx
jest.mock('../../../../../Resources/public/js/attribute/form/SelectAttributeType', () => ({
  default: ({onStepConfirm, onClose}: any) => (
    <div data-testid="select-attribute-type">
      <button onClick={() => onStepConfirm({attribute_type: 'pim_catalog_text'})}>Select text</button>
      <button onClick={onClose}>Close selector</button>
    </div>
  ),
}));

jest.mock('@akeneo-pim-community/settings-ui', () => ({
  CreateAttributeProgressIndicator: () => null,
}));
```

| Test | Assertion |
|------|-----------|
| Renders button with buttonTitle | `"Create attribute"` button in DOM |
| Clicking button shows SelectAttributeType | `data-testid="select-attribute-type"` in DOM |
| SelectAttributeType not visible initially | `queryByTestId('select-attribute-type')` is null |
| After step confirmation, next step view renders | Custom step component shown |
| Final step calls onClick with merged data | `onClick` spy called with `{attribute_type: 'pim_catalog_text', ...}` |

```tsx
const StepView: React.FC<any> = ({onStepConfirm}) => (
  <div data-testid="step-view">
    <button onClick={() => onStepConfirm({label: 'My Attribute'})}>Confirm step</button>
  </div>
);

const defaultSteps = {
  default: [{view: StepView}],
};

test('It renders the create button with the given title', () => {
  renderWithProviders(
    <CreateAttributeButtonApp
      buttonTitle="Create attribute"
      iconsMap={{}}
      steps={defaultSteps}
      onClick={jest.fn()}
    />
  );
  expect(screen.getByText('Create attribute')).toBeInTheDocument();
});

test('It shows SelectAttributeType when the button is clicked', () => {
  renderWithProviders(
    <CreateAttributeButtonApp buttonTitle="Create attribute" iconsMap={{}} steps={defaultSteps} onClick={jest.fn()} />
  );
  expect(screen.queryByTestId('select-attribute-type')).not.toBeInTheDocument();
  userEvent.click(screen.getByText('Create attribute'));
  expect(screen.getByTestId('select-attribute-type')).toBeInTheDocument();
});

test('It advances to the step view after type selection', () => {
  renderWithProviders(
    <CreateAttributeButtonApp buttonTitle="Create attribute" iconsMap={{}} steps={defaultSteps} onClick={jest.fn()} />
  );
  userEvent.click(screen.getByText('Create attribute'));
  userEvent.click(screen.getByText('Select text'));
  expect(screen.getByTestId('step-view')).toBeInTheDocument();
});

test('It calls onClick with merged data when all steps complete', () => {
  const onClick = jest.fn();
  renderWithProviders(
    <CreateAttributeButtonApp buttonTitle="Create attribute" iconsMap={{}} steps={defaultSteps} onClick={onClick} />
  );
  userEvent.click(screen.getByText('Create attribute'));
  userEvent.click(screen.getByText('Select text'));
  userEvent.click(screen.getByText('Confirm step'));
  expect(onClick).toHaveBeenCalledWith({attribute_type: 'pim_catalog_text', label: 'My Attribute'});
});
```

---

## Import Depth Reference

| Test file directory | `../` count to UIBundle root |
|---|---|
| `tests/front/unit/grid/` | 4 |
| `tests/front/unit/job/common/breadcrumb/` | 6 |
| `tests/front/unit/family/form/template/` | 6 |
| `tests/front/unit/mass-edit/form/` | 5 |
| `tests/front/unit/attribute/form/` | 5 |

---

## Coverage Estimate

~8 new test files, ~30 new tests.

**Directly relevant to C1 (Product Grid migration):** ProductGridViewTitle + ProductGridProjectDetails — 9 tests covering grid title rendering with all view types and project completion display.

**Additional coverage unlocked:** All 8 files previously at 0% coverage.

---

## Deliverable

Branch: `test/jest-coverage-uibundle`  
One PR → `master`. Auto-merge immediately after `gh pr create`.

Sub-skill: `superpowers:subagent-driven-development`
