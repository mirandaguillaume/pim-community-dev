# DQI Coverage Phase 3 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add unit tests for 14 uncovered DQI front files: context providers, message builders, Axis/Criterion components, AllAttributesLink, Modal, and Table.

**Architecture:** Same as Phase 2 — relative imports (not workspace aliases) for Stryker tracing, `DependenciesProvider + ThemeProvider(pimTheme)` wrapper, `oro/translator` as `{virtual: true}` AMD mock. Tests live in the DQI module's `tests/front/unit/` tree mirroring `front/src/`.

**Tech Stack:** Jest 29, @testing-library/react (render + renderHook), @testing-library/user-event v12.8.3 (direct API, NO `userEvent.setup()`), styled-components + pimTheme.

---

## CRITICAL CONSTRAINTS (read before writing any test)

1. **Never run Jest locally** — push to branch and let CI validate.
2. **Import depths** — count `../` from the test file's directory back to the DQI module root, then append `front/src/`:
   - `Application/component/` → 5 levels: `../../../../../front/src/`
   - `Application/context/` → 5 levels: `../../../../../front/src/`
   - `Dashboard/Widgets/` → 7 levels: `../../../../../../../front/src/`
   - `Dashboard/KeyIndicators/` → 7 levels: `../../../../../../../front/src/`
   - `DataQualityInsights/` (direct) → 8 levels: `../../../../../../../../front/src/`
   - `DataQualityInsights/Axis/` → 9 levels: `../../../../../../../../../front/src/`
   - `DataQualityInsights/Criterion/` → 9 levels: `../../../../../../../../../front/src/`
3. **AMD virtual mocks** — `jest.mock('oro/translator', () => (key) => key, {virtual: true})` for `AxisError`, `AxisGradingInProgress`, `Title`, `AllAttributesLink`, `Modal`.
4. **userEvent v12** — call `userEvent.click(element)` directly (NO `userEvent.setup()`, NO `await`).
5. **Wrapper** — all `render()` calls use:
   ```tsx
   import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
   import {ThemeProvider} from 'styled-components';
   import {pimTheme} from 'akeneo-design-system';
   const renderWith = (ui: React.ReactElement) =>
     render(<DependenciesProvider><ThemeProvider theme={pimTheme}>{ui}</ThemeProvider></DependenciesProvider>);
   ```
6. **renderHook wrapper** — for context hooks that need providers:
   ```tsx
   const wrapper = ({children}: {children: React.ReactNode}) => (
     <DependenciesProvider><ThemeProvider theme={pimTheme}>{children}</ThemeProvider></DependenciesProvider>
   );
   const {result} = renderHook(() => hookUnderTest(), {wrapper});
   ```

---

## Task 0: Create feature branch

**Files:** none (git only)

- [ ] **Step 1: Create and push branch**

```bash
git checkout master && git pull origin master
git checkout -b test/jest-coverage-dqi-phase3
git push -u origin test/jest-coverage-dqi-phase3
```

---

## Task 1: AxisError + AxisGradingInProgress

**Files:**
- Source: `src/Akeneo/Pim/Automation/DataQualityInsights/front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Axis/AxisError.tsx`
- Source: `src/Akeneo/Pim/Automation/DataQualityInsights/front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Axis/AxisGradingInProgress.tsx`
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/Axis/AxisError.unit.tsx`
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/Axis/AxisGradingInProgress.unit.tsx`

Both components use `oro/translator` and render a single `<div>` with a translated message.

- [ ] **Step 1: Write AxisError.unit.tsx**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {AxisError} from '../../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Axis/AxisError';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('AxisError', () => {
  it('renders the axis error i18n key', () => {
    renderWith(<AxisError />);
    expect(
      screen.getByText('akeneo_data_quality_insights.product_evaluation.messages.error.axis_error')
    ).toBeInTheDocument();
  });
});
```

- [ ] **Step 2: Write AxisGradingInProgress.unit.tsx**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {AxisGradingInProgress} from '../../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Axis/AxisGradingInProgress';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('AxisGradingInProgress', () => {
  it('renders the grading in progress i18n key', () => {
    renderWith(<AxisGradingInProgress />);
    expect(
      screen.getByText(
        'akeneo_data_quality_insights.product_evaluation.messages.axis_grading_in_progress'
      )
    ).toBeInTheDocument();
  });
});
```

- [ ] **Step 3: Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/Axis/AxisError.unit.tsx \
        src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/Axis/AxisGradingInProgress.unit.tsx
git commit -m "test(dqi): AxisError + AxisGradingInProgress smoke tests"
```

---

## Task 2: Criterion/Icon + Criterion/Title

**Files:**
- Source: `src/.../DataQualityInsights/Criterion/Icon.tsx`
- Source: `src/.../DataQualityInsights/Criterion/Title.tsx`
- Create: `src/.../tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/Criterion/Icon.unit.tsx`
- Create: `src/.../tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/Criterion/Title.unit.tsx`

`Icon` clones each child element, injecting `width=20` and `height=20`. It wraps in a styled `<span>`. Test by passing an SVG child and asserting the injected dimensions.

`Title` renders a `<span className="CriterionRecommendationMessage">` containing a translated key (when `criterion` prop given) followed by `: `. When `criterion` is absent, renders only `: `.

- [ ] **Step 1: Write Icon.unit.tsx**

```tsx
import React from 'react';
import {render} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Icon} from '../../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Criterion/Icon';

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('Icon', () => {
  it('clones child element with width and height 20', () => {
    const {container} = renderWith(
      <Icon type="svg">
        <svg data-testid="inner-icon" />
      </Icon>
    );
    const svg = container.querySelector('svg');
    expect(svg).toHaveAttribute('width', '20');
    expect(svg).toHaveAttribute('height', '20');
  });

  it('renders without children', () => {
    const {container} = renderWith(<Icon type="svg" />);
    expect(container.querySelector('span')).toBeInTheDocument();
  });
});
```

- [ ] **Step 2: Write Title.unit.tsx**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Title} from '../../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Criterion/Title';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('Title', () => {
  it('renders the translated criterion key when criterion is provided', () => {
    renderWith(<Title criterion="completeness_of_required_attributes" />);
    expect(
      screen.getByText(
        'akeneo_data_quality_insights.product_evaluation.criteria.completeness_of_required_attributes.recommendation:',
        {exact: false}
      )
    ).toBeInTheDocument();
  });

  it('renders only separator when no criterion is given', () => {
    const {container} = renderWith(<Title />);
    expect(container.querySelector('.CriterionRecommendationMessage')).toBeInTheDocument();
  });
});
```

- [ ] **Step 3: Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/Criterion/Icon.unit.tsx \
        src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/Criterion/Title.unit.tsx
git commit -m "test(dqi): Criterion/Icon + Criterion/Title render tests"
```

---

## Task 3: AllAttributesLink

**Files:**
- Source: `src/.../DataQualityInsights/AllAttributesLink.tsx`
- Create: `src/.../tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/AllAttributesLink.unit.tsx`

`AllAttributesLink` dispatches `window.dispatchEvent(new CustomEvent(...))` on click. The event name depends on `axis` prop:
- `'enrichment'` → `'data-quality:product:filter_all_missing_attributes'`
- `'consistency'` → `'data-quality:product:filter_all_improvable_attributes'`

The `attributes` array is passed as `event.detail.attributes`. Spy on `window.dispatchEvent` to assert.

- [ ] **Step 1: Write AllAttributesLink.unit.tsx**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import AllAttributesLink from '../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/AllAttributesLink';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('AllAttributesLink', () => {
  let dispatchSpy: jest.SpyInstance;

  beforeEach(() => {
    dispatchSpy = jest.spyOn(window, 'dispatchEvent');
  });

  afterEach(() => {
    dispatchSpy.mockRestore();
  });

  it('dispatches filter_all_missing_attributes event for enrichment axis', () => {
    renderWith(<AllAttributesLink axis="enrichment" attributes={['name', 'description']} />);
    userEvent.click(screen.getByRole('presentation', {hidden: true}) || document.querySelector('span')!);
    expect(dispatchSpy).toHaveBeenCalledTimes(1);
    const event = dispatchSpy.mock.calls[0][0] as CustomEvent;
    expect(event.type).toBe('data-quality:product:filter_all_missing_attributes');
    expect(event.detail.attributes).toEqual(['name', 'description']);
  });

  it('dispatches filter_all_improvable_attributes event for consistency axis', () => {
    renderWith(<AllAttributesLink axis="consistency" attributes={['brand']} />);
    userEvent.click(document.querySelector('span.AknSubsection-comment')!);
    const event = dispatchSpy.mock.calls[0][0] as CustomEvent;
    expect(event.type).toBe('data-quality:product:filter_all_improvable_attributes');
    expect(event.detail.attributes).toEqual(['brand']);
  });

  it('renders the translated i18n key', () => {
    renderWith(<AllAttributesLink axis="enrichment" attributes={[]} />);
    expect(
      screen.getByText('akeneo_data_quality_insights.product_evaluation.axis.enrichment.attributes_link')
    ).toBeInTheDocument();
  });
});
```

**Note on click target:** `AllAttributesLink` renders a `<span onClick=...>`. Use `document.querySelector('span.AknSubsection-comment')` or `userEvent.click(screen.getByText('...'))` to trigger the click.

Simplify the click tests — use `screen.getByText(...)` as the click target since the span renders the i18n key:

```tsx
// Replace the click line in both click tests with:
userEvent.click(screen.getByText(`akeneo_data_quality_insights.product_evaluation.axis.enrichment.attributes_link`));
// and for consistency:
userEvent.click(screen.getByText(`akeneo_data_quality_insights.product_evaluation.axis.consistency.attributes_link`));
```

Final clean version:

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import AllAttributesLink from '../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/AllAttributesLink';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('AllAttributesLink', () => {
  let dispatchSpy: jest.SpyInstance;

  beforeEach(() => {
    dispatchSpy = jest.spyOn(window, 'dispatchEvent');
  });

  afterEach(() => {
    dispatchSpy.mockRestore();
  });

  it('renders the translated label', () => {
    renderWith(<AllAttributesLink axis="enrichment" attributes={[]} />);
    expect(
      screen.getByText('akeneo_data_quality_insights.product_evaluation.axis.enrichment.attributes_link')
    ).toBeInTheDocument();
  });

  it('dispatches filter_all_missing_attributes when axis is enrichment', () => {
    renderWith(<AllAttributesLink axis="enrichment" attributes={['name', 'description']} />);
    userEvent.click(
      screen.getByText('akeneo_data_quality_insights.product_evaluation.axis.enrichment.attributes_link')
    );
    const event = dispatchSpy.mock.calls[0][0] as CustomEvent;
    expect(event.type).toBe('data-quality:product:filter_all_missing_attributes');
    expect(event.detail.attributes).toEqual(['name', 'description']);
  });

  it('dispatches filter_all_improvable_attributes when axis is consistency', () => {
    renderWith(<AllAttributesLink axis="consistency" attributes={['brand']} />);
    userEvent.click(
      screen.getByText('akeneo_data_quality_insights.product_evaluation.axis.consistency.attributes_link')
    );
    const event = dispatchSpy.mock.calls[0][0] as CustomEvent;
    expect(event.type).toBe('data-quality:product:filter_all_improvable_attributes');
    expect(event.detail.attributes).toEqual(['brand']);
  });
});
```

- [ ] **Step 2: Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/AllAttributesLink.unit.tsx
git commit -m "test(dqi): AllAttributesLink custom event dispatch tests"
```

---

## Task 4: Modal

**Files:**
- Source: `src/.../application/component/Modal.tsx`
- Create: `src/.../tests/front/unit/Application/component/Modal.unit.tsx`

`Modal` renders a `data-testid="dqiModal"` container with title, subtitle, description, and an image. The save button (`data-testid="dqiValidateModal"`) is visually disabled (has `AknButton--disabled` class) when `enableSaveButton=false` and does NOT call `onConfirm`. The cancel `div` triggers `onDismissModal`.

- [ ] **Step 1: Write Modal.unit.tsx**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import Modal from '../../../../../front/src/application/component/Modal';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

const defaultProps = {
  cssClass: 'my-modal',
  title: 'My Title',
  subtitle: 'My Subtitle',
  description: 'My Description',
  illustrationLink: '/img/illustration.svg',
  modalContent: <span>content</span>,
  onConfirm: jest.fn(),
  onDismissModal: jest.fn(),
  enableSaveButton: true,
};

describe('Modal', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('renders title, subtitle and description', () => {
    renderWith(<Modal {...defaultProps} />);
    expect(screen.getByText('My Title')).toBeInTheDocument();
    expect(screen.getByText('My Subtitle')).toBeInTheDocument();
    expect(screen.getByText('My Description')).toBeInTheDocument();
  });

  it('calls onConfirm when save button is clicked and enabled', () => {
    renderWith(<Modal {...defaultProps} />);
    userEvent.click(screen.getByTestId('dqiValidateModal'));
    expect(defaultProps.onConfirm).toHaveBeenCalledTimes(1);
  });

  it('does not call onConfirm when save button is disabled', () => {
    renderWith(<Modal {...defaultProps} enableSaveButton={false} />);
    userEvent.click(screen.getByTestId('dqiValidateModal'));
    expect(defaultProps.onConfirm).not.toHaveBeenCalled();
  });

  it('save button has disabled class when enableSaveButton is false', () => {
    renderWith(<Modal {...defaultProps} enableSaveButton={false} />);
    expect(screen.getByTestId('dqiValidateModal')).toHaveClass('AknButton--disabled');
  });

  it('calls onDismissModal when cancel is clicked', () => {
    const {container} = renderWith(<Modal {...defaultProps} />);
    userEvent.click(container.querySelector('.AknFullPage-cancel')!);
    expect(defaultProps.onDismissModal).toHaveBeenCalledTimes(1);
  });
});
```

- [ ] **Step 2: Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Modal.unit.tsx
git commit -m "test(dqi): Modal props + callbacks tests"
```

---

## Task 5: Table compound (Table, Row, HeaderCell, Cell)

**Files:**
- Source: `src/.../application/component/Dashboard/Widgets/Table.tsx`
- Create: `src/.../tests/front/unit/Application/component/Dashboard/Widgets/Table.unit.tsx`

`Table` exports four sub-components: `Table`, `Row`, `HeaderCell`, `Cell`. All are purely structural (no AMD deps, no i18n). Use `DependenciesProvider + ThemeProvider` wrapper for consistency.

- [ ] **Step 1: Write Table.unit.tsx**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Table, Row, HeaderCell, Cell} from '../../../../../../../front/src/application/component/Dashboard/Widgets/Table';

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('Table', () => {
  it('renders children inside a tbody', () => {
    renderWith(
      <Table>
        <Row><Cell>content</Cell></Row>
      </Table>
    );
    expect(screen.getByText('content')).toBeInTheDocument();
    expect(document.querySelector('tbody')).toBeInTheDocument();
  });
});

describe('Row', () => {
  it('has AknGrid-bodyRow class when isHeader is false (default)', () => {
    const {container} = renderWith(<table><tbody><Row><td>r</td></Row></tbody></table>);
    expect(container.querySelector('tr')).toHaveClass('AknGrid-bodyRow');
  });

  it('has no AknGrid-bodyRow class when isHeader is true', () => {
    const {container} = renderWith(<table><tbody><Row isHeader><td>r</td></Row></tbody></table>);
    expect(container.querySelector('tr')).not.toHaveClass('AknGrid-bodyRow');
  });
});

describe('Cell', () => {
  it('applies action class when action=true', () => {
    const {container} = renderWith(
      <table><tbody><tr><Cell action>x</Cell></tr></tbody></table>
    );
    expect(container.querySelector('td')).toHaveClass('AknGrid-bodyCell--actions');
  });

  it('applies highlight class when highlight=true', () => {
    const {container} = renderWith(
      <table><tbody><tr><Cell highlight>x</Cell></tr></tbody></table>
    );
    expect(container.querySelector('td')).toHaveClass('AknGrid-bodyCell--highlight');
  });
});

describe('HeaderCell', () => {
  it('renders children in a th with AknGrid-headerCell class', () => {
    const {container} = renderWith(
      <table><tbody><tr><HeaderCell>Head</HeaderCell></tr></tbody></table>
    );
    expect(container.querySelector('th')).toHaveClass('AknGrid-headerCell');
    expect(screen.getByText('Head')).toBeInTheDocument();
  });
});
```

- [ ] **Step 2: Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/Widgets/Table.unit.tsx
git commit -m "test(dqi): Table + Row + HeaderCell + Cell render tests"
```

---

## Task 6: messageBuilder pure function

**Files:**
- Source: `src/.../Dashboard/KeyIndicators/messageBuilder.tsx`
- Create: `src/.../tests/front/unit/Application/component/Dashboard/KeyIndicators/messageBuilder.unit.tsx`

`messageBuilder` is a curried pure function: `messageBuilder(mapping)(source)` splits `source` on spaces and replaces known markers with their mapped JSX, wrapping others in `<span>`.

- [ ] **Step 1: Write messageBuilder.unit.tsx**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {messageBuilder} from '../../../../../../../front/src/application/component/Dashboard/KeyIndicators/messageBuilder';

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('messageBuilder', () => {
  it('replaces a known marker with the mapped JSX element', () => {
    const mapping = {
      '<count_link/>': <button>42 products</button>,
    };
    renderWith(messageBuilder(mapping)('You have <count_link/> to fix'));
    expect(screen.getByRole('button', {name: '42 products'})).toBeInTheDocument();
  });

  it('wraps unknown words in span elements', () => {
    renderWith(messageBuilder({})('hello world'));
    expect(screen.getByText('hello')).toBeInTheDocument();
    expect(screen.getByText('world')).toBeInTheDocument();
  });

  it('renders both markers and plain words in a single source string', () => {
    const mapping = {
      '<link/>': <a href="#">click here</a>,
    };
    renderWith(messageBuilder(mapping)('Please <link/> to continue'));
    expect(screen.getByRole('link', {name: 'click here'})).toBeInTheDocument();
    expect(screen.getByText('Please')).toBeInTheDocument();
    expect(screen.getByText('to')).toBeInTheDocument();
  });
});
```

- [ ] **Step 2: Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/KeyIndicators/messageBuilder.unit.tsx
git commit -m "test(dqi): messageBuilder pure function tests"
```

---

## Task 7: AttributeMessageBuilder + ProductMessageBuilder

**Files:**
- Source: `src/.../KeyIndicators/AttributeMessageBuilder.tsx`
- Source: `src/.../KeyIndicators/ProductMessageBuilder.tsx`
- Create: `src/.../tests/front/unit/Application/component/Dashboard/KeyIndicators/AttributeMessageBuilder.unit.tsx`
- Create: `src/.../tests/front/unit/Application/component/Dashboard/KeyIndicators/ProductMessageBuilder.unit.tsx`

Both use `useTranslate` from `@akeneo-pim-community/shared` (provided by `DependenciesProvider`) and `roughCount` helper. Both return `null` when counts are zero.

`roughCount`: returns the count as-is when < 200 (enough for tests).

`Counts` shape: `{totalGood: number, totalToImprove: number}`.
`CountsByProductType` shape: `{products?: Counts, product_models?: Counts}`.

- [ ] **Step 1: Write AttributeMessageBuilder.unit.tsx**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {AttributeMessageBuilder} from '../../../../../../../front/src/application/component/Dashboard/KeyIndicators/AttributeMessageBuilder';

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('AttributeMessageBuilder', () => {
  it('renders nothing when totalToImprove is 0', () => {
    const {container} = renderWith(
      <AttributeMessageBuilder counts={{totalGood: 5, totalToImprove: 0}} onClick={jest.fn()} />
    );
    expect(container.firstChild).toBeNull();
  });

  it('renders a button when totalToImprove is greater than 0', () => {
    renderWith(
      <AttributeMessageBuilder counts={{totalGood: 5, totalToImprove: 10}} onClick={jest.fn()} />
    );
    expect(screen.getByRole('button')).toBeInTheDocument();
  });

  it('calls onClick when the button is clicked', () => {
    const onClick = jest.fn();
    renderWith(
      <AttributeMessageBuilder counts={{totalGood: 5, totalToImprove: 10}} onClick={onClick} />
    );
    screen.getByRole('button').click();
    expect(onClick).toHaveBeenCalledTimes(1);
  });
});
```

- [ ] **Step 2: Write ProductMessageBuilder.unit.tsx**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ProductMessageBuilder} from '../../../../../../../front/src/application/component/Dashboard/KeyIndicators/ProductMessageBuilder';

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

const makeCounts = (totalToImprove: number) => ({totalGood: 0, totalToImprove});

describe('ProductMessageBuilder', () => {
  it('renders nothing when both products and product_models are 0', () => {
    const {container} = renderWith(
      <ProductMessageBuilder
        counts={{products: makeCounts(0), product_models: makeCounts(0)}}
        onClickOnProducts={jest.fn()}
        onClickOnProductModels={jest.fn()}
      />
    );
    expect(container.firstChild).toBeNull();
  });

  it('renders one button when only products have a non-zero count', () => {
    renderWith(
      <ProductMessageBuilder
        counts={{products: makeCounts(5), product_models: makeCounts(0)}}
        onClickOnProducts={jest.fn()}
        onClickOnProductModels={jest.fn()}
      />
    );
    expect(screen.getAllByRole('button')).toHaveLength(1);
  });

  it('renders two buttons when both products and product_models are non-zero', () => {
    renderWith(
      <ProductMessageBuilder
        counts={{products: makeCounts(5), product_models: makeCounts(3)}}
        onClickOnProducts={jest.fn()}
        onClickOnProductModels={jest.fn()}
      />
    );
    expect(screen.getAllByRole('button')).toHaveLength(2);
  });

  it('calls onClickOnProducts when the products button is clicked', () => {
    const onClickOnProducts = jest.fn();
    renderWith(
      <ProductMessageBuilder
        counts={{products: makeCounts(5), product_models: makeCounts(0)}}
        onClickOnProducts={onClickOnProducts}
        onClickOnProductModels={jest.fn()}
      />
    );
    screen.getAllByRole('button')[0].click();
    expect(onClickOnProducts).toHaveBeenCalledTimes(1);
  });
});
```

- [ ] **Step 3: Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/KeyIndicators/AttributeMessageBuilder.unit.tsx \
        src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/KeyIndicators/ProductMessageBuilder.unit.tsx
git commit -m "test(dqi): AttributeMessageBuilder + ProductMessageBuilder tests"
```

---

## Task 8: AxesContext + KeyIndicatorsContext

**Files:**
- Source: `src/.../application/context/AxesContext.tsx`
- Source: `src/.../application/context/KeyIndicatorsContext.tsx`
- Create: `src/.../tests/front/unit/Application/context/AxesContext.unit.tsx`
- Create: `src/.../tests/front/unit/Application/context/KeyIndicatorsContext.unit.tsx`

Both are simple contexts with no hook dependencies in the Provider. `AxesContext` default: `{axes: []}`. `KeyIndicatorsContext` default: `{tips: {}}`.

- [ ] **Step 1: Write AxesContext.unit.tsx**

```tsx
import React from 'react';
import {renderHook} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {
  useAxesContext,
  AxesContextProvider,
} from '../../../../../front/src/application/context/AxesContext';

const wrapper = ({children}: {children: React.ReactNode}) => (
  <DependenciesProvider>
    <ThemeProvider theme={pimTheme}>{children}</ThemeProvider>
  </DependenciesProvider>
);

describe('AxesContext', () => {
  it('returns default axes as empty array when no Provider', () => {
    const {result} = renderHook(() => useAxesContext(), {wrapper});
    expect(result.current.axes).toEqual([]);
  });

  it('AxesContextProvider passes axes to consumers', () => {
    const providerWrapper = ({children}: {children: React.ReactNode}) => (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AxesContextProvider axes={['enrichment', 'consistency']}>{children}</AxesContextProvider>
        </ThemeProvider>
      </DependenciesProvider>
    );
    const {result} = renderHook(() => useAxesContext(), {wrapper: providerWrapper});
    expect(result.current.axes).toEqual(['enrichment', 'consistency']);
  });
});
```

- [ ] **Step 2: Write KeyIndicatorsContext.unit.tsx**

```tsx
import React from 'react';
import {renderHook} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {
  useKeyIndicatorsContext,
  KeyIndicatorsProvider,
} from '../../../../../front/src/application/context/KeyIndicatorsContext';

const wrapper = ({children}: {children: React.ReactNode}) => (
  <DependenciesProvider>
    <ThemeProvider theme={pimTheme}>{children}</ThemeProvider>
  </DependenciesProvider>
);

describe('KeyIndicatorsContext', () => {
  it('returns default tips as empty object when no Provider', () => {
    const {result} = renderHook(() => useKeyIndicatorsContext(), {wrapper});
    expect(result.current.tips).toEqual({});
  });

  it('KeyIndicatorsProvider passes tips to consumers', () => {
    const tips = {has_image: [{message: 'tip1', icon: 'info'}]};
    const providerWrapper = ({children}: {children: React.ReactNode}) => (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <KeyIndicatorsProvider tips={tips}>{children}</KeyIndicatorsProvider>
        </ThemeProvider>
      </DependenciesProvider>
    );
    const {result} = renderHook(() => useKeyIndicatorsContext(), {wrapper: providerWrapper});
    expect(result.current.tips).toEqual(tips);
  });
});
```

- [ ] **Step 3: Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/context/AxesContext.unit.tsx \
        src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/context/KeyIndicatorsContext.unit.tsx
git commit -m "test(dqi): AxesContext + KeyIndicatorsContext default + provider tests"
```

---

## Task 9: AttributeGroupsStatusContext + DashboardContext

**Files:**
- Source: `src/.../context/AttributeGroupsStatusContext.tsx`
- Source: `src/.../context/DashboardContext.tsx`
- Create: `src/.../tests/front/unit/Application/context/AttributeGroupsStatusContext.unit.tsx`
- Create: `src/.../tests/front/unit/Application/context/DashboardContext.unit.tsx`

**AttributeGroupsStatusContext:** The Provider calls `useFetchAllAttributeGroupsStatus()` — mock it. The default context value is `{load: () => {}, status: {}}`.

**DashboardContext:** `useDashboardContext()` throws `'[DashboardContext]: dashboard context has not been properly initiated'` when called outside a Provider. The Provider calls `useInitDashboardContextState(familyCode, category)` — mock it.

- [ ] **Step 1: Write AttributeGroupsStatusContext.unit.tsx**

```tsx
import React from 'react';
import {render, renderHook} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {
  AttributeGroupsStatusProvider,
  useAttributeGroupsStatusContext,
} from '../../../../../front/src/application/context/AttributeGroupsStatusContext';

jest.mock(
  '../../../../../front/src/infrastructure/hooks/AttributeGroup/useFetchAllAttributeGroupsStatus',
  () => ({
    useFetchAllAttributeGroupsStatus: jest.fn().mockReturnValue({
      load: jest.fn(),
      status: {outdoor: true, marketing: false},
    }),
  })
);

const wrapper = ({children}: {children: React.ReactNode}) => (
  <DependenciesProvider>
    <ThemeProvider theme={pimTheme}>{children}</ThemeProvider>
  </DependenciesProvider>
);

describe('AttributeGroupsStatusContext', () => {
  it('returns default status as empty object when no Provider', () => {
    const {result} = renderHook(() => useAttributeGroupsStatusContext(), {wrapper});
    expect(result.current.status).toEqual({});
  });

  it('Provider renders children', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AttributeGroupsStatusProvider>
            <span data-testid="child">ok</span>
          </AttributeGroupsStatusProvider>
        </ThemeProvider>
      </DependenciesProvider>
    );
    expect(document.querySelector('[data-testid="child"]')).toBeInTheDocument();
  });

  it('Provider exposes the mocked status', () => {
    const providerWrapper = ({children}: {children: React.ReactNode}) => (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AttributeGroupsStatusProvider>{children}</AttributeGroupsStatusProvider>
        </ThemeProvider>
      </DependenciesProvider>
    );
    const {result} = renderHook(() => useAttributeGroupsStatusContext(), {wrapper: providerWrapper});
    expect(result.current.status).toEqual({outdoor: true, marketing: false});
  });
});
```

- [ ] **Step 2: Write DashboardContext.unit.tsx**

```tsx
import React from 'react';
import {renderHook} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {
  DashboardContextProvider,
  useDashboardContext,
} from '../../../../../front/src/application/context/DashboardContext';

const mockUpdateDashboardFilters = jest.fn();

jest.mock('../../../../../front/src/infrastructure/hooks/useInitDashboardContextState', () => ({
  useInitDashboardContextState: jest.fn().mockReturnValue({
    familyCode: 'mugs',
    category: null,
    updateDashboardFilters: mockUpdateDashboardFilters,
  }),
}));

describe('DashboardContext', () => {
  it('useDashboardContext throws when called outside a Provider', () => {
    const spy = jest.spyOn(console, 'error').mockImplementation(() => {});
    expect(() => {
      renderHook(() => useDashboardContext());
    }).toThrow('[DashboardContext]: dashboard context has not been properly initiated');
    spy.mockRestore();
  });

  it('Provider renders children and exposes the context value', () => {
    const providerWrapper = ({children}: {children: React.ReactNode}) => (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <DashboardContextProvider familyCode={null} category={null}>
            {children}
          </DashboardContextProvider>
        </ThemeProvider>
      </DependenciesProvider>
    );
    const {result} = renderHook(() => useDashboardContext(), {wrapper: providerWrapper});
    expect(result.current.familyCode).toBe('mugs');
    expect(result.current.category).toBeNull();
  });
});
```

- [ ] **Step 3: Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/context/AttributeGroupsStatusContext.unit.tsx \
        src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/context/DashboardContext.unit.tsx
git commit -m "test(dqi): AttributeGroupsStatusContext + DashboardContext provider + guard tests"
```

---

## Task 10: Open PR + auto-merge

**Files:** none (git + GitHub CLI)

- [ ] **Step 1: Open the PR**

```bash
gh pr create \
  --base master \
  --head test/jest-coverage-dqi-phase3 \
  --title "test(dqi): cover context providers + message builders + Axis/Criterion/Modal/Table" \
  --body "$(cat <<'EOF'
## Summary
- Adds 14 unit-test files for DQI front components not yet covered after Phase 2 (PR #258)
- Tier 1: AxisError, AxisGradingInProgress, Criterion/Icon, Criterion/Title (smoke + translate mock)
- Tier 2: AllAttributesLink (CustomEvent spy), Modal (props + callbacks), Table (compound sub-components)
- Tier 3: messageBuilder (pure function)
- Tier 4: AttributeMessageBuilder, ProductMessageBuilder (count-based rendering)
- Tier 5: AxesContext, KeyIndicatorsContext, AttributeGroupsStatusContext, DashboardContext (default values + Provider)

## Test plan
- [ ] CI `test-front-unit` green (both shards)
- [ ] CI `lint-front` green
- [ ] 0 new test failures
EOF
)"
```

- [ ] **Step 2: Enable auto-merge immediately**

```bash
# Get the PR number from the URL printed above, then:
gh pr merge <PR_NUMBER> --auto --squash
```

- [ ] **Step 3: Monitor CI**

```bash
gh pr checks <PR_NUMBER> --watch
```
