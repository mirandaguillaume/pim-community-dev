# Upgrade Roadmap — Akeneo PIM Community Edition

**Date**: 2026-02-27
**Status**: Draft
**Goal**: Modernize the stack before EOL deadlines, improve CI speed, harden security.
**Accelerator**: Claude Code agents + worktree isolation for parallel execution.

## Detailed Design Docs

| Phase | Document |
|-------|----------|
| Phase 1 | [`phase1-mysql-node.md`](2026-02-27-phase1-mysql-node.md) — MySQL 8.4 + Node 22 + Jest 29 |
| Phase 2 | [`phase2-symfony64.md`](2026-02-27-phase2-symfony64.md) — Symfony 6.4 + PHP 8.3 |
| Phase 3 | [`phase3-react18.md`](2026-02-27-phase3-react18.md) — React 18 + testing library |
| Behat→Playwright | [`behat-to-playwright.md`](2026-02-27-behat-to-playwright.md) — 294 E2E scenarios migration |

Each doc contains: exact file paths, line numbers, code samples, and step-by-step migration instructions.

## Current State

| Component | Version | Constraint | EOL | Status |
|-----------|---------|-----------|-----|--------|
| **PHP** | 8.2 | `>=8.1` | Dec 2026 (security-only) | Upgrade to 8.3 |
| **Symfony** | 5.4 | `^5.4` | Nov 2025 (fully EOL) | **Critical: upgrade to 6.4 LTS** |
| **Doctrine ORM** | 2.x | `^2.14` | Maintained | OK (ORM 3 not required for Sf6.4) |
| **React** | 17 | `^17.0` | No patches | Upgrade to 18 |
| **Node.js** | 20 | `node:20` | April 2026 | Upgrade to 22 LTS |
| **MySQL** | 8.0 | `mysql:8.0` | April 2026 | Upgrade to 8.4 |
| **Elasticsearch** | 8.11 | `8.11` | Maintenance expired | Upgrade to 8.x latest |
| **Behat/Selenium** | 560 scenarios | 10 shards, ~53 min CI | Legacy approach | Migrate to Playwright |
| **Storybook** | 6.3 | DSM package | 6.x deprecated | Upgrade to 8.x (done in worktree) |

## Accelerated Timeline (with Claude)

```
         Phase 0          Phase 1           Phase 2          Phase 3
         Tooling/CI       EOL + Behat       Symfony 6.4      React 18
         ─────────        ──────────        ───────────      ────────
Feb 27 ──┤ done ├──►
                    Mar S1 ─── MySQL 8.4 + Node 22 (1 session)
                           ─── Behat→Playwright: @critical batch (50 scenarios)
                    Mar S2 ─── Symfony 6.4 worktree (proxy-manager + flex)
                           ─── Behat→Playwright: next batch (~50 scenarios)
                    Mar S3 ─── Symfony 6.4 merge + PHP 8.3
                           ─── Behat→Playwright: continues
                    Mar S4 ──────────────────── React 18 (1-2 sessions)
                                               ─── Behat→Playwright: continues
                                                            ...
                    ─── Behat→Playwright runs continuously in parallel ───►
```

**Estimated completion**: End of March 2026 for Phases 1-3 (was September 2026 without Claude).

---

## Phase 0 — Tooling & CI (done / in progress)

**Goal**: Foundation work that de-risks all subsequent phases.

### Completed

- [x] CI `max-parallel: 8` for behat-legacy shards (was 4, EX63 can handle 8)
- [x] Claude agents rewritten with Context7 + Serena (upgrade-advisor, security-audit, refactor-advisor)
- [x] PHIVE setup: `phive.xml` with php-cs-fixer, infection, composer-unused, composer-require-checker, phpcpd
- [x] php-cs-fixer migrated from Composer to PHIVE (`vendor/bin/` → `tools/`, 27 files)
- [x] `roave/security-advisories` added to composer.json (passive CVE protection)
- [x] `.gitignore` updated for `tools/` and `.phive/`
- [x] CI step added for PHIVE install in `setup-pim-job/action.yml`

### Pending

- [ ] Merge PHIVE worktree (`agent-ae1f8747`) into master
- [ ] Run `composer update` to verify roave/security-advisories doesn't conflict
- [ ] Run `phive install` locally to validate all 5 PHARs download correctly
- [ ] Storybook 8.x merge from worktree (153 files: MDX→CSF3 migration)

---

## Phase 1 — Critical EOL + Behat Migration Start (March S1)

**Deadline**: MySQL 8.0 and Node 20 both EOL in April 2026.
**Claude estimate**: 1 session for EOL fixes + start of Behat migration.

### 1.1 MySQL 8.0 → 8.4

**Effort**: S (1-4h) | **Risk**: Low

MySQL 8.4 is backward-compatible with 8.0 for standard usage. Main changes:
- `mysql_native_password` plugin disabled by default (use `caching_sha2_password`)
- Deprecated: `utf8mb3` charset (Akeneo already uses `utf8mb4`)

Files to update:
- `docker-compose.yml` — image tag `mysql:8.0` → `mysql:8.4`
- `.github/scripts/docker-compose.override.yml.dist` — same
- `.env` — verify `DATABASE_URL` connection string

Validation:
```bash
docker-compose up -d mysql
docker-compose run --rm php php bin/console doctrine:schema:validate
make unit-back
```

### 1.2 Node 20 → 22 LTS

**Effort**: S (1-4h) | **Risk**: Low-Medium

Files to update:
- `docker-compose.yml` — `node:20` → `node:22`
- `.github/workflows/ci.yml` — Node version in setup steps
- `.nvmrc` / `package.json` engines (if present)

Validation:
```bash
yarn install
yarn lint
yarn unit
npx playwright test
```

### 1.3 Hardcoded Secrets Remediation

**Effort**: XS (<1h) | **Risk**: Low

Findings from security audit:
| File | Secret | Fix |
|------|--------|-----|
| `.env` | `APP_SECRET=ThisTokenIsNotSoSecretChangeIt` | Generate real secret, document in README |
| `docker-compose.yml` | `MYSQL_ROOT_PASSWORD=root` | OK for dev, but add `.env.ci` for CI |
| `.env` | `MINIO_ACCESS_KEY/SECRET_KEY` | Same — dev-only, document as such |

Lower priority since these are dev defaults, but should be documented clearly.

### 1.4 Behat → Playwright: Batch 1 — `@critical` scenarios

**Effort**: M-L (ongoing) | **Risk**: Low (incremental, dual-run)

Start with the `@critical` suite (~50 scenarios) because:
- They run as a **separate CI suite** already — easy to swap
- They cover the most important user flows (login, product creation, import/export)
- Fastest feedback loop: if Playwright passes, disable the Behat `critical` suite

#### Migration strategy (incremental)

```
┌─────────────┐     ┌──────────────┐     ┌─────────────────┐
│ Read .feature│────►│ Write .spec.ts│────►│ Run both suites  │
│ + Context.php│     │ (Playwright)  │     │ Compare results  │
└─────────────┘     └──────────────┘     └────────┬────────┘
                                                   │
                                          ┌────────▼────────┐
                                          │ Playwright passes│
                                          │ → tag @skip in   │
                                          │   Behat feature  │
                                          └─────────────────┘
```

1. **Read** the `.feature` file + its `Context.php` step definitions
2. **Translate** to Playwright TypeScript test (Page Object pattern)
3. **Dual-run**: keep Behat scenario active, add Playwright test
4. **Validate**: when Playwright passes reliably (3+ CI runs), tag `@skip` on the Behat scenario
5. **Track**: maintain a migration log (migrated / total per bounded context)

#### What Claude handles well

- Reading Gherkin + Context PHP → generating Playwright TypeScript
- Page Object scaffolding from React component analysis (Serena)
- Batch translation of similar scenarios (e.g., all CRUD patterns)

#### What needs human validation

- Complex business logic assertions (completeness checks, calculation rules)
- Timing-sensitive flows (job execution, async events)
- Visual regressions (layout, CSS — Playwright screenshots help)

#### CI integration

The Playwright job already exists in CI. New tests are added to `tests/e2e/`:
```bash
# Existing Playwright config
npx playwright test

# Per-feature validation during migration
npx playwright test tests/e2e/product-creation.spec.ts
```

As Behat scenarios are tagged `@skip`, shards rebalance automatically (fewer scenarios per shard → faster).

---

## Phase 2 — Symfony 6.4 LTS (March S2-S3)

**Deadline**: Symfony 5.4 has been fully EOL since November 2025. This is the highest-priority code migration.

**Effort**: L (2-3 sessions with Claude) | **Risk**: Medium

### 2.1 Blockers in composer.json

| Blocker | Current | Fix | Effort |
|---------|---------|-----|--------|
| `symfony/proxy-manager-bridge` | `^5.4` | Removed in Symfony 6. Replace with `symfony/var-exporter` lazy ghosts | M |
| `ocramius/proxy-manager` | `^2.2` | Abandoned. Remove, use Symfony lazy ghosts | M |
| `symfony/flex` | `^1.17` | Needs `^2.0` for Symfony 6 | XS |

### 2.2 Lazy Services Migration (18 services)

The 18 services currently using `ocramius/proxy-manager` need to be migrated to Symfony's native lazy ghost objects (available since Symfony 6.2):

```yaml
# Before (proxy-manager)
services:
    App\HeavyService:
        lazy: true  # Uses ocramius proxy

# After (Symfony 6.4 lazy ghosts)
services:
    App\HeavyService:
        lazy: true  # Uses symfony/var-exporter ghost objects (no extra package needed)
```

Use `composer-unused` to verify proxy-manager is fully removed after migration.

### 2.3 Configuration Changes

| Config | Current | Symfony 6.4 |
|--------|---------|-------------|
| `enable_authenticator_manager: true` | `security.yaml` | Remove (always enabled in 6.4) |
| `framework.router.utf8: true` | May be explicit | Default in 6.4, can remove |

### 2.4 Code Deprecations (mostly clean)

The codebase is already well-prepared:
- ✅ 0 `ContainerAwareCommand` (all migrated)
- ✅ 63/68 commands use `#[AsCommand]` attribute
- ✅ Modern `TreeBuilder` usage
- ✅ Modern authenticator system
- ⚠️ 5 remaining `$defaultName` commands (migrate to `#[AsCommand]`)

### 2.5 PHP 8.2 → 8.3

**Effort**: XS (<1h) | **Risk**: Low

Can be done in the same PR as Symfony 6.4 (both require Docker image update).

| Issue | Files | Fix |
|-------|-------|-----|
| `\Serializable` interface (fatal in 8.4, deprecated in 8.2) | 2 files | Use `__serialize()` / `__unserialize()` |
| `json_validate()` available | — | Can now replace `json_decode` + error check patterns |

Files to update:
- `docker-compose.yml` — PHP image tag
- `composer.json` — `"php": ">=8.3"`
- `.github/workflows/ci.yml` — PHP version

### 2.6 Validation Strategy

```bash
# Before starting
docker-compose run --rm php php tools/composer-unused          # Clean unused deps
docker-compose run --rm php php tools/composer-require-checker  # Surface implicit deps

# After migration
docker-compose run --rm php composer validate --strict
docker-compose run --rm php php bin/console cache:clear
PIM_CONTEXT=test make lint-back
PIM_CONTEXT=test make unit-back
PIM_CONTEXT=test make coupling-back
PIM_CONTEXT=test make acceptance-back
```

---

## Phase 3 — Frontend Modernization (March S4 – April)

### 3.1 React 17 → 18

**Effort**: M (1-2 sessions with Claude) | **Risk**: Medium

The codebase is cleaner than expected:
- ✅ 0 unsafe lifecycle methods (`componentWillMount`, etc.)
- ✅ 0 `findDOMNode` calls
- ✅ Only 5 class components (all `ErrorBoundary` — keep as-is)
- ⚠️ **38 `ReactDOM.render()` calls** → `createRoot().render()`
- ⚠️ **104 files** importing `@testing-library/react-hooks` → merge into `@testing-library/react`

Key optimization: 3 bridge/helper files propagate the `ReactDOM.render()` change to 15+ views, so the actual touch-count is smaller than 38.

#### Migration steps:
1. `yarn add react@18 react-dom@18 @types/react@18 @types/react-dom@18`
2. Update 38 `ReactDOM.render()` → `createRoot().render()` (3 bridge files first)
3. Replace `@testing-library/react-hooks` with `renderHook` from `@testing-library/react`
4. Run: `yarn lint && yarn unit && npx playwright test`

### 3.2 Elasticsearch 8.11 → 8.x Latest

**Effort**: S (1-4h) | **Risk**: Low

ES 8.x minor versions are backward-compatible. Update `docker-compose.yml` image tag.

Verify: `docker-compose run --rm php php bin/console akeneo:elasticsearch:reset-indexes`

### 3.3 Storybook 6.3 → 8.x (done)

Already completed in worktree: 153 files migrated from MDX stories to CSF3 format. Needs merge.

---

## Behat → Playwright Migration (continuous, parallel to all phases)

**Total scope**: ~560 scenarios across 10 shards (~53 min CI)
**Target**: Replace Behat+Selenium entirely with Playwright
**Approach**: Incremental, batch-by-batch, dual-run until validated

### Migration Order (by priority)

| Batch | Scope | Scenarios (est.) | Why this order |
|-------|-------|-------------------|----------------|
| **1** | `@critical` suite | ~50 | Separate CI suite, highest-value flows |
| **2** | Product CRUD | ~80 | Most common user journeys, many similar patterns |
| **3** | Import/Export | ~60 | Job-based flows, good Playwright async support |
| **4** | Category & Channel | ~40 | Tree navigation, simpler flows |
| **5** | User Management | ~30 | Auth flows, permissions |
| **6** | Connectivity | ~50 | API-heavy, may benefit from Playwright API testing |
| **7** | Remaining contexts | ~250 | Everything else, lowest priority |

### Progress Tracking

Each batch follows the same cycle:
1. Claude reads `.feature` + `Context.php` → generates `.spec.ts`
2. Human reviews business logic assertions
3. Dual-run in CI (both Behat and Playwright)
4. After 3+ green CI runs → `@skip` the Behat scenario
5. Update this table:

| Batch | Status | Migrated | Remaining | CI time saved |
|-------|--------|----------|-----------|---------------|
| 1 — @critical | Pending | 0/50 | 50 | — |
| 2 — Product | Pending | 0/80 | 80 | — |
| 3 — Import/Export | Pending | 0/60 | 60 | — |
| 4 — Category/Channel | Pending | 0/40 | 40 | — |
| 5 — User Mgmt | Pending | 0/30 | 30 | — |
| 6 — Connectivity | Pending | 0/50 | 50 | — |
| 7 — Remaining | Pending | 0/250 | 250 | — |
| **Total** | | **0/560** | **560** | **0 min** |

### Expected CI Impact

```
Current:    10 shards × ~5.3 min/shard = ~53 min (max-parallel: 8)
After 50%:   5 shards × ~5.3 min/shard + Playwright ~8 min = ~13 min
After 100%:  Playwright only = ~8-12 min (vs 53 min today)
```

### Step Definition Reuse Strategy

Many Behat Context classes share common patterns. Translate these once as Playwright fixtures/helpers:

| Behat Context | Playwright equivalent |
|---------------|----------------------|
| `WebUser` (login, navigation) | `fixtures/auth.ts` (storageState) |
| `DataGridContext` (filtering, sorting) | `pages/DataGridPage.ts` (Page Object) |
| `ProductContext` (create, edit) | `pages/ProductPage.ts` |
| `ImportExportContext` (launch job, wait) | `helpers/job-runner.ts` (poll API) |
| `NotificationContext` (flash messages) | `helpers/notifications.ts` (locator) |

---

## Phase 4 — Future (2027+)

### 4.1 PHP 8.4

- Wait for Symfony 6.4 to fully support 8.4
- Fix 2 `\Serializable` files (fatal error in 8.4)
- New features: property hooks, `new` in initializers
- **Prerequisite**: Phase 2 complete

### 4.2 Symfony 7.x

- Symfony 7.4 LTS expected Nov 2028
- **Prerequisite**: stable on 6.4 LTS first
- Use Rector: `docker-compose run --rm php php vendor/bin/rector process src/ --dry-run --set symfony70`

### 4.3 Doctrine ORM 3.x

- Not required for Symfony 6.4 (ORM 2.x works fine)
- Significant API changes — plan separately after Symfony 6.4 is stable
- Use upgrade-advisor agent with Context7 for breaking changes analysis

---

## EOL Timeline

```
2025 Nov ──── Symfony 5.4 fully EOL ◄── OVERDUE
2026 Feb ──── Today ◄── Phase 0 complete, starting Phase 1
2026 Mar ──── Target: MySQL 8.4 + Node 22 + Symfony 6.4 + PHP 8.3 + React 18
              ─── Behat→Playwright migration ongoing ───►
2026 Apr ──── MySQL 8.0 EOL + Node 20 EOL (should be migrated by now)
2026 Jun ──── Target: 50% Behat scenarios migrated to Playwright
2026 Sep ──── Target: 100% Behat migration complete, Selenium removed
2026 Dec ──── PHP 8.2 security support ends
2027 Nov ──── Symfony 6.4 LTS security support ends
2028 Dec ──── PHP 8.4 security support ends
```

## Risk Matrix

| Phase | Impact if delayed | Likelihood of issues | Overall risk |
|-------|-------------------|---------------------|--------------|
| Phase 1 (MySQL/Node) | High — EOL April 2026, no security patches | Low — backward-compatible | **Medium** |
| Phase 2 (Symfony 6.4) | Critical — already past EOL, CVEs unpatched | Medium — 3 blockers, 18 lazy services | **High** |
| Phase 3 (React 18) | Medium — no security patches but no known CVEs | Low — codebase is clean | **Low** |
| Behat→Playwright | High impact on velocity — 53 min CI blocks iteration | Low — incremental, dual-run | **Medium** |
| Phase 4 (future) | Low — well within support windows | — | **Low** |

## Tools Available

| Tool | Location | Purpose |
|------|----------|---------|
| `tools/php-cs-fixer` | PHIVE | Code style (PHP) |
| `tools/infection` | PHIVE | Mutation testing |
| `tools/composer-unused` | PHIVE | Find unused Composer packages |
| `tools/composer-require-checker` | PHIVE | Find implicit dependencies |
| `tools/phpcpd` | PHIVE | Copy/paste detection |
| `vendor/bin/rector` | Composer | Automated code migrations |
| `vendor/bin/phpstan` | Composer | Static analysis |
| Claude `upgrade-advisor` agent | `.claude/agents/` | Context7 + Serena analysis |
| Claude `security-audit` agent | `.claude/agents/` | OWASP + CVE scanning |
| Claude `refactor-advisor` agent | `.claude/agents/` | Code quality + Doctrine anti-patterns |
