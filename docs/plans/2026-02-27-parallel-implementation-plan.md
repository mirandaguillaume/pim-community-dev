# Parallel Upgrade Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Execute Phase 1 (MySQL 8.4 + Node 22 + Jest 29), Phase 2 critical blockers (Symfony 6.4), and Behat→Playwright Batch 1 scaffolding in 4 parallel worktrees after committing all documentation.

**Architecture:** Commit pending work first, then launch 4 isolated worktrees. Each worktree is independent — no cross-dependencies. Merge sequentially after validation.

**Tech Stack:** MySQL 8.4, Node 22, Jest 29, Symfony 6.4, Doctrine ORM 2.x, Playwright, PHP 8.2 (staying 8.2 for now — 8.3 will come with the full SF6.4 composer update).

**Design docs:**
- `docs/plans/2026-02-27-phase1-mysql-node.md` — full MySQL + Node analysis
- `docs/plans/2026-02-27-phase2-symfony64.md` — full Symfony 6.4 analysis
- `docs/plans/2026-02-27-phase3-react18.md` — React 18 (not in this plan, depends on Jest 29)

---

## Task 0: Commit documentation + pending changes on master

**Files to commit (main repo `/home/gumiranda/pim-community-dev`):**
- Modified: `.claude/agents/refactor-advisor.md`, `security-audit.md`, `upgrade-advisor.md`
- Modified: `.github/workflows/ci.yml` (max-parallel: 4 → 8)
- New: `docs/plans/2026-02-27-upgrade-roadmap.md`
- New: `docs/plans/2026-02-27-phase1-mysql-node.md`
- New: `docs/plans/2026-02-27-phase2-symfony64.md`
- New: `docs/plans/2026-02-27-phase3-react18.md`
- New: `docs/plans/2026-02-27-behat-to-playwright.md`
- New: `docs/plans/2026-02-27-parallel-implementation-plan.md`

**Step 1: Stage documentation files**

```bash
cd /home/gumiranda/pim-community-dev
git add docs/plans/2026-02-27-*.md
git add .claude/agents/refactor-advisor.md .claude/agents/security-audit.md .claude/agents/upgrade-advisor.md
git add .github/workflows/ci.yml
```

**Step 2: Commit**

```bash
git commit -m "docs: add upgrade roadmap, phase design docs, and improved agents

- Upgrade roadmap with accelerated timeline (MySQL 8.4, Symfony 6.4, React 18, Behat→Playwright)
- Detailed design docs for each phase with exact file paths and blockers
- Agents rewritten with Context7 + Serena MCP tools (upgrade-advisor, security-audit, refactor-advisor)
- CI behat max-parallel: 4 → 8 (EX63 can handle 8 shards at ~40GB RAM)

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

**Step 3: Verify**

```bash
git status
git log --oneline -1
```

Expected: clean working tree (except `.php-cs-fixer.cache`, `.claude/worktrees/`, unrelated untracked files).

---

## Task A: MySQL 8.0 → 8.4 (worktree)

**Worktree branch:** `upgrade/mysql-8.4`

**Files:**
- Modify: `docker-compose.yml:71-72`
- Modify: `config/packages/doctrine.yml:18`
- Modify: `src/Akeneo/Platform/PimRequirements.php:28-29`
- Modify: `.github/actions/setup-pim-job/action.yml:72`
- Modify: `.github/workflows/php-compat.yml:106,176`
- Modify: `.github/docs/self-hosted-runner-setup.md:77,203`

### Step A1: Create worktree

```bash
cd /home/gumiranda/pim-community-dev
git worktree add .claude/worktrees/mysql-84 -b upgrade/mysql-8.4
cd .claude/worktrees/mysql-84
```

### Step A2: Fix docker-compose.yml — image + auth flag

Read `docker-compose.yml` and find the mysql service (~line 70-72).

```yaml
# Before
  mysql:
    image: 'mysql:8.0.30'
    command: '--default-authentication-plugin=mysql_native_password --log_bin_trust_function_creators=1'

# After
  mysql:
    image: 'mysql:8.4'
    command: '--mysql-native-password=ON'
```

Note: `--log_bin_trust_function_creators` is deprecated and unnecessary in 8.4 with default config. Remove it.

### Step A3: Fix Doctrine server_version

Read `config/packages/doctrine.yml` line 18.

```yaml
# Before
server_version: '8.0'

# After
server_version: '8.4'
```

### Step A4: Fix PimRequirements.php version bounds

Read `src/Akeneo/Platform/PimRequirements.php` lines 28-29.

```php
// Before
final public const LOWEST_REQUIRED_MYSQL_VERSION = '8.0.30';
final public const GREATEST_REQUIRED_MYSQL_VERSION = '8.1.0';

// After
final public const LOWEST_REQUIRED_MYSQL_VERSION = '8.0.30';
final public const GREATEST_REQUIRED_MYSQL_VERSION = '9.0.0';
```

### Step A5: Fix CI image references

Read `.github/actions/setup-pim-job/action.yml` line 72. Change `mysql:8.0.30` → `mysql:8.4`.

Read `.github/workflows/php-compat.yml` lines 106 and 176. Change `mysql:8.0.30` → `mysql:8.4` (two occurrences).

Read `.github/docs/self-hosted-runner-setup.md` lines 77 and 203. Change `mysql:8.0.30` → `mysql:8.4`.

### Step A6: Commit

```bash
git add -A
git commit -m "feat: upgrade MySQL 8.0.30 → 8.4

- Docker image: mysql:8.0.30 → mysql:8.4
- Remove --default-authentication-plugin (removed in 8.4), use --mysql-native-password=ON
- Remove --log_bin_trust_function_creators (deprecated)
- Doctrine server_version: '8.0' → '8.4'
- PimRequirements: raise upper bound to 9.0.0
- Update all CI image references

Ref: docs/plans/2026-02-27-phase1-mysql-node.md

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

### Step A7: Validation (manual, after merge)

```bash
docker-compose up -d mysql
docker-compose run --rm php php bin/console akeneo:system-requirements
docker-compose run --rm php php bin/console doctrine:schema:validate
```

---

## Task B: Node 20 → 22 + Jest 26 → 29 (worktree)

**Worktree branch:** `upgrade/node-22-jest-29`

This is the **transversal blocker** — Jest 29 unblocks both Node 22 (this task) and React 18 (Phase 3).

**Files:**
- Modify: `docker-compose.yml:45` (node image)
- Modify: `docker/Dockerfile_node:14-16` (nodesource URL + base)
- Modify: `package.json` (jest, ts-jest, @types/node, jest-environment-jsdom)
- Modify: `.github/workflows/ci.yml:168` (setup-node version)
- Modify: `.github/workflows/dsm-test.yml:20`
- Modify: `.github/workflows/dsm-extract.yml:21`
- Modify: `.github/actions/setup-pim-job/action.yml:74`
- Modify: `.github/workflows/php-compat.yml:107,177`
- Modify: `.github/docs/self-hosted-runner-setup.md:77,83`
- Modify: All jest config files (add `testEnvironment: 'jsdom'`)
- Create: `.nvmrc`

### Step B1: Create worktree

```bash
cd /home/gumiranda/pim-community-dev
git worktree add .claude/worktrees/node-22 -b upgrade/node-22-jest-29
cd .claude/worktrees/node-22
```

### Step B2: Update Node Docker references

Read and modify:
- `docker-compose.yml:45` — `node:20` → `node:22`
- `.github/actions/setup-pim-job/action.yml:74` — `node:20` → `node:22`
- `.github/workflows/php-compat.yml:107,177` — `node:20` → `node:22`
- `.github/docs/self-hosted-runner-setup.md:77,83` — `node:20` → `node:22`

### Step B3: Update Dockerfile_node

Read `docker/Dockerfile_node` lines 14-16.

```dockerfile
# Before
RUN sh -c 'echo "deb https://deb.nodesource.com/node_20.x bullseye main" > /etc/apt/sources.list.d/nodesource.list'

# After
RUN sh -c 'echo "deb https://deb.nodesource.com/node_22.x bookworm main" > /etc/apt/sources.list.d/nodesource.list'
```

Also update the comment on the line above from "NodeJS 20" to "NodeJS 22".

### Step B4: Update CI workflow Node versions

Read and modify:
- `.github/workflows/ci.yml:168` — `node-version: 20` → `node-version: 22`
- `.github/workflows/dsm-test.yml:20` — `node-version: '20'` → `node-version: '22'`
- `.github/workflows/dsm-extract.yml:21` — `node-version: '20'` → `node-version: '22'`

Also update step names from "Install node 20" to "Install node 22".

### Step B5: Create .nvmrc

```
22
```

### Step B6: Update package.json — Jest 29 + @types/node

Read `package.json`. Find and update these dependencies:

```json
// devDependencies changes:
"@types/node": "^22.0.0",           // was "11.9.5"
"jest": "^29.7.0",                   // was "^26.4.2"
"ts-jest": "^29.1.0",               // was "^26.4.0"

// new devDependency:
"jest-environment-jsdom": "^29.7.0"  // NEW — extracted from Jest 27+
```

### Step B7: Update Jest config files — add testEnvironment

Find all jest config files and add `"testEnvironment": "jsdom"` where missing:

- `tests/front/common/base.jest.json` — add `"testEnvironment": "jsdom"`
- Verify `src/Akeneo/Platform/Bundle/ImportExportBundle/front/jest.config.json` already has it
- Check `tests/front/integration/jest/integration.jest.js` — verify custom environment still works
- Check all workspace jest configs that inherit from `base.jest.json`

### Step B8: Commit

```bash
git add -A
git commit -m "feat: upgrade Node 20 → 22 and Jest 26 → 29

- Docker node image: node:20 → node:22
- Dockerfile_node: nodesource node_20.x/bullseye → node_22.x/bookworm
- All CI workflows: node-version 20 → 22
- Jest: ^26.4.2 → ^29.7.0 (required for Node 22 support)
- ts-jest: ^26.4.0 → ^29.1.0
- @types/node: 11.9.5 → ^22.0.0
- Added jest-environment-jsdom (unbundled from Jest 27+)
- Added testEnvironment: 'jsdom' to all jest configs (default changed in Jest 27)
- Created .nvmrc for local dev

Ref: docs/plans/2026-02-27-phase1-mysql-node.md

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

### Step B9: Validation (manual, after merge)

```bash
yarn install
yarn lint
yarn unit
```

Note: some test files may need adjustments for Jest 29 API changes (fake timers, done callbacks). Fix iteratively.

---

## Task C: Symfony 6.4 Critical Blockers (worktree)

**Worktree branch:** `upgrade/symfony64-blockers`

This task does NOT run `composer update symfony/*`. It only fixes the code blockers so that the composer update will succeed when run manually.

**Files:**
- Modify: `config/packages/doctrine.yml:42,47` (DoctrineProvider)
- Modify: `config/packages/prod/doctrine.yml:5-13` (cache drivers)
- Modify: `src/Akeneo/Platform/Bundle/UIBundle/Http/FormLoginAuthenticator.php:76`
- Modify: `src/Oro/Bundle/SecurityBundle/Metadata/EntitySecurityMetadata.php:7+`
- Modify: `src/Oro/Bundle/SecurityBundle/Metadata/ActionMetadata.php:7+`
- Modify: `src/Akeneo/UserManagement/Component/Model/UserInterface.php:23`
- Modify: `src/Akeneo/UserManagement/Component/Model/User.php:235,252`
- Modify: `src/Oro/Bundle/SecurityBundle/Acl/Domain/RootBasedAclWrapper.php:156,164`

### Step C1: Create worktree

```bash
cd /home/gumiranda/pim-community-dev
git worktree add .claude/worktrees/sf64-blockers -b upgrade/symfony64-blockers
cd .claude/worktrees/sf64-blockers
```

### Step C2: Fix DoctrineProvider — dev config

Read `config/packages/doctrine.yml` lines 40-50.

```yaml
# Before
services:
    doctrine.result_cache_provider:
        class: Symfony\Component\Cache\DoctrineProvider
        arguments: ['@doctrine.result_cache_pool']
    doctrine.system_cache_provider:
        class: Symfony\Component\Cache\DoctrineProvider
        arguments: ['@doctrine.system_cache_pool']

# After
services:
    doctrine.result_cache_provider:
        class: Doctrine\Common\Cache\Psr6\DoctrineProvider
        arguments: ['@doctrine.result_cache_pool']
    doctrine.system_cache_provider:
        class: Doctrine\Common\Cache\Psr6\DoctrineProvider
        arguments: ['@doctrine.system_cache_pool']
```

### Step C3: Fix DoctrineProvider — prod config

Read `config/packages/prod/doctrine.yml`. Verify the cache drivers reference the same service IDs. The service class change in Step C2 is sufficient — the prod config just references service IDs, not classes directly.

If prod config also uses `Symfony\Component\Cache\DoctrineProvider` directly, apply the same fix.

### Step C4: Fix getContentType() → getContentTypeFormat()

Read `src/Akeneo/Platform/Bundle/UIBundle/Http/FormLoginAuthenticator.php` line 76.

```php
// Before
&& ($this->options['form_only'] ? 'form' === $request->getContentType() : true);

// After
&& ($this->options['form_only'] ? 'form' === $request->getContentTypeFormat() : true);
```

### Step C5: Fix \Serializable — EntitySecurityMetadata

Read `src/Oro/Bundle/SecurityBundle/Metadata/EntitySecurityMetadata.php`.

Replace `implements AclClassInfo, \Serializable` with `implements AclClassInfo`.
Replace `serialize()` / `unserialize()` methods with `__serialize()` / `__unserialize()`.

```php
// Before
class EntitySecurityMetadata implements AclClassInfo, \Serializable
{
    public function serialize(): string { return serialize([...]); }
    public function unserialize(string $serialized): void { [...] = unserialize($serialized); }
}

// After
class EntitySecurityMetadata implements AclClassInfo
{
    public function __serialize(): array { return [...]; }
    public function __unserialize(array $data): void { [...] = $data; }
}
```

### Step C6: Fix \Serializable — ActionMetadata

Same pattern as Step C5 for `src/Oro/Bundle/SecurityBundle/Metadata/ActionMetadata.php`.

### Step C7: Fix \Serializable — UserInterface + User

Read `src/Akeneo/UserManagement/Component/Model/UserInterface.php` line 23.
Remove `\Serializable` from the extends list.

Read `src/Akeneo/UserManagement/Component/Model/User.php` lines 235, 252.
Replace `serialize()` / `unserialize()` with `__serialize()` / `__unserialize()`.

### Step C8: Fix \Serializable — RootBasedAclWrapper

Read `src/Oro/Bundle/SecurityBundle/Acl/Domain/RootBasedAclWrapper.php` lines 156, 164.
Replace `serialize()` / `unserialize()` with `__serialize()` / `__unserialize()`.

### Step C9: Commit

```bash
git add -A
git commit -m "fix: resolve Symfony 6.4 critical blockers (pre-upgrade)

- Replace Symfony\Component\Cache\DoctrineProvider (removed in SF6) with
  Doctrine\Common\Cache\Psr6\DoctrineProvider
- Fix Request::getContentType() → getContentTypeFormat() (renamed in SF6)
- Migrate \Serializable interface to __serialize()/__unserialize() in:
  EntitySecurityMetadata, ActionMetadata, UserInterface, User, RootBasedAclWrapper
  (deprecated PHP 8.1, fatal PHP 8.4)

These changes are backward-compatible with Symfony 5.4 — safe to merge before
the full composer update.

Ref: docs/plans/2026-02-27-phase2-symfony64.md

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

### Step C10: Validation (on SF 5.4, before upgrade)

```bash
docker-compose run --rm php php bin/console cache:clear
PIM_CONTEXT=test make lint-back
PIM_CONTEXT=test make unit-back
```

These fixes are **backward-compatible** — they work on both SF 5.4 and SF 6.4. The Doctrine cache bridge class exists in `doctrine/cache` which is already installed. `getContentTypeFormat()` was added as an alias in SF 5.4 before `getContentType()` was removed in SF 6.

---

## Task D: Behat → Playwright — Batch 1 Scaffolding + First @critical Scenarios (worktree)

**Worktree branch:** `feat/playwright-batch1`

This task scaffolds the Playwright Page Object architecture and migrates the first wave of `@critical` scenarios. The existing setup (`tests/front/e2e/fixtures/pim.ts` + `product/edit.spec.ts`) gives us the foundation.

**Scope:** Scaffold infrastructure + translate 10-15 @critical scenarios as proof-of-concept. The remaining ~90 @critical scenarios follow the same patterns and can be batch-translated in subsequent sessions.

**Files:**
- Create: `tests/front/e2e/pages/LoginPage.ts`
- Create: `tests/front/e2e/pages/DataGridPage.ts`
- Create: `tests/front/e2e/pages/NavigationHelper.ts`
- Create: `tests/front/e2e/helpers/api-client.ts`
- Create: `tests/front/e2e/helpers/notifications.ts`
- Create: `tests/front/e2e/critical/product-crud.spec.ts`
- Create: `tests/front/e2e/critical/category.spec.ts`
- Create: `tests/front/e2e/critical/structure.spec.ts`
- Modify: `tests/front/e2e/fixtures/pim.ts` (extend with storageState auth)

### Step D1: Create worktree

```bash
cd /home/gumiranda/pim-community-dev
git worktree add .claude/worktrees/playwright-batch1 -b feat/playwright-batch1
cd .claude/worktrees/playwright-batch1
```

### Step D2: Read @critical scenarios to understand patterns

Read the Behat feature files to identify the most common @critical flows:

```bash
grep -r "@critical" tests/legacy/features/ --include="*.feature" -l | head -20
```

Then read 5-6 representative @critical .feature files completely, plus their Context class implementations in:
- `tests/legacy/features/Context/WebUser.php` (login, form filling, navigation)
- `tests/legacy/features/Context/DataGridContext.php` (grid interactions)
- `tests/legacy/features/Context/NavigationContext.php` (page routing)

### Step D3: Create LoginPage Page Object

**File:** `tests/front/e2e/pages/LoginPage.ts`

Extract and enhance the existing `login()` function from `pim.ts` into a proper Page Object:

```typescript
import {Page, expect} from '@playwright/test';

export class LoginPage {
  constructor(private page: Page) {}

  async goto() {
    await this.page.goto('/user/login');
  }

  async login(username: string, password: string) {
    await this.goto();
    await this.page.locator('input[name="_username"]').fill(username);
    await this.page.locator('input[name="_password"]').fill(password);
    await this.page.getByRole('button', {name: 'Login'}).click();
    await expect(this.page).not.toHaveURL(/\/user\/login/, {timeout: 120_000});
    await this.waitForAppReady();
  }

  async waitForAppReady() {
    await expect(this.page.locator('.AknDefault-progressContainer')).toBeHidden({timeout: 120_000});
    await expect(this.page.locator('.hash-loading-mask .loading-mask')).toBeHidden({timeout: 120_000});
  }
}
```

### Step D4: Create DataGridPage Page Object

**File:** `tests/front/e2e/pages/DataGridPage.ts`

Maps to `DataGridContext.php` (80 steps). Start with the most used interactions:

```typescript
import {Page, expect} from '@playwright/test';

export class DataGridPage {
  constructor(private page: Page) {}

  async waitForGridLoaded() {
    await this.page.waitForResponse(resp => resp.url().includes('/datagrid/'));
    await expect(this.page.locator('.AknLoadingMask')).toBeHidden({timeout: 30_000});
  }

  async getRowCount(): Promise<number> {
    return this.page.locator('table.grid tbody tr').count();
  }

  async clickRow(index: number) {
    await this.page.locator('table.grid tbody tr').nth(index).click();
  }

  async filterBy(column: string, value: string) {
    // Map Behat "I filter by ..." step
    await this.page.locator(`[data-name="${column}"] .filter-criteria-selector`).click();
    await this.page.locator('.filter-input input').fill(value);
    await this.page.locator('.filter-update').click();
    await this.waitForGridLoaded();
  }

  async expectRowCount(expected: number) {
    await expect(this.page.locator('table.grid tbody tr')).toHaveCount(expected);
  }

  async expectColumnContains(column: string, text: string) {
    await expect(this.page.locator(`td[data-column="${column}"]`).first()).toContainText(text);
  }
}
```

### Step D5: Create NavigationHelper

**File:** `tests/front/e2e/pages/NavigationHelper.ts`

Maps Behat's `NavigationContext.php` page routing to Playwright URL navigation:

```typescript
import {Page, expect} from '@playwright/test';

const ROUTES: Record<string, string> = {
  'products':        '/#/enrich/product/',
  'categories':      '/#/configuration/category/',
  'families':        '/#/configuration/family/',
  'attributes':      '/#/configuration/attribute/',
  'attribute groups': '/#/configuration/attribute-group/',
  'channels':        '/#/configuration/channel/',
  'locales':         '/#/configuration/locale/',
  'group types':     '/#/configuration/group-type/',
  'association types': '/#/configuration/association-type/',
  'users':           '/#/user/management/users/',
  'roles':           '/#/user/management/roles/',
  'import profiles':  '/#/collect/import-profile/',
  'export profiles':  '/#/spread/export-profile/',
};

export class NavigationHelper {
  constructor(private page: Page) {}

  async goTo(pageName: string) {
    const route = ROUTES[pageName.toLowerCase()];
    if (!route) throw new Error(`Unknown page: ${pageName}`);
    await this.page.goto(route);
    await expect(this.page.locator('.AknLoadingMask')).toBeHidden({timeout: 30_000});
  }

  async expectPageTitle(title: string) {
    await expect(this.page.locator('.AknTitleContainer-title')).toContainText(title);
  }
}
```

### Step D6: Create API client helper

**File:** `tests/front/e2e/helpers/api-client.ts`

Replaces `FixturesContext.php` — uses PIM REST API for test data seeding:

```typescript
import {APIRequestContext} from '@playwright/test';

let tokenCache: {token: string; expiresAt: number} | null = null;

export async function getApiToken(request: APIRequestContext): Promise<string> {
  if (tokenCache && Date.now() < tokenCache.expiresAt) return tokenCache.token;

  const response = await request.post('/api/oauth/v1/token', {
    form: {
      grant_type: 'password',
      username: 'admin',
      password: 'admin',
      client_id: '1_api_client_id',  // verify in .env or fixtures
      client_secret: 'api_secret',
    },
  });
  const data = await response.json();
  tokenCache = {token: data.access_token, expiresAt: Date.now() + (data.expires_in - 60) * 1000};
  return data.access_token;
}

export async function apiGet(request: APIRequestContext, path: string) {
  const token = await getApiToken(request);
  return request.get(`/api/rest/v1${path}`, {
    headers: {Authorization: `Bearer ${token}`},
  });
}

export async function apiPost(request: APIRequestContext, path: string, data: unknown) {
  const token = await getApiToken(request);
  return request.post(`/api/rest/v1${path}`, {
    data,
    headers: {Authorization: `Bearer ${token}`, 'Content-Type': 'application/json'},
  });
}
```

### Step D7: Create notifications helper

**File:** `tests/front/e2e/helpers/notifications.ts`

Maps Behat flash message assertions:

```typescript
import {Page, expect} from '@playwright/test';

export async function expectSuccessMessage(page: Page, text: string) {
  await expect(page.locator('.flash-messages-holder .AknMessageBox--success, .flash-messages-holder .AknFlash--success'))
    .toContainText(text, {timeout: 10_000});
}

export async function expectErrorMessage(page: Page, text: string) {
  await expect(page.locator('.flash-messages-holder .AknMessageBox--error, .flash-messages-holder .AknFlash--error'))
    .toContainText(text, {timeout: 10_000});
}

export async function expectValidationError(page: Page, field: string, message: string) {
  const fieldContainer = page.locator(`.field-container:has([data-attribute="${field}"])`);
  await expect(fieldContainer.locator('.AknFieldContainer-footer .error-message, .validation-tooltip'))
    .toContainText(message);
}
```

### Step D8: Write first @critical spec — product CRUD

**File:** `tests/front/e2e/critical/product-crud.spec.ts`

Read 3-4 @critical product scenarios from `tests/legacy/features/` and translate them.

```typescript
import {test, expect} from '@playwright/test';
import {LoginPage} from '../pages/LoginPage';
import {DataGridPage} from '../pages/DataGridPage';
import {NavigationHelper} from '../pages/NavigationHelper';
import {expectSuccessMessage} from '../helpers/notifications';

test.describe('@critical Product CRUD', () => {
  test.beforeEach(async ({page}) => {
    const loginPage = new LoginPage(page);
    await loginPage.login('Julia', 'Julia');
  });

  test('User can access the product grid', async ({page}) => {
    const nav = new NavigationHelper(page);
    await nav.goTo('products');
    const grid = new DataGridPage(page);
    const count = await grid.getRowCount();
    expect(count).toBeGreaterThan(0);
  });

  // More @critical scenarios translated from .feature files...
  // Each test maps to one Behat Scenario tagged @critical
});
```

### Step D9: Write first @critical spec — structure (group types, families)

**File:** `tests/front/e2e/critical/structure.spec.ts`

Translate 3-4 @critical structure management scenarios.

### Step D10: Write first @critical spec — category tree

**File:** `tests/front/e2e/critical/category.spec.ts`

Translate 2-3 @critical category tree scenarios.

### Step D11: Run Playwright tests locally to validate

```bash
npx playwright test tests/front/e2e/critical/ --reporter=line
```

Fix any locator issues, timing problems, or data dependencies.

### Step D12: Commit

```bash
git add -A
git commit -m "feat: scaffold Playwright Page Objects and migrate first @critical scenarios

Infrastructure:
- LoginPage, DataGridPage, NavigationHelper Page Objects
- API client helper for test data seeding (replaces FixturesContext)
- Notification helper for flash message assertions

First @critical scenarios:
- Product CRUD (grid access, edit, save)
- Structure management (group types, families)
- Category tree navigation

These run alongside Behat — no Behat scenarios removed yet.

Ref: docs/plans/2026-02-27-behat-to-playwright.md

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

### Step D13: Validation — dual-run in CI

After merge, both Behat `critical` suite AND new Playwright `critical/` specs run in CI.
When Playwright passes 3+ times consistently, tag the corresponding Behat scenarios with `@skip`.

---

## Merge Strategy

```
master ──────┬─── merge Task A (MySQL 8.4) ─── CI green? ───┐
             │                                                │
             ├─── merge Task B (Node 22 + Jest 29) ──────────┤
             │                                                │
             ├─── merge Task C (SF6.4 blockers) ─────────────┤
             │                                                │
             └─── merge Task D (Playwright Batch 1) ─────────┘
                                                              │
                                                              ▼
                                                     All merged on master
                                                     Ready for:
                                                     - Phase 2: composer update symfony/*
                                                     - Phase 3: React 18 (unblocked by Jest 29)
                                                     - Behat Batch 2: next @critical scenarios
```

Merge order: **A first** (smallest, least risk), then **C** (backward-compatible), then **B** (largest, may need test fixes), then **D** (additive only, no breaking changes).

---

## What This Plan Does NOT Cover

| Item | Why | When |
|------|-----|------|
| `composer update symfony/*` | Needs manual validation of dependency conflicts | After Task C is merged, in a dedicated session |
| React 18 migration | Blocked by Jest 29 (Task B) | After Task B is merged |
| Behat Batch 2-5 | Follows same pattern as Task D, Claude can batch-translate | Ongoing after Task D merged |
| PHP 8.2→8.3 Docker image | Comes with the full SF6.4 composer update | Phase 2 main session |
| PHIVE worktree merge | Already complete, independent | Can merge anytime |
