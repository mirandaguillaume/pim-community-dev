# TypeScript 5 + ESLint 8 + @typescript-eslint v7 Upgrade Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Upgrade TypeScript from 4.9.5 to 5.7, ESLint from 6 to 8, and @typescript-eslint from v4 to v7 in a single PR to reduce technical debt and enable modern tooling.

**Architecture:** Big Bang approach — all three upgrades in a single branch. The upgrades are interdependent: @typescript-eslint v7 requires ESLint 8.56+ and TypeScript 4.7.4+. All 13 ESLint config files will be updated in-place. No architectural changes (no flat config, no `moduleResolution: bundler`, no `jsx: react-jsx` migration).

**Tech Stack:** TypeScript 5.7, ESLint 8.57, @typescript-eslint 7.x, eslint-config-react-app 7.0.1, ts-loader 9.5 (already compatible), ts-jest 29.1 (already compatible)

---

## Context & Risk Assessment

### Dependency Graph
```
@typescript-eslint v7 ──requires──▶ ESLint 8.56+ ──requires──▶ eslint-plugin upgrades
                      ──requires──▶ TypeScript 4.7.4+ (TS 5.7 qualifies)
eslint-config-react-app 7 ──requires──▶ ESLint 8
                           ──peer dep──▶ @typescript-eslint ^5 (we force v7 via resolution)
```

### Breaking Changes to Handle

| What | From | To | Impact |
|------|------|----|--------|
| TypeScript | 4.9.5 | 5.7 | Low — 32 regular enums (no const enum), no decorators, no deprecated config |
| ESLint | 6.5 | 8.57 | Medium — plugin version bumps required |
| @typescript-eslint | 4.x | 7.x | High — rule renames + config name changes in 9 files |
| eslint-config-react-app | 6.0 | 7.0.1 | Low — 4 simple workspace configs |
| eslint-plugin-react-hooks | 2.x | 4.x | Low — compatible API |
| eslint-plugin-jest | 24.x | 27.x | Low — used by ImportExport only |
| eslint-plugin-testing-library | 3.x | 5.x | Low — used by ImportExport only |
| eslint-plugin-flowtype | 5.x | 8.x | Low — required by react-app config, not used directly |

### @typescript-eslint Rule Migration Map

These rules were renamed/removed between v4 and v7:

| Old (v4) | New (v7) | Files Affected | Current Value |
|----------|----------|----------------|---------------|
| `recommended-requiring-type-checking` (config) | `recommended-type-checked` | 9 files (Group B) | extends |
| `@typescript-eslint/camelcase` | Removed (use `naming-convention`) | 8 files | "off" or "error" |
| `@typescript-eslint/ban-types` | Removed (split into 3 rules) | 3 files | "off" |
| `@typescript-eslint/ban-ts-comment` | `@typescript-eslint/ts-comment` | 1 file | "off" |
| `@typescript-eslint/comma-spacing` | Removed (use core or @stylistic) | 1 file (DSM) | "error" |
| `@typescript-eslint/no-var-requires` | `@typescript-eslint/no-require-imports` | 1 file | "off" |

### File Groups

**Group A — react-app preset (4 files, simple configs):**
- `src/Akeneo/Platform/Bundle/CatalogVolumeMonitoringBundle/front/.eslintrc.json`
- `src/Akeneo/Platform/Bundle/ImportExportBundle/front/.eslintrc.json`
- `src/Akeneo/Platform/Job/front/process-tracker/.eslintrc.json`
- `src/Oro/Bundle/ConfigBundle/front/.eslintrc.json`

**Group B — type-checked configs (9 files, need rule renames):**
- `front-packages/akeneo-design-system/.eslintrc.json`
- `components/identifier-generator/front/.eslintrc.json`
- `src/Akeneo/Connectivity/Connection/front/.eslintrc.json`
- `src/Akeneo/Connectivity/Connection/workspaces/permission-form/.eslintrc.json`
- `src/Akeneo/Platform/Bundle/CommunicationChannelBundle/front/.eslintrc.json`
- `src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/legacy-bridge/.eslintrc.json`
- `src/Akeneo/Pim/Structure/Bundle/Resources/workspaces/settings-ui/.eslintrc.json`
- `src/Akeneo/UserManagement/Bundle/Resources/workspaces/user-ui/.eslintrc.json`

**Group C — root config (1 file, JS only, no changes needed):**
- `.eslintrc` (uses `@babel/eslint-parser`, not affected by @typescript-eslint upgrade)

---

## Task 1: Create Branch and Update Root package.json

**Files:**
- Modify: `package.json` (devDependencies + resolutions)

**Step 1: Create feature branch from master**

```bash
git checkout master
git pull origin master
git checkout -b upgrade/typescript-5-eslint-8
```

**Step 2: Update devDependencies in package.json**

Change these versions in `devDependencies`:
```json
"@typescript-eslint/eslint-plugin": "^7.0.0",
"@typescript-eslint/parser": "^7.0.0",
"eslint": "^8.57.0",
"eslint-config-react-app": "^7.0.1",
"eslint-plugin-flowtype": "^8.0.3",
"eslint-plugin-jest": "^27.9.0",
"eslint-plugin-react-hooks": "^4.6.0",
"eslint-plugin-testing-library": "^5.11.1",
"typescript": "^5.7.0"
```

These stay unchanged (already compatible):
```
"eslint-plugin-import": "^2.22.1"       — ESLint 8 compatible
"eslint-plugin-jsx-a11y": "^6.3.1"      — ESLint 8 compatible
"eslint-plugin-react": "^7.16.0"        — ESLint 8 compatible
"ts-loader": "^9.5.0"                   — TS 5 compatible
"ts-jest": "^29.1.0"                    — TS 5 compatible
```

**Step 3: Update resolutions in package.json**

Change:
```json
"typescript": "5.7.3"
```

Add (to resolve eslint-config-react-app peer dep mismatch):
```json
"@typescript-eslint/eslint-plugin": "^7.0.0",
"@typescript-eslint/parser": "^7.0.0"
```

**Step 4: Run yarn install**

```bash
yarn install
```

Expected: yarn.lock regenerated with new versions. Watch for peer dep warnings (non-blocking).

**Step 5: Verify installed versions**

```bash
yarn why typescript
yarn why eslint
yarn why @typescript-eslint/parser
```

Expected: TypeScript 5.7.x, ESLint 8.57.x, @typescript-eslint/parser 7.x.x

**Step 6: Commit**

```bash
git add package.json yarn.lock
git commit -m "upgrade: typescript 5.7, eslint 8, @typescript-eslint v7

Update root package.json devDependencies and resolutions for the
TypeScript 5 + ESLint 8 + @typescript-eslint v7 upgrade bundle."
```

---

## Task 2: Fix ESLint Configs — Group B (Type-Checked Configs)

These 9 files use `recommended-requiring-type-checking` and deprecated rules that must be renamed.

**Files:**
- Modify: `front-packages/akeneo-design-system/.eslintrc.json`
- Modify: `components/identifier-generator/front/.eslintrc.json`
- Modify: `src/Akeneo/Connectivity/Connection/front/.eslintrc.json`
- Modify: `src/Akeneo/Connectivity/Connection/workspaces/permission-form/.eslintrc.json`
- Modify: `src/Akeneo/Platform/Bundle/CommunicationChannelBundle/front/.eslintrc.json`
- Modify: `src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/legacy-bridge/.eslintrc.json`
- Modify: `src/Akeneo/Pim/Structure/Bundle/Resources/workspaces/settings-ui/.eslintrc.json`
- Modify: `src/Akeneo/UserManagement/Bundle/Resources/workspaces/user-ui/.eslintrc.json`

**Step 1: Rename config extends in ALL 9 files**

In every file in Group B, replace:
```
"plugin:@typescript-eslint/recommended-requiring-type-checking"
```
with:
```
"plugin:@typescript-eslint/recommended-type-checked"
```

Also remove `"plugin:@typescript-eslint/eslint-recommended"` from extends — it's been merged into `recommended` since v6.

**Step 2: Fix akeneo-design-system/.eslintrc.json**

Changes:
- Remove: `"@typescript-eslint/camelcase": "off"` (rule removed in v5)
- Remove: `"@typescript-eslint/comma-spacing": "error"` (removed in v6)
- Remove: `"comma-spacing": "off"` (was only needed to prevent conflict with TS version)

**Step 3: Fix identifier-generator/front/.eslintrc.json**

Changes:
- Remove: `"@typescript-eslint/camelcase": "off"` (rule removed)
- Remove: `"@typescript-eslint/ban-types": "off"` (deprecated, split into auto-included rules)
- Replace: `"@typescript-eslint/ban-ts-comment": "off"` → `"@typescript-eslint/ts-comment": "off"`

**Step 4: Fix Connection/front/.eslintrc.json**

Changes:
- Remove: `"@typescript-eslint/camelcase": "off"`
- Remove: `"@typescript-eslint/ban-types": "off"`

**Step 5: Fix permission-form/.eslintrc.json**

Changes:
- Remove: `"@typescript-eslint/camelcase": "off"`
- Remove: `"@typescript-eslint/ban-types": "off"`

**Step 6: Fix CommunicationChannelBundle/.eslintrc.json**

Changes:
- Remove: `"@typescript-eslint/camelcase": ["error", {"properties": "never"}]` (rule removed; camelCase enforcement via `naming-convention` is out of scope)
- Replace: `"@typescript-eslint/no-var-requires": "off"` → `"@typescript-eslint/no-require-imports": "off"`
- Remove: `"@typescript-eslint/parser": "off"` (this is invalid — there's no such rule, it's a parser)

**Step 7: Fix legacy-bridge/.eslintrc.json**

Changes:
- Remove: `"@typescript-eslint/camelcase": ["error", {"properties": "never"}]`

**Step 8: Fix settings-ui/.eslintrc.json**

Changes:
- Remove: `"@typescript-eslint/camelcase": ["error", {"properties": "never"}]`

**Step 9: Fix user-ui/.eslintrc.json**

Changes:
- Remove: `"@typescript-eslint/camelcase": ["error", {"properties": "never"}]`

**Step 10: Commit**

```bash
git add -A '*.eslintrc.json'
git commit -m "fix: migrate eslintrc configs for @typescript-eslint v7

- Rename recommended-requiring-type-checking → recommended-type-checked
- Remove eslint-recommended (merged into recommended since v6)
- Remove deprecated rules: camelcase, ban-types, ban-ts-comment, comma-spacing
- Replace no-var-requires → no-require-imports, ban-ts-comment → ts-comment"
```

---

## Task 3: Fix ESLint Configs — Group A (react-app Configs)

The 4 workspaces using `react-app` preset should work with eslint-config-react-app v7 without changes. But verify they don't need adjustments.

**Files:**
- Possibly modify: `src/Akeneo/Platform/Bundle/CatalogVolumeMonitoringBundle/front/.eslintrc.json`
- Possibly modify: `src/Akeneo/Platform/Bundle/ImportExportBundle/front/.eslintrc.json`
- Possibly modify: `src/Akeneo/Platform/Job/front/process-tracker/.eslintrc.json`
- Possibly modify: `src/Oro/Bundle/ConfigBundle/front/.eslintrc.json`

**Step 1: Test lint on each Group A workspace**

```bash
yarn workspace @akeneo-pim-community/process-tracker lint:check 2>&1 | head -50
yarn workspace @akeneo-pim-community/catalog-volume-monitoring lint:check 2>&1 | head -50
yarn workspace @akeneo-pim-community/config lint:check 2>&1 | head -50
yarn workspace @akeneo-pim-community/import-export lint:check 2>&1 | head -50
```

Expected: all pass. If errors, fix the specific config.

**Step 2: If ImportExportBundle errors on `react-app/jest`**

The `react-app/jest` sub-config may reference testing-library rules that changed. If it errors:
- Replace `"react-app/jest"` with just `"react-app"` (the jest rules are optional)
- Or remove the `react-app/jest` extends entirely

**Step 3: Commit (if changes needed)**

```bash
git add -A '*.eslintrc.json'
git commit -m "fix: adjust react-app eslint configs for eslint-config-react-app v7"
```

---

## Task 4: Verify TypeScript Compilation

**Step 1: Run tsc on root tsconfig**

```bash
npx tsc --noEmit -p tsconfig.json 2>&1 | head -50
```

Expected: no new errors. TS 5.7 is backward compatible for our config. Watch for:
- Enum narrowing changes (unlikely — all 32 enums are regular, no const)
- `isolatedModules` stricter checks (already enabled everywhere)

**Step 2: Run webpack build**

```bash
NODE_PATH=node_modules npx webpack --config webpack.config.js 2>&1 | tail -30
```

This tests ts-loader 9.5 with TS 5.7. Expected: successful build.

**Step 3: Run lib:build on key packages**

```bash
yarn dsm:build 2>&1 | tail -10
yarn shared:build 2>&1 | tail -10
yarn measurement:build 2>&1 | tail -10
yarn category:build 2>&1 | tail -10
```

Expected: all pass (tsc compile with declaration output).

**Step 4: Commit (only if fixes were needed)**

If any TypeScript errors appeared, fix them and commit:
```bash
git add -A
git commit -m "fix: resolve TypeScript 5.7 compilation errors"
```

---

## Task 5: Verify Test Suites

**Step 1: Run workspace unit tests**

```bash
yarn packages:unit 2>&1 | tail -30
```

This runs ts-jest 29.1 with TS 5.7 across all workspace test suites. Expected: all pass.

**Step 2: Run root unit tests**

```bash
jest --no-cache --config tests/front/unit/jest/unit.jest.js --runInBand --forceExit 2>&1 | tail -30
```

Expected: pass. ts-jest is already TS 5 compatible.

**Step 3: Fix any test failures**

If tests fail due to TS 5 changes (unlikely), fix and commit:
```bash
git add -A
git commit -m "fix: adjust tests for TypeScript 5.7 compatibility"
```

---

## Task 6: Verify Full Lint Pipeline

**Step 1: Run root lint**

```bash
yarn lint 2>&1 | tail -50
```

This runs ESLint 8 on `src/**/*.js` with the root `.eslintrc` (babel parser). Expected: pass.

**Step 2: Run packages:lint:check**

```bash
yarn packages:lint:check 2>&1 | tail -50
```

This runs lint on all workspaces that have `lint:check` scripts: shared, measurement, process-tracker, import-export, catalog-volume-monitoring, config, identifier-generator.

**Step 3: Fix any new lint errors**

@typescript-eslint v7 `recommended-type-checked` may enable new rules that weren't in v4's `recommended-requiring-type-checking`. If new violations appear:
- Prefer disabling new rules (adding them as "off") rather than fixing code
- This keeps the PR focused on the upgrade, not code changes
- Document disabled rules for future cleanup

```bash
git add -A
git commit -m "fix: suppress new lint rules from @typescript-eslint v7"
```

---

## Task 7: Final Verification and PR

**Step 1: Run complete front-end pipeline**

```bash
# Build
yarn webpack-dev 2>&1 | tail -20

# Lint
yarn lint 2>&1 | tail -20
yarn packages:lint:check 2>&1 | tail -20

# Tests
yarn packages:unit 2>&1 | tail -20
```

**Step 2: Verify audit improvement**

```bash
yarn audit --summary 2>&1
```

Expected: vulnerability count should drop (removing old ESLint + plugins removes transitive vulns).

**Step 3: Commit any remaining fixes**

```bash
git add -A
git commit -m "fix: final adjustments for TypeScript 5 upgrade"
```

**Step 4: Push and create PR**

```bash
git push -u origin upgrade/typescript-5-eslint-8
```

Create PR targeting `master`:
```
Title: upgrade: TypeScript 5.7, ESLint 8, @typescript-eslint v7

## Summary
- TypeScript 4.9.5 → 5.7.3
- ESLint 6.5 → 8.57
- @typescript-eslint 4.x → 7.x
- eslint-config-react-app 6 → 7.0.1
- eslint-plugin-react-hooks 2 → 4, eslint-plugin-jest 24 → 27, eslint-plugin-testing-library 3 → 5, eslint-plugin-flowtype 5 → 8

## Changes
- Updated root package.json dependencies and resolutions
- Migrated 9 ESLint configs: `recommended-requiring-type-checking` → `recommended-type-checked`
- Removed deprecated rules: `camelcase`, `ban-types`, `ban-ts-comment`, `comma-spacing`, `no-var-requires`
- All existing tests pass, webpack build succeeds, lint pipeline green

## Test plan
- [ ] CI front-build passes (ts-loader + TS 5.7)
- [ ] CI front-lint passes (ESLint 8 + @typescript-eslint v7)
- [ ] CI front-unit passes (ts-jest + TS 5.7)
- [ ] CI behat-legacy passes (no backend impact)
- [ ] `yarn audit` shows reduced vulnerabilities
```
