# react-router 5 → 6 Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Migrate react-router-dom from v5 to v6 across all 5 workspaces (82 files), replacing deprecated APIs and adopting the data router pattern for useBlocker support. Fixes path-to-regexp HIGH vulnerability.

**Architecture:** All 5 React micro-apps use `<HashRouter>` with `<Switch>`/`<Route>` patterns. Migration converts to `createHashRouter()` + `<RouterProvider>` (data router API) to enable `useBlocker()` for unsaved changes. Each workspace is self-contained with its own router setup.

**Tech Stack:** React 18, react-router-dom 6.x, TypeScript 5.7, Jest 29

---

### Task 1: Upgrade packages

**Files:**
- Modify: `package.json`

**Step 1: Update dependencies**

```bash
yarn remove @types/react-router-dom
yarn add react-router-dom@^6.28
```

Note: `@types/react-router-dom` is no longer needed — v6 ships built-in TypeScript types. The `history` package is no longer a peer dep either (internal to v6).

**Step 2: Verify lockfile**

```bash
yarn install
```

**Step 3: Commit**

```bash
git add package.json yarn.lock
git commit -m "upgrade: react-router-dom 5 → 6, remove @types/react-router-dom"
```

---

### Task 2: Migrate IdentifierGeneratorApp router

**Files:**
- Modify: `components/identifier-generator/front/src/feature/IdentifierGeneratorApp.tsx`

**Step 1: Convert to data router + Routes**

Replace:
```tsx
import {HashRouter as Router, Route, Switch} from 'react-router-dom';

<Router basename="/configuration/identifier-generator">
  <Switch>
    <Route path="/:identifierGeneratorCode">
      <Edit />
    </Route>
    <Route path="/">
      <List />
    </Route>
  </Switch>
</Router>
```

With:
```tsx
import {createHashRouter, RouterProvider, Route, createRoutesFromElements} from 'react-router-dom';

const router = createHashRouter(
  createRoutesFromElements(
    <>
      <Route path="/:identifierGeneratorCode" element={<Edit />} />
      <Route path="/" element={<List />} />
    </>
  ),
  {basename: '/configuration/identifier-generator'}
);

// In JSX:
<RouterProvider router={router} />
```

**Step 2: Run tests**

```bash
yarn jest --config components/identifier-generator/front/jest.config.ts --passWithNoTests 2>&1 | tail -20
```

**Step 3: Commit**

```bash
git commit -am "refactor(identifier-generator): migrate to react-router v6 data router"
```

---

### Task 3: Migrate MeasurementApp router

**Files:**
- Modify: `src/Akeneo/Tool/Bundle/MeasureBundle/front/src/feature/MeasurementApp.tsx`

**Step 1: Convert to data router + Routes**

Same pattern as Task 2. Replace `<HashRouter>` + `<Switch>` + `<Route>` with `createHashRouter` + `RouterProvider`.

Routes:
- `/:measurementFamilyCode` → `<Edit />`
- `/` → `<List />`

**Step 2: Run tests**

```bash
yarn jest --config src/Akeneo/Tool/Bundle/MeasureBundle/front/jest.config.ts --passWithNoTests 2>&1 | tail -20
```

**Step 3: Commit**

```bash
git commit -am "refactor(measurement): migrate to react-router v6 data router"
```

---

### Task 4: Migrate ProcessTrackerApp router (render prop → element)

**Files:**
- Modify: `src/Akeneo/Platform/Job/front/process-tracker/src/feature/ProcessTrackerApp.tsx`
- Modify: `src/Akeneo/Platform/Job/front/process-tracker/src/feature/pages/JobExecutionDetail.tsx` (if params need extraction)

**Step 1: Convert render prop to element + useParams**

Replace:
```tsx
<Route
  path="/show/:jobExecutionId"
  render={props => (
    <JobExecutionDetail
      key={props.match.params.jobExecutionId}
      jobExecutionId={props.match.params.jobExecutionId}
    />
  )}
/>
```

With:
```tsx
<Route path="/show/:jobExecutionId" element={<JobExecutionDetailWrapper />} />
```

Create wrapper (inline or separate):
```tsx
const JobExecutionDetailWrapper = () => {
  const {jobExecutionId} = useParams<{jobExecutionId: string}>();
  return <JobExecutionDetail key={jobExecutionId} jobExecutionId={jobExecutionId!} />;
};
```

Also convert to data router pattern.

**Step 2: Run tests**

```bash
yarn jest --config src/Akeneo/Platform/Job/front/process-tracker/jest.config.ts --passWithNoTests 2>&1 | tail -20
```

**Step 3: Commit**

```bash
git commit -am "refactor(process-tracker): migrate to react-router v6 data router"
```

---

### Task 5: Migrate CategoriesApp router

**Files:**
- Modify: `src/Akeneo/Category/front/src/feature/CategoriesApp.tsx`

**Step 1: Convert to data router + Routes**

Current routes:
- `/:treeId/tree` → `<CategoriesTreePage />`
- `/:categoryId/edit` → `<EditCategoryProvider><CategoryEditPage /></EditCategoryProvider>`
- `/:treeId/template/:templateId` → `<SaveStatusProvider><UnsavedChangesGuard /><TemplateFormProvider><TemplatePage /></TemplateFormProvider></SaveStatusProvider>`
- `/` → `<CategoriesIndex />`

For the template route with providers, wrap them in a layout route or keep as nested elements.

**Step 2: Run tests**

```bash
yarn jest --config src/Akeneo/Category/front/jest.config.ts --passWithNoTests 2>&1 | tail -20
```

**Step 3: Commit**

```bash
git commit -am "refactor(category): migrate to react-router v6 data router"
```

---

### Task 6: Migrate Connectivity router setup files (7 files)

**Files:**
- Modify: `src/Akeneo/Connectivity/Connection/front/src/infrastructure/Settings.tsx`
- Modify: `src/Akeneo/Connectivity/Connection/front/src/infrastructure/Marketplace.tsx`
- Modify: `src/Akeneo/Connectivity/Connection/front/src/infrastructure/Apps.tsx`
- Modify: `src/Akeneo/Connectivity/Connection/front/src/infrastructure/WebhookSettings.tsx`
- Modify: `src/Akeneo/Connectivity/Connection/front/src/infrastructure/ErrorManagement.tsx`
- Modify: `src/Akeneo/Connectivity/Connection/front/src/infrastructure/ConnectedApps.tsx`
- Modify: `src/Akeneo/Connectivity/Connection/front/src/infrastructure/CustomApps.tsx`

**Step 1: Convert each file's HashRouter + Switch to data router pattern**

Each infrastructure file follows the same pattern:
```tsx
export const Settings = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <Index />
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));
```

Convert to:
```tsx
export const Settings = withDependencies(() => {
    const router = createHashRouter(
        createRoutesFromElements(<Route path="/*" element={<Index />} />)
    );
    return (
        <StrictMode>
            <AkeneoThemeProvider>
                <RouterProvider router={router} />
            </AkeneoThemeProvider>
        </StrictMode>
    );
});
```

**Special: Marketplace.tsx — convert Redirect → Navigate**

Replace:
```tsx
<Redirect from='/connect/marketplace/profile' to='/connect/app-store/profile' />
<Redirect from='/connect/marketplace' to='/connect/app-store' />
```

With:
```tsx
<Route path="/connect/marketplace/profile" element={<Navigate to="/connect/app-store/profile" replace />} />
<Route path="/connect/marketplace" element={<Navigate to="/connect/app-store" replace />} />
```

**Step 2: Run tests**

```bash
yarn jest --config src/Akeneo/Connectivity/Connection/front/jest.config.ts --passWithNoTests 2>&1 | tail -20
```

**Step 3: Commit**

```bash
git commit -am "refactor(connectivity): migrate 7 router setup files to react-router v6"
```

---

### Task 7: Migrate useHistory → useNavigate (38 files)

**Files:**
- All files importing `useHistory` from `react-router-dom` or `react-router`

**Step 1: Mechanical replacement across all workspaces**

Pattern replacements:
```
import {useHistory} from 'react-router-dom'  →  import {useNavigate} from 'react-router-dom'
const history = useHistory()                  →  const navigate = useNavigate()
history.push(path)                            →  navigate(path)
history.push(path, state)                     →  navigate(path, {state})
history.replace(path)                         →  navigate(path, {replace: true})
history.goBack()                              →  navigate(-1)
history.go(-1)                                →  navigate(-1)
history.go(n)                                 →  navigate(n)
```

**Special: history.createHref() in EventSubscriptionDisabled.tsx**

Replace:
```tsx
const history = useHistory();
href={history.createHref({pathname: generatePath('...', {connectionCode})})}
```

With:
```tsx
// Simply compute the path — no useHref needed for static links
const href = `#${generatePath('/connect/connection-settings/:connectionCode/event-subscription', {connectionCode})}`;
```

Or use `useHref()`:
```tsx
import {useHref, generatePath} from 'react-router-dom';
const href = useHref(generatePath('/connect/connection-settings/:connectionCode/event-subscription', {connectionCode}));
```

**Step 2: Verify no useHistory remains**

```bash
grep -rn "useHistory" src/ components/ --include="*.ts" --include="*.tsx" | grep -v node_modules | grep -v __tests__ | grep -v test
```

**Step 3: Commit**

```bash
git commit -am "refactor: migrate useHistory → useNavigate across 38 files"
```

---

### Task 8: Migrate Switch → Routes in page components

**Files:**
- All 25 components using `<Switch>` that weren't already converted in Tasks 2-6

**Step 1: Mechanical replacement**

```
import {Switch, Route} from 'react-router-dom'  →  import {Routes, Route} from 'react-router-dom'
<Switch>                                          →  <Routes>
</Switch>                                         →  </Routes>
<Route path="..." component={X} />                →  <Route path="..." element={<X />} />
<Route exact path="...">                          →  <Route path="...">
```

Note: In v6, all routes match exactly by default. Use `path="/*"` for catch-all.

**Step 2: Commit**

```bash
git commit -am "refactor: migrate Switch → Routes in 25 page components"
```

---

### Task 9: Add useParams types (20 files)

**Files:**
- All files using `useParams()` without type arguments

**Step 1: Add type parameters**

Replace:
```tsx
const {code} = useParams() as {code: string};
// or
const {code} = useParams<{code: string}>();
```

With (v6 pattern — params are always `string | undefined`):
```tsx
const {code} = useParams<'code'>();
// code is string | undefined, use ! if guaranteed by route
```

Or the cleaner v6 pattern:
```tsx
const params = useParams();
const code = params.code!; // non-null assertion when route guarantees it
```

**Step 2: Commit**

```bash
git commit -am "refactor: add useParams type annotations for react-router v6"
```

---

### Task 10: Implement useUnsavedChangesGuard hook (Prompt → useBlocker)

**Files:**
- Create: `front-packages/shared/src/hooks/useUnsavedChangesGuard.ts`
- Modify: `src/Akeneo/Category/front/src/feature/components/templates/UnsavedChangeGuard.tsx`
- Modify: `src/Akeneo/Tool/Bundle/MeasureBundle/front/src/feature/pages/edit/Edit.tsx`

**Step 1: Write failing test for the shared hook**

```tsx
// front-packages/shared/src/hooks/useUnsavedChangesGuard.unit.ts
import {renderHook} from '@testing-library/react';
import {useUnsavedChangesGuard} from './useUnsavedChangesGuard';

// Test that it blocks navigation when shouldBlock=true
// Test that it allows navigation when shouldBlock=false
// Test that it adds/removes beforeunload listener
```

**Step 2: Implement the hook**

```tsx
import {useBlocker} from 'react-router-dom';
import {useCallback, useEffect} from 'react';

export const useUnsavedChangesGuard = (shouldBlock: boolean, message: string) => {
  // Block react-router navigation
  const blocker = useBlocker(shouldBlock);

  useEffect(() => {
    if (blocker.state === 'blocked') {
      const proceed = window.confirm(message);
      if (proceed) {
        blocker.proceed();
      } else {
        blocker.reset();
      }
    }
  }, [blocker, message]);

  // Block browser tab close
  const handleBeforeUnload = useCallback(
    (event: BeforeUnloadEvent) => {
      if (shouldBlock) {
        event.preventDefault();
        event.returnValue = message;
      }
    },
    [shouldBlock, message]
  );

  useEffect(() => {
    window.addEventListener('beforeunload', handleBeforeUnload);
    return () => window.removeEventListener('beforeunload', handleBeforeUnload);
  }, [handleBeforeUnload]);
};
```

**Step 3: Update UnsavedChangeGuard.tsx (Category)**

Replace `<Prompt>` with the hook:
```tsx
import {useUnsavedChangesGuard} from '@akeneo-pim-community/shared';

export const UnsavedChangesGuard = () => {
  const translate = useTranslate();
  const {globalStatus} = useSaveStatus();
  const message = translate('akeneo.category.template.attribute.settings.unsaved_changes');

  useUnsavedChangesGuard(globalStatus !== Status.SAVED, message);

  // Backbone bridge (keep existing setCanLeavePage logic)
  const {setCanLeavePage, setLeavePageMessage} = useContext(CanLeavePageContext);
  useEffect(() => {
    if (globalStatus === Status.SAVED) {
      setCanLeavePage(true);
    } else {
      setCanLeavePage(false);
      setLeavePageMessage(message);
    }
  }, [globalStatus, setCanLeavePage, setLeavePageMessage, message]);

  return null; // No JSX needed — hook handles everything
};
```

**Step 4: Update Edit.tsx (MeasureBundle)**

Replace `<Prompt when={isModified} message={...} />` with:
```tsx
useUnsavedChangesGuard(isModified, translate('pim_ui.flash.unsaved_changes'));
```

Remove `Prompt` import.

**Step 5: Run tests**

```bash
yarn jest --config front-packages/shared/jest.config.ts --passWithNoTests 2>&1 | tail -20
yarn jest --config src/Akeneo/Category/front/jest.config.ts --passWithNoTests 2>&1 | tail -20
yarn jest --config src/Akeneo/Tool/Bundle/MeasureBundle/front/jest.config.ts --passWithNoTests 2>&1 | tail -20
```

**Step 6: Commit**

```bash
git commit -am "feat: add useUnsavedChangesGuard hook, replace Prompt with useBlocker"
```

---

### Task 11: Migrate test utilities

**Files:**
- Modify: `src/Akeneo/Connectivity/Connection/front/tests/test-utils.tsx`
- Modify: All test files using `createMemoryHistory` (6 files)
- Modify: All test files mocking `react-router-dom` (5+ files)

**Step 1: Rewrite Connectivity test-utils**

Replace:
```tsx
import {createMemoryHistory} from 'history';
import {Router} from 'react-router-dom';

export const historyMock = {
    history: createMemoryHistory(),
    reset: () => { historyMock.history = createMemoryHistory(); },
};

<Router history={historyMock.history}>{children}</Router>
```

With:
```tsx
import {MemoryRouter} from 'react-router-dom';

<MemoryRouter>{children}</MemoryRouter>
```

For tests that need to inspect navigation, use `useLocation()` inside a test component or `createMemoryRouter()`.

**Step 2: Update identifier-generator test files**

Replace `createMemoryHistory` + `<Router history={...}>` with `<MemoryRouter>`:
```tsx
// Before
import {createMemoryHistory} from 'history';
import {Router} from 'react-router';
const history = createMemoryHistory();
render(<Router history={history}><Component /></Router>);

// After
import {MemoryRouter} from 'react-router-dom';
render(<MemoryRouter><Component /></MemoryRouter>);
```

Files:
- `components/identifier-generator/front/src/feature/pages/__tests__/ListPage.test.tsx`
- `components/identifier-generator/front/src/feature/pages/__tests__/CreateOrEditGeneratorPage.test.tsx`
- `components/identifier-generator/front/src/feature/pages/__tests__/CreateGeneratorPage.test.tsx`
- `components/identifier-generator/front/src/feature/__tests__/IdentifierGeneratorApp.test.tsx`

**Step 3: Update useParams mocks**

Replace:
```tsx
jest.mock('react-router-dom', () => ({
  ...jest.requireActual('react-router-dom'),
  useParams: () => ({identifierGeneratorCode: 'my_generator'}),
}));
```

This pattern still works in v6 — keep as-is.

**Step 4: Update MeasureBundle test**

Replace `createMemoryHistory` usage in `MeasurementFamilyTable.test.tsx`.

**Step 5: Verify no `history` package imports remain**

```bash
grep -rn "from 'history'" src/ components/ --include="*.ts" --include="*.tsx" | grep -v node_modules
```

**Step 6: Commit**

```bash
git commit -am "test: migrate test utilities to react-router v6 (MemoryRouter, remove history package)"
```

---

### Task 12: Clean up and verify

**Step 1: Remove history package if no longer needed**

```bash
# Check if any file still imports from 'history'
grep -rn "from 'history'" src/ components/ front-packages/ --include="*.ts" --include="*.tsx" | grep -v node_modules
# If clean:
yarn remove history @types/history 2>/dev/null || true
```

**Step 2: Run full frontend lint**

```bash
yarn lint 2>&1 | tail -30
```

**Step 3: Run full frontend tests**

```bash
yarn unit 2>&1 | tail -30
```

**Step 4: Fix any failures**

Common v6 test issues:
- `useNavigate()` may only be used in the context of a `<Router>` → wrap with `<MemoryRouter>`
- `useBlocker()` requires data router → wrap with `createMemoryRouter` in tests
- Route matching now exact by default → add `/*` suffix for prefix matching

**Step 5: Commit fixes**

```bash
git commit -am "fix: resolve react-router v6 test failures"
```

**Step 6: Push and create PR**

```bash
git push -u origin upgrade/react-router-6
gh pr create --title "upgrade: react-router 5 → 6" --body "..."
```

---

## Migration Quick Reference

| v5 | v6 | Notes |
|----|-----|-------|
| `<Switch>` | `<Routes>` | |
| `<Route component={X}>` | `<Route element={<X />}>` | |
| `<Route render={fn}>` | `<Route element={<X />}>` + useParams inside X | |
| `<Redirect to="...">` | `<Navigate to="..." replace>` | |
| `<Prompt when message>` | `useBlocker()` | Requires data router |
| `useHistory()` | `useNavigate()` | |
| `history.push(path)` | `navigate(path)` | |
| `history.replace(path)` | `navigate(path, {replace: true})` | |
| `history.goBack()` | `navigate(-1)` | |
| `history.createHref()` | `useHref()` or compute manually | |
| `useRouteMatch()` | `useMatch()` | Not used in codebase |
| `<HashRouter>` | `createHashRouter()` + `<RouterProvider>` | For useBlocker support |
| `createMemoryHistory()` | `<MemoryRouter>` or `createMemoryRouter()` | Tests only |

## Verification

- [ ] `grep -rn "from 'react-router-dom'" src/ components/ | grep -c "Switch\|Redirect\|useHistory\|Prompt"` → 0
- [ ] `yarn lint` passes
- [ ] `yarn unit` passes
- [ ] All 5 app entry points use `createHashRouter` + `RouterProvider`
- [ ] `<Prompt>` fully replaced by `useUnsavedChangesGuard` hook
- [ ] No `history` package direct imports remain
- [ ] `yarn audit --groups dependencies | grep path-to-regexp` → no HIGH vulnerability
