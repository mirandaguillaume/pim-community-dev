# DQI Component Coverage — Phase 3 (Context + Compound Components)

Date: 2026-06-09
Branch: `test/jest-coverage-dqi-phase3`
PR: to be opened against master

---

## Context

Phase 2 (PR #258) covered 17 presentational components and `EvaluationHelper`. This spec covers the next tier of uncovered files in the DQI front module: context providers, message builders, small compound components, and a custom-event component.

**Remaining testable files after Phase 2:** ~57 (excluding index barrels, type-only interfaces, jQuery/Select2 widgets, Victory charts, and full-app entry points).

This spec covers the **next 14 files** — all with manageable mock requirements.

---

## Scope

### Tier 1 — Simple smoke (oro/translator virtual mock)

| Source | Test file | Key assertions |
|--------|-----------|----------------|
| `Axis/AxisError.tsx` | `Axis/AxisError.unit.tsx` | renders i18n key via `data-testid` or text |
| `Axis/AxisGradingInProgress.tsx` | `Axis/AxisGradingInProgress.unit.tsx` | renders i18n key |
| `Criterion/Title.tsx` | `Criterion/Title.unit.tsx` | no criterion → renders only `: `; with criterion → renders translated key |
| `Criterion/Icon.tsx` | `Criterion/Icon.unit.tsx` | clones child with width=20 height=20 |

### Tier 2 — Props + callbacks + custom events

| Source | Test file | Key assertions |
|--------|-----------|----------------|
| `AllAttributesLink.tsx` | `DataQualityInsights/AllAttributesLink.unit.tsx` | enrichment click → `dispatchEvent(CustomEvent('data-quality:product:filter_all_missing_attributes', ...))`, consistency → `filter_all_improvable_attributes`; attributes in detail |
| `Modal.tsx` | `Application/component/Modal.unit.tsx` | renders title/subtitle/description; save disabled when `enableSaveButton=false`; confirm fires `onConfirm`; cancel fires `onDismissModal` |
| `Dashboard/Widgets/Table.tsx` | `Dashboard/Widgets/Table.unit.tsx` | Table renders children in `<tbody>`; Row with `isHeader=true` has no row class; Cell with `align=right` sets textAlign; `action=true` adds action class |

### Tier 3 — Pure function

| Source | Test file | Key assertions |
|--------|-----------|----------------|
| `KeyIndicators/messageBuilder.tsx` | `KeyIndicators/messageBuilder.unit.tsx` | known marker replaced by JSX element; unknown word wrapped in `<span>`; result is `<TextWithLink>` container |

### Tier 4 — Message builders (useTranslate + roughCount)

| Source | Test file | Key assertions |
|--------|-----------|----------------|
| `KeyIndicators/AttributeMessageBuilder.tsx` | `KeyIndicators/AttributeMessageBuilder.unit.tsx` | returns null when `totalToImprove=0`; renders button with rough count text otherwise |
| `KeyIndicators/ProductMessageBuilder.tsx` | `KeyIndicators/ProductMessageBuilder.unit.tsx` | returns null when both counts=0; renders one button (products only); renders two buttons (products + models) |

### Tier 5 — Context providers

| Source | Test file | Key assertions |
|--------|-----------|----------------|
| `context/AxesContext.tsx` | `context/AxesContext.unit.tsx` | default value `axes=[]`; `AxesContextProvider` passes `axes` prop to consumers |
| `context/KeyIndicatorsContext.tsx` | `context/KeyIndicatorsContext.unit.tsx` | default value `tips={}`; `KeyIndicatorsProvider` passes `tips` prop |
| `context/AttributeGroupsStatusContext.tsx` | `context/AttributeGroupsStatusContext.unit.tsx` | default value `{load: fn, status: {}}`; Provider renders children; mock `useFetchAllAttributeGroupsStatus` |
| `context/DashboardContext.tsx` | `context/DashboardContext.unit.tsx` | `useDashboardContext()` throws outside Provider; Provider renders children; mock `useInitDashboardContextState` |

---

## Deliberately Excluded (Phase 3)

| File | Reason |
|------|--------|
| `keyIndicatorDescriptorsCE.tsx` | Renders DSM icon JSX in object literal — needs ThemeProvider at module level; marginal ROI |
| `ScoreDistributionSection/Header.tsx` | Embeds `TimePeriodFilter` which needs Dashboard context |
| `constant/*.ts` | String-only exports, no logic |
| `domain/AttributeGroup.ts`, `domain/Score.ts` | Type-only files |
| `CategoryWidget.tsx`, `FamilyWidget.tsx`, `FamiliesSelect2.tsx` | jQuery/Select2 |
| `QualityScoreEvolutionChart.tsx`, `ScoreDistributionChart*.tsx` | Victory charts |
| `ProductEditFormApp.tsx`, `ProductModelEditFormApp.tsx` | Full app entry points |
| `QualityScorePortal.tsx`, `TabContentWithPortalDecorator.tsx` | React portals |
| Complex compound components | Phase 4 |

---

## Test Infrastructure

Same as Phase 2. No new infrastructure.

- `@testing-library/react` (`render`, `renderHook`, `screen`, `fireEvent`)
- `@testing-library/user-event` v12.8.3 (NO `userEvent.setup()` — direct API only)
- `DependenciesProvider` + `ThemeProvider(pimTheme)` wrapper for DSM-dependent components
- `jest.mock('oro/translator', () => (key: string) => key, {virtual: true})` for AMD virtual modules

---

## Import Depth Reference

From each test file's directory, counting `../` levels back to the DQI module root (`DataQualityInsights/`), then append `front/src/`:

| Test directory | `../` count | Prefix |
|---|---|---|
| `tests/front/unit/Application/component/` | 5 | `../../../../../front/src/` |
| `tests/front/unit/Application/context/` | 5 | `../../../../../front/src/` |
| `tests/front/unit/Application/component/Dashboard/Widgets/` | 7 | `../../../../../../../front/src/` |
| `tests/front/unit/Application/component/Dashboard/KeyIndicators/` | 7 | `../../../../../../../front/src/` |
| `tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/` | 8 | `../../../../../../../../front/src/` |
| `tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/Axis/` | 9 | `../../../../../../../../../front/src/` |
| `tests/front/unit/Application/component/ProductEditForm/TabContent/DataQualityInsights/Criterion/` | 9 | `../../../../../../../../../front/src/` |

---

## Coverage Estimate

~14 new test files, ~45-60 new tests.

Expected delta on DQI coverage:
- Statements: +3 to +5 pp
- Branches: +2 to +4 pp (context guards, message builder conditions)
- Functions: +3 to +5 pp

---

## Deliverable

One PR: `test/jest-coverage-dqi-phase3` → `master`
Auto-merge: `gh pr merge <N> --auto --squash` immediately after `gh pr create`.
