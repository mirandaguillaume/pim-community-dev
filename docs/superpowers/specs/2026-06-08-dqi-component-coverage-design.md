# DQI Component Coverage — Phase 2 (Component Gaps)

Date: 2026-06-08
Branch: `test/jest-coverage-dqi-components`
PR: to be opened against master

---

## Context

Phase 1 (DQI reducers, helpers, domain) and Phase 8 (hooks + fetchers OOM fixes) are complete.
190 source files exist in the DQI front module; 86 have test coverage.

**Remaining untested files** fall into three categories:
- Pure TypeScript interfaces (`*.interface.ts`, type-only files) — no logic, not testable
- Simple presentational components — low effort, high coverage return
- Complex widgets (`FamilyWidget`, `CategoryWidget`, charts) — jQuery/Select2/portal dependencies, deliberately skipped

This spec covers the **middle tier**: one pure logic function + ~16 simple components.

---

## Scope

### Tier 1 — Pure Logic (1 file)

**`EvaluationHelper.ts`** exports `convertEvaluationToLegacyFormat(axes, productEvaluation)`.

This function takes a new-format product evaluation (channel → locale → criteria[]) and redistributes
criteria into the legacy format (axis → channel → locale → {rate, criteria}) by filtering each criterion
against the axis's allowed code list.

Test cases:
1. Empty `axes` → empty result `{}`
2. Empty `productEvaluation` → empty result for all axes
3. Single axis, single channel, single locale — criteria filtered to axis codes
4. Multi-axis — criteria distributed exclusively to their matching axis
5. Criterion present in evaluation but no axis claims it — dropped silently
6. `evaluationPlaceholder` constant — verify shape (`rate.value: null`, `criteria: []`)

### Tier 2 — Simple Components (~16 files)

All tests use the `DependenciesProvider` + `ThemeProvider(pimTheme)` wrapper established in Phase 1.
All test files use **relative imports** (not workspace aliases) to preserve Stryker per-test coverage tracing.
File naming convention: `*.unit.tsx` (or `.unit.ts` for non-JSX).

#### Group A — No-props render components
| Source | Test file | Key assertions |
|--------|-----------|----------------|
| `QualityScoreLoader.tsx` | `QualityScoreLoader.unit.tsx` | `data-testid="quality-score-loader"` present |
| `EmptyKeyIndicators.tsx` | `EmptyKeyIndicators.unit.tsx` | both i18n keys rendered |
| `KeyIndicatorNoData.tsx` | `KeyIndicatorNoData.unit.tsx` | i18n key rendered |
| `EmptyChartPlaceholder.tsx` | `EmptyChartPlaceholder.unit.tsx` | renders without crash |
| `QualityScorePending.tsx` | `QualityScorePending.unit.tsx` | `data-testid="quality-score-pending"` + i18n key |

#### Group B — Props + Router dependency
| Source | Test file | Key assertions |
|--------|-----------|----------------|
| `BackLinkButton.tsx` | `BackLinkButton.unit.tsx` | label renders; click triggers `Router.redirectToRoute(route, routeParams)` |
| `KeyIndicatorBase.tsx` | `KeyIndicatorBase.unit.tsx` | ProgressBar `percent` matches `percentOK`; translated title present |
| `SectionTitle.tsx` | `SectionTitle.unit.tsx` | renders children |

Mock pattern for `BackLinkButton`:
```ts
jest.mock('pim/router', () => ({redirectToRoute: jest.fn()}));
```

#### Group C — Action button components (callback props)
| Source | Test file | Key assertions |
|--------|-----------|----------------|
| `RemoveItem.tsx` | `RemoveItem.unit.tsx` | click calls `remove` callback |
| `AddItem.tsx` | `AddItem.unit.tsx` | renders children; click calls `add` callback |
| `SeeInGrid.tsx` | `SeeInGrid.unit.tsx` | click calls `follow` callback |

#### Group D — AttributeGroup components
| Source | Test file | Key assertions |
|--------|-----------|----------------|
| `HelperMessage.tsx` | `HelperMessage.unit.tsx` | renders message text |
| `ToggleActivation.tsx` | `ToggleActivation.unit.tsx` | renders toggle; click fires `onChange` |

#### Group E — ProductEditForm sub-components
| Source | Test file | Key assertions |
|--------|-----------|----------------|
| `AxisHeader.tsx` | `AxisHeader.unit.tsx` | renders axis label |
| `NoAttributeGroups.tsx` | `NoAttributeGroups.unit.tsx` | renders i18n message |
| `AttributesGroupsHelper.tsx` | `AttributesGroupsHelper.unit.tsx` | renders without crash |

---

## Deliberately Excluded

| File | Reason |
|------|--------|
| `FamilyWidget.tsx` | jQuery Select2 (`FamiliesSelect2`), portal, localStorage — flaky test surface |
| `CategoryWidget.tsx` | Same class of dependency |
| `Widgets.tsx` | Container for above |
| `FamilyModal.tsx`, `CategoryModal.tsx` | Portal + jQuery dependencies |
| `QualityScoreEvolutionChart.tsx` | Victory charts — heavy canvas mock |
| `ScoreDistributionChart*.tsx` | Same |
| `ProductEvaluationFetcher.ts` | TypeScript interface — no logic |
| `ProductFetcher.ts` | TypeScript interface — no logic |
| `PageContextHook.ts` | TypeScript interface — no logic |
| `followKeyIndicatorResult.ts` | Type definitions only |

---

## Test Infrastructure

No new infrastructure needed. Existing patterns apply:
- `@testing-library/react` + `@testing-library/user-event`
- `DependenciesProvider` from `@akeneo-pim-community/legacy-bridge`
- `ThemeProvider` + `pimTheme` from `akeneo-design-system`
- `jest.mock('pim/router', ...)` for Router-dependent components

---

## Coverage Estimate

~17 new test files, ~60-80 new tests. Expected coverage delta on DQI:
- Statements: +4 to +6 pp (currently ~68%)
- Branches: +2 to +3 pp (currently ~57%)
- Functions: +3 to +5 pp (currently ~45%)

Mutation score target: ≥90% on Tier 1, ≥70% on Tier 2 (render-level tests kill fewer mutants by design).

---

## Deliverable

One PR: `test/jest-coverage-dqi-components` → `master`
Commit message pattern: `test(dqi): cover component gaps — EvaluationHelper + 16 presentational components`
