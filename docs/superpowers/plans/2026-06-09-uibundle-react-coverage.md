# UIBundle React Components Coverage — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Write unit tests for the 8 pure React components in UIBundle that currently have 0% coverage, as a safety net before the Phase C1 Backbone→React Product Grid migration.

**Architecture:** Each task creates one `*.unit.tsx` file in `src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/`. All tests use `renderWithProviders` from `@akeneo-pim-community/legacy-bridge/tests/front/unit/utils` — the same pattern used by the 20 existing UIBundle tests. No local Jest run (crashes the machine) — CI validates via the PR.

**Tech Stack:** React 18, @testing-library/react, @testing-library/user-event v12.8.3 (no `.setup()`), Jest, TypeScript.

---

## CRITICAL CONSTRAINTS

- **NEVER run `yarn test`, `jest`, or any Jest command locally.** It crashes the machine. Tests are validated by CI only.
- `translate()` returns the raw i18n key in tests (no override needed — `renderWithProviders` sets this up).
- `useRoute()` returns the route name string as-is in tests.
- All `userEvent` calls are direct (v12 API): `userEvent.click(el)` — never `await userEvent.setup().click()`.

---

## File Structure

| Task | File to Create | Source File |
|------|---------------|-------------|
| 0 | (branch creation) | — |
| 1 | `tests/front/unit/grid/ProductGridProjectDetails.unit.tsx` | `Resources/public/js/grid/ProductGridProjectDetails.tsx` |
| 2 | `tests/front/unit/grid/ProductGridViewTitle.unit.tsx` | `Resources/public/js/grid/ProductGridViewTitle.tsx` |
| 3 | `tests/front/unit/job/common/breadcrumb/JobBreadcrumb.unit.tsx` | `Resources/public/js/job/common/breadcrumb/JobBreadcrumb.tsx` |
| 4 | `tests/front/unit/family/form/template/FamilyTemplateSelector.unit.tsx` | `Resources/public/js/family/form/template/FamilyTemplateSelector.tsx` |
| 5 | `tests/front/unit/mass-edit/form/ChooseApp.unit.tsx` | `Resources/public/js/mass-edit/form/ChooseApp.tsx` |
| 6 | `tests/front/unit/attribute/form/CreateAttributeCodeAndLabel.unit.tsx` | `Resources/public/js/attribute/form/CreateAttributeCodeAndLabel.tsx` |
| 7 | `tests/front/unit/attribute/form/SelectAttributeType.unit.tsx` | `Resources/public/js/attribute/form/SelectAttributeType.tsx` |
| 8 | `tests/front/unit/attribute/form/CreateAttributeButtonApp.unit.tsx` | `Resources/public/js/attribute/form/CreateAttributeButtonApp.tsx` |
| 9 | (PR creation) | — |

All paths are relative to: `src/Akeneo/Platform/Bundle/UIBundle/`

---

## Task 0: Create the branch

- [ ] **Step 1: Create and checkout branch**

```bash
git checkout -b test/jest-coverage-uibundle
```

Expected: now on branch `test/jest-coverage-uibundle`.

---

## Task 1: ProductGridProjectDetails

**Files:**
- Create: `src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/grid/ProductGridProjectDetails.unit.tsx`

Source reads (for reference — do NOT run tests locally):
```tsx
// src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/grid/ProductGridProjectDetails.tsx
// Props: { projectDetails: { dueDateLabel: string; dueDate: string; completionRatio: number } }
// Renders: <Badge level={getLevel(completionRatio)}>{completionRatio} %</Badge>
//          {dueDateLabel}: {dueDate}
// getLevel: 0→'danger', 100→'primary', else→'warning'
// No useTranslate — dueDateLabel comes from props directly.
```

- [ ] **Step 1: Create the test file**

```tsx
import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
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

test('It renders dueDateLabel and dueDate from props', () => {
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

- [ ] **Step 2: Commit**

```bash
git add src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/grid/ProductGridProjectDetails.unit.tsx
git commit -m "test(uibundle): ProductGridProjectDetails unit tests"
```

---

## Task 2: ProductGridViewTitle

**Files:**
- Create: `src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/grid/ProductGridViewTitle.unit.tsx`

Source reads (for reference):
```tsx
// src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/grid/ProductGridViewTitle.tsx
// Props: { type: string; projectDetails?: {dueDateLabel, dueDate, completionRatio}; children: string }
// Renders children (view name), then:
//   if type === 'public' || type === 'view': ` (${translate('pim_common.public_view')})`
//   if projectDetails: <ProductGridProjectDetails projectDetails={projectDetails} />
```

- [ ] **Step 1: Create the test file**

```tsx
import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {ProductGridViewTitle} from '../../../../Resources/public/js/grid/ProductGridViewTitle';

test('It renders the view name from children', () => {
  renderWithProviders(<ProductGridViewTitle type="default">My View</ProductGridViewTitle>);
  expect(screen.getByText('My View')).toBeInTheDocument();
});

test('It shows the public label for type "public"', () => {
  renderWithProviders(<ProductGridViewTitle type="public">My View</ProductGridViewTitle>);
  expect(screen.getByText(/pim_common\.public_view/)).toBeInTheDocument();
});

test('It shows the public label for type "view"', () => {
  renderWithProviders(<ProductGridViewTitle type="view">My View</ProductGridViewTitle>);
  expect(screen.getByText(/pim_common\.public_view/)).toBeInTheDocument();
});

test('It hides the public label for non-public types', () => {
  renderWithProviders(<ProductGridViewTitle type="default">My View</ProductGridViewTitle>);
  expect(screen.queryByText(/pim_common\.public_view/)).not.toBeInTheDocument();
});

test('It renders project details when projectDetails prop is provided', () => {
  renderWithProviders(
    <ProductGridViewTitle
      type="default"
      projectDetails={{dueDateLabel: 'Due date', dueDate: '2026-06-30', completionRatio: 50}}
    >
      My View
    </ProductGridViewTitle>
  );
  expect(screen.getByText('Due date')).toBeInTheDocument();
  expect(screen.getByText('50 %')).toBeInTheDocument();
});
```

- [ ] **Step 2: Commit**

```bash
git add src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/grid/ProductGridViewTitle.unit.tsx
git commit -m "test(uibundle): ProductGridViewTitle unit tests"
```

---

## Task 3: JobBreadcrumb

**Files:**
- Create: `src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/job/common/breadcrumb/JobBreadcrumb.unit.tsx`

Source reads (for reference):
```tsx
// src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/job/common/breadcrumb/JobBreadcrumb.tsx
// Props: { isEdit: boolean; jobCode: string; jobLabel: string; jobType: string }
// Renders DSM <Breadcrumb> with:
//   - Step: translate(`pim_menu.tab.${jobType}s`)  (e.g. "pim_menu.tab.exports")
//   - Step: jobLabel
//   - Step (only if isEdit): translate('pim_common.edit')
```

- [ ] **Step 1: Create the test file**

```tsx
import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {JobBreadcrumb} from '../../../../../../Resources/public/js/job/common/breadcrumb/JobBreadcrumb';

test('It renders the job label', () => {
  renderWithProviders(
    <JobBreadcrumb isEdit={false} jobCode="my_export" jobLabel="My Export Job" jobType="export" />
  );
  expect(screen.getByText('My Export Job')).toBeInTheDocument();
});

test('It renders the job type i18n key', () => {
  renderWithProviders(
    <JobBreadcrumb isEdit={false} jobCode="my_export" jobLabel="My Export Job" jobType="export" />
  );
  expect(screen.getByText('pim_menu.tab.exports')).toBeInTheDocument();
});

test('It shows the edit step when isEdit is true', () => {
  renderWithProviders(
    <JobBreadcrumb isEdit={true} jobCode="my_export" jobLabel="My Export Job" jobType="export" />
  );
  expect(screen.getByText('pim_common.edit')).toBeInTheDocument();
});

test('It hides the edit step when isEdit is false', () => {
  renderWithProviders(
    <JobBreadcrumb isEdit={false} jobCode="my_export" jobLabel="My Export Job" jobType="export" />
  );
  expect(screen.queryByText('pim_common.edit')).not.toBeInTheDocument();
});
```

- [ ] **Step 2: Commit**

```bash
git add src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/job/common/breadcrumb/JobBreadcrumb.unit.tsx
git commit -m "test(uibundle): JobBreadcrumb unit tests"
```

---

## Task 4: FamilyTemplateSelector

**Files:**
- Create: `src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/family/form/template/FamilyTemplateSelector.unit.tsx`

Source reads (for reference):
```tsx
// src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/family/form/template/FamilyTemplateSelector.tsx
// Props: { close: () => void }
// Renders: <Modal id="template-selector" onClose={close} closeTitle={translate('pim_common.cancel')}></Modal>
// DSM Modal renders a close button with title={closeTitle}. No modal body content.
```

- [ ] **Step 1: Create the test file**

```tsx
import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {FamilyTemplateSelector} from '../../../../../../Resources/public/js/family/form/template/FamilyTemplateSelector';

test('It renders the modal close button', () => {
  renderWithProviders(<FamilyTemplateSelector close={jest.fn()} />);
  expect(screen.getByTitle('pim_common.cancel')).toBeInTheDocument();
});

test('It calls close when the close button is clicked', () => {
  const close = jest.fn();
  renderWithProviders(<FamilyTemplateSelector close={close} />);
  userEvent.click(screen.getByTitle('pim_common.cancel'));
  expect(close).toHaveBeenCalledTimes(1);
});
```

**Troubleshooting:** If `getByTitle('pim_common.cancel')` fails, the DSM Modal may render the close button as `aria-label` instead of `title`. Use `screen.getByRole('button', {name: 'pim_common.cancel'})` as fallback.

- [ ] **Step 2: Commit**

```bash
git add src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/family/form/template/FamilyTemplateSelector.unit.tsx
git commit -m "test(uibundle): FamilyTemplateSelector unit tests"
```

---

## Task 5: ChooseApp

**Files:**
- Create: `src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/mass-edit/form/ChooseApp.unit.tsx`

Source reads (for reference):
```tsx
// src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/mass-edit/form/ChooseApp.tsx
// Props: { operations: {code, label, icon}[]; selectedOperationCode?: string; onChange: (code) => void }
// State: currentOperationCode (useState, initialized from selectedOperationCode)
// Renders a <Tiles> with one <Tile> per operation.
// Tile selected={currentOperationCode === operation.code}
// On click: calls onChange(code) AND sets local state.
// getIcon(): maps 'icon-edit'→<EditIcon/>, 'icon-groups'→<GroupsIcon/>, etc. Unknown icon → <ExplanationPointIcon/> + console.warn
```

- [ ] **Step 1: Create the test file**

```tsx
import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {ChooseApp} from '../../../../../Resources/public/js/mass-edit/form/ChooseApp';

const operations = [
  {code: 'edit_common', label: 'Edit attributes', icon: 'icon-edit'},
  {code: 'add_to_group', label: 'Add to group', icon: 'icon-groups'},
];

test('It renders all operation labels', () => {
  renderWithProviders(<ChooseApp operations={operations} onChange={jest.fn()} />);
  expect(screen.getByText('Edit attributes')).toBeInTheDocument();
  expect(screen.getByText('Add to group')).toBeInTheDocument();
});

test('It calls onChange with the clicked operation code', () => {
  const onChange = jest.fn();
  renderWithProviders(<ChooseApp operations={operations} onChange={onChange} />);
  userEvent.click(screen.getByText('Edit attributes'));
  expect(onChange).toHaveBeenCalledWith('edit_common');
});

test('It renders with a pre-selected operation', () => {
  renderWithProviders(
    <ChooseApp operations={operations} selectedOperationCode="add_to_group" onChange={jest.fn()} />
  );
  expect(screen.getByText('Add to group')).toBeInTheDocument();
});

test('It renders without crash for an unknown icon', () => {
  const unknownOps = [{code: 'unknown', label: 'Unknown op', icon: 'icon-nonexistent'}];
  renderWithProviders(<ChooseApp operations={unknownOps} onChange={jest.fn()} />);
  expect(screen.getByText('Unknown op')).toBeInTheDocument();
});
```

- [ ] **Step 2: Commit**

```bash
git add src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/mass-edit/form/ChooseApp.unit.tsx
git commit -m "test(uibundle): ChooseApp unit tests"
```

---

## Task 6: CreateAttributeCodeAndLabel

**Files:**
- Create: `src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/attribute/form/CreateAttributeCodeAndLabel.unit.tsx`

Source reads (for reference):
```tsx
// src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/attribute/form/CreateAttributeCodeAndLabel.tsx
// 9 lines — thin wrapper: export const view = CreateAttributeCodeAndLabel
// Renders: <CreateAttributeModal {...props} /> from '@akeneo-pim-community/settings-ui'
// Props (CreateAttributeButtonStepProps): { onClose, onStepConfirm, initialData?, onBack?, children? }
```

- [ ] **Step 1: Create the test file**

```tsx
import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';

jest.mock('@akeneo-pim-community/settings-ui', () => ({
  CreateAttributeModal: ({onClose}: {onClose: () => void}) => (
    <div data-testid="create-attribute-modal">
      <button onClick={onClose}>Close modal</button>
    </div>
  ),
}));

import {view as CreateAttributeCodeAndLabel} from '../../../../../Resources/public/js/attribute/form/CreateAttributeCodeAndLabel';

test('It renders and delegates to CreateAttributeModal', () => {
  renderWithProviders(
    <CreateAttributeCodeAndLabel onClose={jest.fn()} onStepConfirm={jest.fn()} />
  );
  expect(screen.getByTestId('create-attribute-modal')).toBeInTheDocument();
});
```

- [ ] **Step 2: Commit**

```bash
git add src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/attribute/form/CreateAttributeCodeAndLabel.unit.tsx
git commit -m "test(uibundle): CreateAttributeCodeAndLabel unit tests"
```

---

## Task 7: SelectAttributeType

**Files:**
- Create: `src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/attribute/form/SelectAttributeType.unit.tsx`

Source reads (for reference):
```tsx
// src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/attribute/form/SelectAttributeType.tsx
// Imports: useRouter, useTranslate, useFeatureFlags from '@akeneo-pim-community/shared'
// Imports: useGetIdentifierAttributesCount from './hooks'
// useEffect: fetches Router.generate('pim_enrich_attribute_type_index') → attribute types list
//   filters reference_data types if feature flag disabled
//   sorts by translated label
//   setAttributeTypes(sorted)
// Renders: <Tile> per type, disabled when type==='pim_catalog_identifier' && count >= 10
// onClick Tile: calls onStepConfirm({attribute_type: type})
// Props: iconsMap, onStepConfirm, onClose + children (from CreateAttributeButtonStepProps)
```

Mocks needed:
- `global.fetch` — returns attribute type JSON
- `'../../../../../Resources/public/js/attribute/form/hooks'` — controls `useGetIdentifierAttributesCount`
- `'@akeneo-pim-community/shared'` — override `useFeatureFlags` and `useRouter` (spread `jest.requireActual` to keep `useTranslate` etc.)

**Important:** `jest.mock` calls are hoisted to the top of the file by Babel. The `mockUseGetIdentifierAttributesCount` variable declared before `jest.mock` is captured in the factory closure — this is intentional and correct.

- [ ] **Step 1: Create the test file**

```tsx
import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';

const mockUseGetIdentifierAttributesCount = jest.fn(() => ({count: 0}));

jest.mock('../../../../../Resources/public/js/attribute/form/hooks', () => ({
  useGetIdentifierAttributesCount: () => mockUseGetIdentifierAttributesCount(),
  useMainIdentifierCode: () => 'sku',
}));

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useFeatureFlags: () => ({isEnabled: () => true}),
  useRouter: () => ({generate: (url: string) => url}),
}));

import SelectAttributeType from '../../../../../Resources/public/js/attribute/form/SelectAttributeType';

afterEach(() => {
  global.fetch && (global.fetch as jest.Mock).mockClear();
  delete global.fetch;
  mockUseGetIdentifierAttributesCount.mockReturnValue({count: 0});
});

test('It renders attribute type tiles after fetching', async () => {
  global.fetch = jest.fn().mockResolvedValue({
    ok: true,
    json: () => Promise.resolve({pim_catalog_text: {}, pim_catalog_textarea: {}}),
  });

  renderWithProviders(
    <SelectAttributeType iconsMap={{}} onClose={jest.fn()} onStepConfirm={jest.fn()} />
  );

  await screen.findByText('pim_enrich.entity.attribute.property.type.pim_catalog_text');
  expect(screen.getByText('pim_enrich.entity.attribute.property.type.pim_catalog_textarea')).toBeInTheDocument();
});

test('It calls onStepConfirm with the chosen attribute type', async () => {
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

test('It disables the identifier tile when the limit of 10 is reached', async () => {
  mockUseGetIdentifierAttributesCount.mockReturnValue({count: 10});
  global.fetch = jest.fn().mockResolvedValue({
    ok: true,
    json: () => Promise.resolve({pim_catalog_identifier: {}, pim_catalog_text: {}}),
  });

  renderWithProviders(
    <SelectAttributeType iconsMap={{}} onClose={jest.fn()} onStepConfirm={jest.fn()} />
  );

  await screen.findByText('pim_enrich.entity.attribute.property.type.pim_catalog_identifier');
  // DSM Tile renders with aria-disabled="true" when disabled prop is true
  const identifierLabel = screen.getByText('pim_enrich.entity.attribute.property.type.pim_catalog_identifier');
  const tile = identifierLabel.closest('[aria-disabled="true"]');
  expect(tile).not.toBeNull();
});
```

**Troubleshooting for the disabled-tile test:** If `closest('[aria-disabled="true"]')` returns null, inspect the rendered markup (`screen.debug()`) to find the actual attribute the DSM Tile uses for disabled state. Alternatives: `getByRole('button', {name: /identifier/}).closest('[disabled]')`, or simply check the tooltip text appears: `screen.getByText('pim_enrich.entity.attribute.property.identifier_limit_reached_title')`.

- [ ] **Step 2: Commit**

```bash
git add src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/attribute/form/SelectAttributeType.unit.tsx
git commit -m "test(uibundle): SelectAttributeType unit tests"
```

---

## Task 8: CreateAttributeButtonApp

**Files:**
- Create: `src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/attribute/form/CreateAttributeButtonApp.unit.tsx`

Source reads (for reference):
```tsx
// src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/attribute/form/CreateAttributeButtonApp.tsx
// Props: { buttonTitle, iconsMap, steps, isModalOpen?, onClick, initialData? }
// State: isOpen (useBooleanState), attributeData (useState), currentStepIndex (useState, starts at -1)
// When isOpen:
//   if currentStepIndex === -1: renders <SelectAttributeType> (type selection step)
//   else: renders step component at steps[attributeData.attribute_type ?? 'default'][currentStepIndex]
// handleStepConfirm(data): merges data into attributeData, advances index or calls onClick+close if last step
// handleClose: resets to -1 / initialData, closes
// handleBack: decrements currentStepIndex
// Always renders: <Button id="attribute-create-button" onClick={open}>{buttonTitle}</Button>
```

Strategy: mock `SelectAttributeType` (default export) to avoid fetch dependency. Use a `StepView` test fixture to simulate a configurable step.

- [ ] **Step 1: Create the test file**

```tsx
import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';

jest.mock('../../../../../Resources/public/js/attribute/form/SelectAttributeType', () => ({
  default: ({onStepConfirm, onClose}: {onStepConfirm: (data: any) => void; onClose: () => void}) => (
    <div data-testid="select-attribute-type">
      <button onClick={() => onStepConfirm({attribute_type: 'pim_catalog_text'})}>Select text type</button>
      <button onClick={onClose}>Close selector</button>
    </div>
  ),
}));

jest.mock('@akeneo-pim-community/settings-ui', () => ({
  CreateAttributeProgressIndicator: () => null,
}));

import {CreateAttributeButtonApp} from '../../../../../Resources/public/js/attribute/form/CreateAttributeButtonApp';

const StepView: React.FC<any> = ({onStepConfirm, onClose}) => (
  <div data-testid="step-view">
    <button onClick={() => onStepConfirm({label: 'My Attribute'})}>Confirm step</button>
    <button onClick={onClose}>Cancel step</button>
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

test('It does not show SelectAttributeType before the button is clicked', () => {
  renderWithProviders(
    <CreateAttributeButtonApp
      buttonTitle="Create attribute"
      iconsMap={{}}
      steps={defaultSteps}
      onClick={jest.fn()}
    />
  );
  expect(screen.queryByTestId('select-attribute-type')).not.toBeInTheDocument();
});

test('It shows SelectAttributeType when the create button is clicked', () => {
  renderWithProviders(
    <CreateAttributeButtonApp
      buttonTitle="Create attribute"
      iconsMap={{}}
      steps={defaultSteps}
      onClick={jest.fn()}
    />
  );
  userEvent.click(screen.getByText('Create attribute'));
  expect(screen.getByTestId('select-attribute-type')).toBeInTheDocument();
});

test('It advances to the step view after type selection', () => {
  renderWithProviders(
    <CreateAttributeButtonApp
      buttonTitle="Create attribute"
      iconsMap={{}}
      steps={defaultSteps}
      onClick={jest.fn()}
    />
  );
  userEvent.click(screen.getByText('Create attribute'));
  userEvent.click(screen.getByText('Select text type'));
  expect(screen.getByTestId('step-view')).toBeInTheDocument();
});

test('It calls onClick with merged data when the last step is confirmed', () => {
  const onClick = jest.fn();
  renderWithProviders(
    <CreateAttributeButtonApp
      buttonTitle="Create attribute"
      iconsMap={{}}
      steps={defaultSteps}
      onClick={onClick}
    />
  );
  userEvent.click(screen.getByText('Create attribute'));
  userEvent.click(screen.getByText('Select text type'));
  userEvent.click(screen.getByText('Confirm step'));
  expect(onClick).toHaveBeenCalledWith({attribute_type: 'pim_catalog_text', label: 'My Attribute'});
});
```

- [ ] **Step 2: Commit**

```bash
git add src/Akeneo/Platform/Bundle/UIBundle/tests/front/unit/attribute/form/CreateAttributeButtonApp.unit.tsx
git commit -m "test(uibundle): CreateAttributeButtonApp unit tests"
```

---

## Task 9: Open PR and enable auto-merge

- [ ] **Step 1: Push branch**

```bash
git push -u origin test/jest-coverage-uibundle
```

- [ ] **Step 2: Create PR**

```bash
gh pr create \
  --title "test(uibundle): unit tests for 8 pure React components" \
  --body "$(cat <<'EOF'
## Summary

- Add 8 unit test files for UIBundle React components that previously had 0% coverage
- Grid: ProductGridProjectDetails, ProductGridViewTitle (C1 safety net)
- Job: JobBreadcrumb
- Family: FamilyTemplateSelector
- Mass-edit: ChooseApp
- Attribute form: CreateAttributeCodeAndLabel, SelectAttributeType, CreateAttributeButtonApp

## Test pattern

All tests use `renderWithProviders` from `@akeneo-pim-community/legacy-bridge/tests/front/unit/utils`, consistent with the 20 existing UIBundle unit tests.

## Test plan

- [ ] All 8 test files pass in CI Jest job
- [ ] No new TypeScript errors
EOF
)"
```

- [ ] **Step 3: Enable auto-merge**

```bash
gh pr merge <PR_NUMBER> --auto --squash
```

Replace `<PR_NUMBER>` with the number printed by `gh pr create`.

- [ ] **Step 4: Monitor CI**

```bash
gh run list --branch test/jest-coverage-uibundle --limit 5
```

Watch for the Jest job (`unit-front` or similar). If it fails, read logs with:
```bash
gh run view <RUN_ID> --log-failed
```

---

## Self-Review Notes

- **Spec coverage:** All 8 components from the spec have a dedicated task. ✅
- **No placeholders:** All test code is complete. Task 7 includes troubleshooting note for DSM Tile disabled state. ✅
- **Type consistency:** `CreateAttributeButtonStepProps` shape (`onClose`, `onStepConfirm`) used consistently in Tasks 6, 7, 8. ✅
- **Import depth:** Grid (4 `../`), breadcrumb/template (6 `../`), mass-edit/attribute (5 `../`). Matches spec table. ✅
- **userEvent:** All calls are direct v12 API (`userEvent.click(el)`) — no `.setup()`. ✅
- **No local Jest:** Plan explicitly forbids local test execution. CI validates via PR. ✅
