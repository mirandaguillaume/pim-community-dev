# Dual-Track: SF 6.4 Deprecations + react-router 5 → 6

Date: 2026-03-16
Status: Approved
Branches: `fix/sf64-deprecations` (existing worktree), `upgrade/react-router-6` (new worktree)

## Context

With React 18 (#75) and PHP 8.3 (#76) merged, two independent upgrades can run in parallel:
- **Track A** (backend): Clean up Symfony 6.4 deprecations to prepare for SF 7.0
- **Track B** (frontend): Migrate react-router 5 → 6 (fixes path-to-regexp HIGH vulnerability)

Zero file overlap — PHP vs TS/TSX. Ideal for parallel worktrees.

## Track A — SF 6.4 Deprecations (3 incremental PRs)

### PR A1: EventSubscribers → #[AsEventListener] + Intl fix

**Scope:** 139 EventSubscriberInterface implementations + 1 Intl fix
- Rector `SymfonySetList::SYMFONY_54` handles the transformation
- Manual review: priority values, event name inference, multi-event subscribers
- Fix `Intl::getIcuVersion()` in PimRequirements.php

**Distribution:**
| Context | Files |
|---------|-------|
| Pim (Enrichment, Structure, Automation) | 62 |
| Tool (Versioning, Connector, Batch, API) | 27 |
| Platform (UIBundle, Installer, Job) | 15 |
| Connectivity (Connection) | 14 |
| UserManagement | 7 |
| Category | 6 |
| Channel | 4 |
| identifier-generator | 3 |
| Oro SecurityBundle | 1 |

**CI validation:** `deprecation-back` job (PHPStan with phpstan-deprecations.neon)

### PR A2: Intl deprecations (~12 files)

IntlDateFormatter, NumberFormatter → native PHP Intl or polyfill.
Tracked in phpstan-deprecations-baseline.neon (12+ entries).

### PR A3: Annotation reader cleanup (3 files)

Oro SecurityBundle annotation loader. May be blocked by Doctrine ORM 3 (annotations still used for ORM mapping).

## Track B — react-router 5 → 6 (single PR)

### Current state
- react-router-dom 5.3.0, react-router 5.2.1
- 82 files importing react-router APIs
- 5 workspaces: Connectivity (56 files), Category (11), identifier-generator (9), MeasureBundle (5), process-tracker (1)

### Migration steps

**1. Package upgrades**
- react-router-dom 5 → 6, react-router 5 → 6
- Remove @types/react-router-dom (types built-in in v6)

**2. Mechanical migration (80 files)**
| Pattern | Count | Migration |
|---------|-------|-----------|
| `<Switch>` | 25 | → `<Routes>` |
| `useHistory()` | 38 files (77 occ.) | → `useNavigate()` |
| `useParams()` | 20 files | Add type params |
| `<Redirect>` | 2 | → `<Navigate>` |
| Route render prop | 1 | → element prop |

Transformations:
- `history.push(path)` → `navigate(path)`
- `history.replace(path)` → `navigate(path, { replace: true })`
- `history.goBack()` → `navigate(-1)`

**3. Router setup migration (5 apps)**

`<HashRouter>` → `createHashRouter()` + `<RouterProvider>` (required for useBlocker).

Apps: IdentifierGeneratorApp, CategoriesApp, MeasurementApp, ProcessTrackerApp, Connectivity infra.

**4. Prompt → useBlocker (2 files)**

| File | Location |
|------|----------|
| UnsavedChangeGuard.tsx | Category/front |
| Edit.tsx | MeasureBundle/front |

Create shared `useUnsavedChangesGuard()` hook using react-router v6.4+ `useBlocker()`.

### Risks
- Connectivity workspace volume (56 files, 68% of total)
- `useBlocker` requires data router API (`createHashRouter`) — all 5 apps must migrate
- Test files also need updating (useHistory mocks → useNavigate mocks)

## Execution

Parallel worktrees dispatched as independent agents:
- Track A agent: rebase `fix/sf64-deprecations`, run Rector, verify
- Track B agent: create `upgrade/react-router-6`, mechanical migration, test

No merge conflicts expected (zero shared files).
