# DQI Component Coverage — Phase 2 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add 17 unit-test files covering the DQI front module's untested components and pure logic, raising statement coverage by ~4-6 pp.

**Architecture:** One test file per source file; pure-logic first (EvaluationHelper), then components grouped by dependency pattern (useTranslate → pim/router → oro/translator → complex mocks). All tests use relative imports so Stryker can trace per-test coverage. Wrapper: `DependenciesProvider` + `ThemeProvider(pimTheme)` for every component test.

**Tech Stack:** React Testing Library (`@testing-library/react`, `@testing-library/user-event`), Jest, TypeScript, `DependenciesProvider` (`@akeneo-pim-community/legacy-bridge`), `pimTheme` (`akeneo-design-system`).

---

## Paths reference

All test files live under:
`src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/`

All source files live under:
`src/Akeneo/Pim/Automation/DataQualityInsights/front/src/`

Relative import from `tests/front/unit/Application/component/` → source = `../../../../../../front/src/application/component/`

Relative import from `tests/front/unit/Application/helper/` → source = `../../../../../../front/src/application/helper/`

Relative import from `tests/front/unit/Application/component/Dashboard/KeyIndicators/` → source = `../../../../../../../front/src/application/component/Dashboard/KeyIndicators/`

Relative import from `tests/front/unit/Application/component/Dashboard/Widgets/` → source = `../../../../../../../front/src/application/component/Dashboard/Widgets/`

Relative import from `tests/front/unit/Application/component/AttributeGroup/` → source = `../../../../../../front/src/application/component/AttributeGroup/`

Relative import from `tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/` → source = `../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/`

Relative import from `tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/Axis/` → source = `../../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Axis/`

---

## Reusable wrapper (reference — do NOT create a file)

Every component test wraps the component in:

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

// usage:
render(
  <DependenciesProvider>
    <ThemeProvider theme={pimTheme}>
      <MyComponent {...props} />
    </ThemeProvider>
  </DependenciesProvider>
);
```

---

## Task 0: Create feature branch

- [ ] **Create the branch**

```bash
git checkout -b test/jest-coverage-dqi-components
```

---

## Task 1: EvaluationHelper — pure function

**Files:**
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/helper/EvaluationHelper.unit.ts`

- [ ] **Write the test**

```ts
import {evaluationPlaceholder, convertEvaluationToLegacyFormat} from '../../../../../../front/src/application/helper/EvaluationHelper';

describe('evaluationPlaceholder', () => {
  it('has null rate and empty criteria', () => {
    expect(evaluationPlaceholder.rate.value).toBeNull();
    expect(evaluationPlaceholder.rate.rank).toBeNull();
    expect(evaluationPlaceholder.criteria).toEqual([]);
  });
});

describe('convertEvaluationToLegacyFormat', () => {
  it('returns empty object when axes is empty', () => {
    const result = convertEvaluationToLegacyFormat(
      {},
      {ecommerce: {en_US: [{code: 'criterion_1', rate: {value: 80, rank: 'B'}, status: 'done', improvable_attributes: []}]}}
    );
    expect(result).toEqual({});
  });

  it('returns empty axis entries when productEvaluation is empty', () => {
    const result = convertEvaluationToLegacyFormat({enrichment: ['criterion_1']}, {});
    expect(result).toEqual({});
  });

  it('distributes criteria to the correct axis', () => {
    const axes = {enrichment: ['criterion_1'], consistency: ['criterion_2']};
    const evaluation = {
      ecommerce: {
        en_US: [
          {code: 'criterion_1', rate: {value: 80, rank: 'B'}, status: 'done' as const, improvable_attributes: ['attr_a']},
          {code: 'criterion_2', rate: {value: 60, rank: 'C'}, status: 'done' as const, improvable_attributes: []},
        ],
      },
    };

    const result = convertEvaluationToLegacyFormat(axes, evaluation);

    expect(result.enrichment.ecommerce.en_US.criteria).toHaveLength(1);
    expect(result.enrichment.ecommerce.en_US.criteria[0].code).toBe('criterion_1');
    expect(result.consistency.ecommerce.en_US.criteria).toHaveLength(1);
    expect(result.consistency.ecommerce.en_US.criteria[0].code).toBe('criterion_2');
  });

  it('drops criteria that belong to no axis', () => {
    const axes = {enrichment: ['criterion_1']};
    const evaluation = {
      ecommerce: {
        en_US: [
          {code: 'criterion_1', rate: {value: 80, rank: 'B'}, status: 'done' as const, improvable_attributes: []},
          {code: 'unknown_criterion', rate: {value: 50, rank: 'C'}, status: 'done' as const, improvable_attributes: []},
        ],
      },
    };

    const result = convertEvaluationToLegacyFormat(axes, evaluation);

    expect(result.enrichment.ecommerce.en_US.criteria).toHaveLength(1);
    expect(result.enrichment.ecommerce.en_US.criteria[0].code).toBe('criterion_1');
  });

  it('sets rate to null on the output for each locale', () => {
    const axes = {enrichment: ['criterion_1']};
    const evaluation = {
      ecommerce: {
        en_US: [{code: 'criterion_1', rate: {value: 80, rank: 'B'}, status: 'done' as const, improvable_attributes: []}],
      },
    };

    const result = convertEvaluationToLegacyFormat(axes, evaluation);

    expect(result.enrichment.ecommerce.en_US.rate).toBeNull();
  });

  it('handles multiple channels and locales independently', () => {
    const axes = {enrichment: ['criterion_1']};
    const criterion = {code: 'criterion_1', rate: {value: 80, rank: 'B'}, status: 'done' as const, improvable_attributes: []};
    const evaluation = {
      ecommerce: {en_US: [criterion], fr_FR: [criterion]},
      print: {en_US: [criterion]},
    };

    const result = convertEvaluationToLegacyFormat(axes, evaluation);

    expect(result.enrichment.ecommerce.en_US.criteria).toHaveLength(1);
    expect(result.enrichment.ecommerce.fr_FR.criteria).toHaveLength(1);
    expect(result.enrichment.print.en_US.criteria).toHaveLength(1);
  });
});
```

- [ ] **Verify via CI** — push the branch and check the `test-front-unit` CI job passes with the new test file.

- [ ] **Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/helper/EvaluationHelper.unit.ts
git commit -m "test(dqi): EvaluationHelper — convertEvaluationToLegacyFormat + placeholder"
```

---

## Task 2: Pure render — QualityScoreLoader + EmptyChartPlaceholder

**Files:**
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/QualityScoreLoader.unit.tsx`
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/EmptyChartPlaceholder.unit.tsx`

- [ ] **Write QualityScoreLoader test**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {QualityScoreLoader} from '../../../../../../front/src/application/component/QualityScoreLoader';

test('it renders the quality score loader skeleton', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <QualityScoreLoader />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByTestId('quality-score-loader')).toBeInTheDocument();
});
```

- [ ] **Write EmptyChartPlaceholder test**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {EmptyChartPlaceholder} from '../../../../../../../front/src/application/component/Dashboard/EmptyChartPlaceholder';

test('it renders both placeholder i18n messages', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <EmptyChartPlaceholder />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('akeneo_data_quality_insights.dqi_dashboard.no_data_title')).toBeInTheDocument();
  expect(screen.getByText('akeneo_data_quality_insights.dqi_dashboard.no_data_subtitle')).toBeInTheDocument();
});
```

- [ ] **Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/QualityScoreLoader.unit.tsx
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/EmptyChartPlaceholder.unit.tsx
git commit -m "test(dqi): QualityScoreLoader + EmptyChartPlaceholder smoke tests"
```

---

## Task 3: useTranslate i18n — EmptyKeyIndicators + QualityScorePending

**Files:**
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/KeyIndicators/EmptyKeyIndicators.unit.tsx`
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/QualityScorePending.unit.tsx`

- [ ] **Write EmptyKeyIndicators test**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {EmptyKeyIndicators} from '../../../../../../../front/src/application/component/Dashboard/KeyIndicators/EmptyKeyIndicators';

test('it renders both empty-state i18n messages', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <EmptyKeyIndicators />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(
    screen.getByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.no_data')
  ).toBeInTheDocument();
  expect(
    screen.getByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.no_data_subtitle')
  ).toBeInTheDocument();
});
```

- [ ] **Write QualityScorePending test**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {QualityScorePending} from '../../../../../../front/src/application/component/QualityScorePending';

test('it renders the pending badge with its i18n key', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <QualityScorePending />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByTestId('quality-score-pending')).toBeInTheDocument();
  expect(
    screen.getByText('akeneo_data_quality_insights.quality_score.pending')
  ).toBeInTheDocument();
});
```

- [ ] **Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/KeyIndicators/EmptyKeyIndicators.unit.tsx
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/QualityScorePending.unit.tsx
git commit -m "test(dqi): EmptyKeyIndicators + QualityScorePending i18n smoke tests"
```

---

## Task 4: Props + translate — KeyIndicatorNoData + SectionTitle

**Files:**
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/KeyIndicators/KeyIndicatorNoData.unit.tsx`
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/KeyIndicators/SectionTitle.unit.tsx`

- [ ] **Write KeyIndicatorNoData test**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {KeyIndicatorNoData} from '../../../../../../../front/src/application/component/Dashboard/KeyIndicators/KeyIndicatorNoData';

test('it renders the no-data message for a given indicator type', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <KeyIndicatorNoData type="has_image" title="akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title">
          <span data-testid="icon">icon</span>
        </KeyIndicatorNoData>
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(
    screen.getByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.no_data')
  ).toBeInTheDocument();
  expect(
    screen.getByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title')
  ).toBeInTheDocument();
  expect(screen.getByTestId('icon')).toBeInTheDocument();
});
```

- [ ] **Write SectionTitle test**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {SectionTitle} from '../../../../../../../front/src/application/component/Dashboard/KeyIndicators/SectionTitle';

test('it renders the translated section title', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <SectionTitle title="my_i18n_key" />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('my_i18n_key')).toBeInTheDocument();
});

test('it renders children alongside the title', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <SectionTitle title="my_i18n_key">
          <button>action</button>
        </SectionTitle>
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByRole('button', {name: 'action'})).toBeInTheDocument();
});
```

- [ ] **Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/KeyIndicators/KeyIndicatorNoData.unit.tsx
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/KeyIndicators/SectionTitle.unit.tsx
git commit -m "test(dqi): KeyIndicatorNoData + SectionTitle render tests"
```

---

## Task 5: BackLinkButton — Router mock + click

**Files:**
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/BackLinkButton.unit.tsx`

- [ ] **Write the test**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

jest.mock('pim/router', () => ({redirectToRoute: jest.fn()}), {virtual: true});

import {BackLinkButton} from '../../../../../../front/src/application/component/BackLinkButton';

describe('BackLinkButton', () => {
  beforeEach(() => {
    const Router = require('pim/router');
    Router.redirectToRoute.mockClear();
  });

  it('renders with the given label', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <BackLinkButton label="Go back" route="pim_enrich_product_index" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(screen.getByRole('button', {name: 'Go back'})).toBeInTheDocument();
  });

  it('calls Router.redirectToRoute with the correct route on click', async () => {
    const user = userEvent.setup();
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <BackLinkButton label="Go back" route="pim_enrich_product_index" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    await user.click(screen.getByRole('button', {name: 'Go back'}));

    const Router = require('pim/router');
    expect(Router.redirectToRoute).toHaveBeenCalledWith('pim_enrich_product_index', undefined);
  });

  it('passes routeParams to Router.redirectToRoute', async () => {
    const user = userEvent.setup();
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <BackLinkButton label="Go back" route="pim_enrich_product_index" routeParams={[]} />
        </ThemeProvider>
      </DependenciesProvider>
    );

    await user.click(screen.getByRole('button', {name: 'Go back'}));

    const Router = require('pim/router');
    expect(Router.redirectToRoute).toHaveBeenCalledWith('pim_enrich_product_index', []);
  });
});
```

- [ ] **Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/BackLinkButton.unit.tsx
git commit -m "test(dqi): BackLinkButton — render + Router redirect on click"
```

---

## Task 6: Callback buttons — RemoveItem + AddItem + SeeInGrid

**Files:**
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/Widgets/RemoveItem.unit.tsx`
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/Widgets/AddItem.unit.tsx`
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/Widgets/SeeInGrid.unit.tsx`

- [ ] **Write RemoveItem test**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {RemoveItem} from '../../../../../../../front/src/application/component/Dashboard/Widgets/RemoveItem';

test('it calls the remove callback when the close icon is clicked', async () => {
  const handleRemove = jest.fn();
  const user = userEvent.setup();

  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <RemoveItem remove={handleRemove} />
      </ThemeProvider>
    </DependenciesProvider>
  );

  await user.click(screen.getByRole('img', {hidden: true}));
  expect(handleRemove).toHaveBeenCalledTimes(1);
});
```

Note: `CloseIcon` renders as an SVG element. If `getByRole('img', {hidden: true})` doesn't work, use `document.querySelector('svg')` to click the SVG directly:
```tsx
const svg = document.querySelector('svg') as SVGElement;
await user.click(svg);
```

- [ ] **Write AddItem test**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AddItem} from '../../../../../../../front/src/application/component/Dashboard/Widgets/AddItem';

test('it renders the children label', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <AddItem add={jest.fn()}>Add families</AddItem>
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('Add families')).toBeInTheDocument();
});

test('it calls the add callback when clicked', async () => {
  const handleAdd = jest.fn();
  const user = userEvent.setup();

  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <AddItem add={handleAdd}>Add families</AddItem>
      </ThemeProvider>
    </DependenciesProvider>
  );

  await user.click(screen.getByRole('button', {name: 'Add families'}));
  expect(handleAdd).toHaveBeenCalledTimes(1);
});
```

- [ ] **Write SeeInGrid test**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {SeeInGrid} from '../../../../../../../front/src/application/component/Dashboard/Widgets/SeeInGrid';

test('it renders the translated label', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <SeeInGrid follow={jest.fn()} />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(
    screen.getByText('akeneo_data_quality_insights.dqi_dashboard.widgets.see_in_grid')
  ).toBeInTheDocument();
});

test('it calls the follow callback when clicked', async () => {
  const handleFollow = jest.fn();
  const user = userEvent.setup();

  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <SeeInGrid follow={handleFollow} />
      </ThemeProvider>
    </DependenciesProvider>
  );

  await user.click(
    screen.getByRole('button', {name: 'akeneo_data_quality_insights.dqi_dashboard.widgets.see_in_grid'})
  );
  expect(handleFollow).toHaveBeenCalledTimes(1);
});
```

- [ ] **Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/Widgets/RemoveItem.unit.tsx
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/Widgets/AddItem.unit.tsx
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/Widgets/SeeInGrid.unit.tsx
git commit -m "test(dqi): RemoveItem + AddItem + SeeInGrid callback tests"
```

---

## Task 7: KeyIndicatorBase — ProgressBar + translate

**Files:**
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/KeyIndicators/KeyIndicatorBase.unit.tsx`

- [ ] **Write the test**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {KeyIndicatorBase} from '../../../../../../../front/src/application/component/Dashboard/KeyIndicators/KeyIndicatorBase';

const renderKeyIndicatorBase = (percentOK: number) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <KeyIndicatorBase
          percentOK={percentOK}
          titleI18nKey="akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title"
          icon={<span data-testid="test-icon">icon</span>}
        />
      </ThemeProvider>
    </DependenciesProvider>
  );

test('it renders the translated title', () => {
  renderKeyIndicatorBase(75);

  expect(
    screen.getByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title')
  ).toBeInTheDocument();
});

test('it renders the percentage label', () => {
  renderKeyIndicatorBase(42);

  expect(screen.getByText('42%')).toBeInTheDocument();
});

test('it renders the icon slot', () => {
  renderKeyIndicatorBase(75);

  expect(screen.getByTestId('test-icon')).toBeInTheDocument();
});

test('it renders children when provided', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <KeyIndicatorBase
          percentOK={50}
          titleI18nKey="my_title"
          icon={<span>icon</span>}
        >
          <span data-testid="child-content">link</span>
        </KeyIndicatorBase>
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByTestId('child-content')).toBeInTheDocument();
});
```

- [ ] **Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/Dashboard/KeyIndicators/KeyIndicatorBase.unit.tsx
git commit -m "test(dqi): KeyIndicatorBase — ProgressBar percent + title + icon rendering"
```

---

## Task 8: oro/translator group — HelperMessage + NoAttributeGroups

**Files:**
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/AttributeGroup/HelperMessage.unit.tsx`
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/NoAttributeGroups.unit.tsx`

Note: these components use `import translate from 'oro/translator'` (legacy CJS module), NOT `useTranslate`. Mock: `jest.mock('oro/translator', () => (key: string) => key, {virtual: true})`.

- [ ] **Write HelperMessage test**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

import {HelperMessage} from '../../../../../../front/src/application/component/AttributeGroup/HelperMessage';

test('it renders the helper info text and link', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <HelperMessage />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(
    screen.getByText('akeneo_data_quality_insights.attribute_group.helper_dqi_info')
  ).toBeInTheDocument();
  expect(
    screen.getByText('akeneo_data_quality_insights.attribute_group.helper_dqi_link')
  ).toBeInTheDocument();
  expect(
    screen.getByRole('link', {name: 'akeneo_data_quality_insights.attribute_group.helper_dqi_link'})
  ).toHaveAttribute('target', '_blank');
});
```

- [ ] **Write NoAttributeGroups test**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

import {NoAttributeGroups} from '../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/NoAttributeGroups';

test('it renders the title, subtitle and help center link', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <NoAttributeGroups />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(
    screen.getByText('akeneo_data_quality_insights.product_evaluation.messages.no_attribute_groups.title')
  ).toBeInTheDocument();
  expect(
    screen.getByText('akeneo_data_quality_insights.product_evaluation.messages.no_attribute_groups.subtitle')
  ).toBeInTheDocument();
  expect(
    screen.getByRole('link', {name: 'akeneo_data_quality_insights.product_evaluation.messages.no_attribute_groups.help_center_link'})
  ).toHaveAttribute('target', '_blank');
});
```

- [ ] **Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/AttributeGroup/HelperMessage.unit.tsx
git add "src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/NoAttributeGroups.unit.tsx"
git commit -m "test(dqi): HelperMessage + NoAttributeGroups (oro/translator mock)"
```

---

## Task 9: AttributeGroupsHelper — conditional rendering

**Files:**
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/AttributesGroupsHelper.unit.tsx`

Note: the source file is named `AttributesGroupsHelper.tsx` but exports `AttributeGroupsHelper` (no 's' in export name).

- [ ] **Write the test**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

jest.mock('oro/translator', () => (key: string, params?: any) => {
  if (params?.link) return `translated_with_link:${params.link}`;
  return key;
}, {virtual: true});

import {AttributeGroupsHelper} from '../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/AttributesGroupsHelper';

const makeGroup = (code: string, enLabel: string) => ({
  code,
  sort_order: 0,
  attributes: [],
  labels: {en_US: enLabel, fr_FR: enLabel + '_fr'},
  permissions: {view: [], edit: []},
  attributes_sort_order: {},
  meta: {id: 1},
  isDqiActivated: true,
});

const mockGroups = {
  marketing: makeGroup('marketing', 'Marketing'),
  erp: makeGroup('erp', 'ERP'),
};

describe('AttributeGroupsHelper', () => {
  it('renders nothing when not all groups evaluated and groups are empty', () => {
    const {container} = render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AttributeGroupsHelper evaluatedAttributeGroups={null} allGroupsEvaluated={false} locale="en_US" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(container.firstChild).toBeEmptyDOMElement();
  });

  it('renders nothing when not all groups evaluated and groups collection is empty', () => {
    const {container} = render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AttributeGroupsHelper evaluatedAttributeGroups={{}} allGroupsEvaluated={false} locale="en_US" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(container.firstChild).toBeEmptyDOMElement();
  });

  it('renders evaluated group labels when not all groups evaluated but some groups exist', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AttributeGroupsHelper evaluatedAttributeGroups={mockGroups} allGroupsEvaluated={false} locale="en_US" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    const groupsEl = screen.getByTestId('dqi-evaluated-attribute-groups');
    expect(groupsEl.textContent).toContain('Marketing');
    expect(groupsEl.textContent).toContain('ERP');
  });

  it('renders the all-groups-evaluated message when allGroupsEvaluated is true', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AttributeGroupsHelper evaluatedAttributeGroups={mockGroups} allGroupsEvaluated={true} locale="en_US" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(
      screen.getByText(/translated_with_link/)
    ).toBeInTheDocument();
  });
});
```

- [ ] **Commit**

```bash
git add "src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/AttributesGroupsHelper.unit.tsx"
git commit -m "test(dqi): AttributeGroupsHelper — conditional rendering branches"
```

---

## Task 10: ToggleActivation — hook + security-context mocks

**Files:**
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/AttributeGroup/ToggleActivation.unit.tsx`

- [ ] **Write the test**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});
jest.mock('pim/security-context', () => ({isGranted: jest.fn()}), {virtual: true});
jest.mock(
  '../../../../../../front/src/infrastructure/hooks/AttributeGroup/useAttributeGroupState',
  () => ({useAttributeGroupState: jest.fn()})
);

import {ToggleActivation} from '../../../../../../front/src/application/component/AttributeGroup/ToggleActivation';
import {useAttributeGroupState} from '../../../../../../front/src/infrastructure/hooks/AttributeGroup/useAttributeGroupState';

const mockUseAttributeGroupState = useAttributeGroupState as jest.Mock;
const mockIsGranted = require('pim/security-context').isGranted as jest.Mock;

describe('ToggleActivation', () => {
  const mockToggle = jest.fn();

  beforeEach(() => {
    jest.clearAllMocks();
    mockUseAttributeGroupState.mockReturnValue({isGroupActivated: true, toggleGroupActivation: mockToggle});
    mockIsGranted.mockReturnValue(true);
  });

  it('renders a checked checkbox when the group is activated', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <ToggleActivation groupCode="marketing" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(screen.getByRole('checkbox')).toBeChecked();
  });

  it('renders an unchecked checkbox when the group is deactivated', () => {
    mockUseAttributeGroupState.mockReturnValue({isGroupActivated: false, toggleGroupActivation: mockToggle});

    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <ToggleActivation groupCode="marketing" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(screen.getByRole('checkbox')).not.toBeChecked();
  });

  it('calls toggleGroupActivation when the label is clicked and the user is granted', async () => {
    const user = userEvent.setup();
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <ToggleActivation groupCode="marketing" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    await user.click(screen.getByRole('label') ?? document.querySelector('label.switch-small')!);
    expect(mockToggle).toHaveBeenCalledTimes(1);
  });

  it('does not call toggleGroupActivation when the user is not granted', async () => {
    mockIsGranted.mockReturnValue(false);
    const user = userEvent.setup();

    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <ToggleActivation groupCode="marketing" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    await user.click(document.querySelector('label.switch-small')!);
    expect(mockToggle).not.toHaveBeenCalled();
  });
});
```

Note on clicking the label: `ToggleActivation` uses a `<label className="switch-small">` as the toggle trigger. Use `document.querySelector('label.switch-small')` to target it.

- [ ] **Commit**

```bash
git add src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/AttributeGroup/ToggleActivation.unit.tsx
git commit -m "test(dqi): ToggleActivation — mocked hook + security-context, toggle behavior"
```

---

## Task 11: AxisHeader — AllAttributesLink conditional rendering

**Files:**
- Create: `src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/Axis/AxisHeader.unit.tsx`

- [ ] **Write the test**

```tsx
import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});
jest.mock(
  '../../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/AllAttributesLink',
  () => ({__esModule: true, default: ({attributes}: {attributes: string[]}) => <span data-testid="all-attributes-link">link:{attributes.length}</span>})
);

import {AxisHeader} from '../../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Axis/AxisHeader';
import Evaluation from '../../../../../../../../../front/src/domain/Evaluation.interface';

const makeEvaluation = (improvableAttributes: string[] = []): Evaluation => ({
  rate: null,
  criteria: improvableAttributes.length > 0
    ? [{code: 'criterion_1', rate: {value: 80, rank: 'B'}, status: 'done', improvable_attributes: improvableAttributes}]
    : [],
});

describe('AxisHeader', () => {
  it('renders the translated axis title', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AxisHeader evaluation={makeEvaluation()} axis="enrichment" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(
      screen.getByText('akeneo_data_quality_insights.product_evaluation.axis.enrichment.title')
    ).toBeInTheDocument();
  });

  it('does not render AllAttributesLink when no improvable attributes', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AxisHeader evaluation={makeEvaluation([])} axis="enrichment" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(screen.queryByTestId('all-attributes-link')).not.toBeInTheDocument();
  });

  it('renders AllAttributesLink with attributes when improvable attributes exist', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AxisHeader evaluation={makeEvaluation(['attr_1', 'attr_2'])} axis="enrichment" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(screen.getByTestId('all-attributes-link')).toBeInTheDocument();
    expect(screen.getByTestId('all-attributes-link').textContent).toBe('link:2');
  });
});
```

- [ ] **Commit**

```bash
git add "src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/Axis/AxisHeader.unit.tsx"
git commit -m "test(dqi): AxisHeader — axis title + AllAttributesLink conditional display"
```

---

## Task 12: Open the PR

- [ ] **Push the branch**

```bash
git push -u origin test/jest-coverage-dqi-components
```

- [ ] **Open PR**

```bash
gh pr create \
  --title "test(dqi): cover component gaps — EvaluationHelper + 16 presentational components" \
  --body "## Summary
- Adds 17 unit-test files for DQI front components previously missing coverage
- Tier 1: EvaluationHelper pure function (convertEvaluationToLegacyFormat, 6 cases)
- Tier 2: 16 presentational components (smoke + key behavioral assertions)
- Skipped: FamilyWidget, CategoryWidget, charts — jQuery/Select2/portal dependencies

## Test groups
- Pure logic: EvaluationHelper
- No-props render: QualityScoreLoader, EmptyChartPlaceholder
- useTranslate: EmptyKeyIndicators, QualityScorePending, KeyIndicatorNoData, SectionTitle, KeyIndicatorBase, SeeInGrid
- Router mock: BackLinkButton
- Callback props: RemoveItem, AddItem
- oro/translator mock: HelperMessage, NoAttributeGroups
- Complex mocks: AttributeGroupsHelper, ToggleActivation, AxisHeader

## Test plan
- [ ] CI \`test-front-unit\` passes (no regressions, all new tests green)
- [ ] Coverage delta: statements +4-6 pp on DQI module
"
```

- [ ] **Wait for CI** — check `test-front-unit` shard results in the PR.

---

## Troubleshooting

**`DependenciesProvider` renders nothing / i18n keys don't resolve**
The `DependenciesProvider` from `@akeneo-pim-community/legacy-bridge` provides a `useTranslate` that returns the key as-is in test env. No additional setup needed.

**`oro/translator` import fails**
Always add `{virtual: true}` to the mock: `jest.mock('oro/translator', () => (key: string) => key, {virtual: true})`. This is needed because `oro/translator` is a legacy AMD module resolved via Jest's `moduleNameMapper`, not a real file in `node_modules`.

**`pim/router` or `pim/security-context` import fails**
Same fix: add `{virtual: true}`.

**`CloseIcon` click in RemoveItem doesn't fire**
`CloseIcon` is an SVG from akeneo-design-system. Try `document.querySelector('svg')` or wrap the container with a `data-testid` and use `within()` to scope the click.

**AxisHeader `Evaluation.interface.ts` type errors**
The `Evaluation` default export has `rate: Rate | null`. Import `Rate` from `'../../../../../../../../../front/src/domain/Rate.interface'` if TypeScript complains, or cast `null as any`.
