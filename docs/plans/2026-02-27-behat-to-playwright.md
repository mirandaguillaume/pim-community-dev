# Behat → Playwright Migration

**Date**: 2026-02-27
**Status**: Ready for implementation
**Effort**: XL (continuous, parallel to all other phases)
**Target**: Replace Behat+Selenium E2E tests entirely with Playwright

## Scope Inventory

### Total Scenarios

| Category | Scenarios | Migration target |
|----------|-----------|-----------------|
| Legacy E2E `@javascript` (Selenium) | **294** | **Yes — primary target** |
| Legacy non-`@javascript` (no browser) | ~366 | Evaluate: keep as Behat or convert to API tests |
| Back-end acceptance `@acceptance-back` | ~79 | **No** — keep as Behat (no UI) |
| Identifier-generator acceptance | 188 | **No** — purely back-end HTTP tests |
| **Total in project** | **974** | **294 to migrate** |

### Feature Files by Bounded Context

| Context | .feature files | Scenarios (est.) | `@javascript` | Batch |
|---------|---------------|------------------|----------------|-------|
| `pim/enrichment/product` | 256 | 572 | ~180 | 2, 3 |
| `pim/structure` | ~56 | ~90 | ~40 | 4 |
| `platform` | 39 | 55 | ~30 | 5 |
| `channel` | 13 | 17 | ~10 | 4 |
| `user-management` | 8 | 16 | ~10 | 5 |
| `connectivity` | 3 | 4 | ~4 | 6 |
| **Subtotal legacy** | **316** | **660** | **~294** | |

### `@critical` Scenarios

**104 occurrences** of `@critical` tag. These run in a separate CI suite (`critical`) with filter `@critical && ~@ce && ~@skip && ~@skip-pef`.

**This is Batch 1** — migrate these first because:
1. Separate CI job — can be swapped independently
2. Cover the most important user flows
3. Highest CI time savings (full suite bootstrap for ~104 scenarios)

---

## Behat Configuration

### Suites (from `behat.yml`)

| Suite | Paths | Filter | Use |
|-------|-------|--------|-----|
| `critical` | `tests/legacy/features` | `@critical && ~@ce && ~@skip && ~@skip-pef` | First CI run |
| `all` | `tests/legacy/features` + Connectivity | `~@skip && ~@critical && ~@unstable && ...` | Sharded CI run |
| `chipmunk` | `pim/` + `platform/` | same exclusions | Named branch run |
| `raccoon` | `user-management/` + `channel/` + `platform/` | same exclusions | Named branch run |
| `weasel` | `platform/` | same exclusions | Named branch run |

### Sharding Strategy

`.github/scripts/run_behat.sh`:
1. `vendor/bin/behat --list-scenarios` → list all matching scenarios
2. If `.test-timings/behat.json` exists → **greedy bin-packing** (largest first, fill lightest shard)
3. Fallback → **cksum hash-based** round-robin
4. On failure → `--rerun` retry (flaky tolerance)

CI matrix: 10 shards, `max-parallel: 8`.

---

## Context Classes (Step Definitions)

### Top 4 — The Core (340 steps, used by every suite)

| Context | Steps | Role |
|---------|-------|------|
| `Context/WebUser.php` | **167** | All UI interactions: form fills, clicks, field checks, tab nav |
| `Context/FixturesContext.php` | **93** | Data setup: `Given the following products/families/attributes:` |
| `Context/DataGridContext.php` | **80** | Grid: filtering, sorting, pagination, column checks |
| `Context/SecurityContext.php` | **53** | Login, ACL, role checks |

### Domain Contexts (277 steps across 23 classes)

| Context | Steps | Domain |
|---------|-------|--------|
| `NavigationContext.php` (both versions) | 32 + 28 | Page navigation |
| `AssertionContext.php` | 26 | General assertions |
| `Domain/Spread/ExportProfilesContext.php` | 14 | Export |
| `Domain/Enrich/AttributeTabContext.php` | 10 | Product form tabs |
| `Domain/TreeContext.php` | 9 | Category tree |
| `JobContext.php` | 9 | Import/export jobs |
| Others (16 classes) | 3-8 each | Various |

**Total**: ~617 step definitions across 27 Context classes.

---

## Existing Playwright Setup

### Config (`playwright.config.ts`)
```typescript
testDir: 'tests/front/e2e',
timeout: 120_000,
retries: 1,
workers: 1,
use: {
  baseURL: process.env.PIM_URL || 'http://localhost:8080',
  trace: 'on-first-retry',
  screenshot: 'only-on-failure',
},
```

### Existing Tests
- **1 spec file**: `tests/front/e2e/product/edit.spec.ts`
- **1 fixture file**: `tests/front/e2e/fixtures/pim.ts` (exports: `login()`, `goToProductsGrid()`, `selectFirstProduct()`, `saveProduct()`, etc.)

---

## Playwright Architecture (target)

### Directory Structure

```
tests/front/e2e/
├── fixtures/
│   ├── pim.ts              # Auth + base fixtures (exists)
│   ├── data-seeder.ts      # API-based data seeding (NEW)
│   └── types.ts            # Shared TypeScript types
├── pages/
│   ├── LoginPage.ts        # → replaces WebUser login steps
│   ├── DataGridPage.ts     # → replaces DataGridContext (80 steps)
│   ├── ProductPage.ts      # → replaces ProductContext + WebUser product steps
│   ├── CategoryTreePage.ts # → replaces TreeContext
│   ├── ImportPage.ts       # → replaces ImportProfilesContext
│   └── ExportPage.ts       # → replaces ExportProfilesContext
├── helpers/
│   ├── job-runner.ts       # Wait for async jobs via API polling
│   ├── notifications.ts    # Flash message assertions
│   └── api-client.ts       # Direct API calls for setup/teardown
├── product/
│   ├── edit.spec.ts        # (exists)
│   ├── create.spec.ts
│   ├── import.spec.ts
│   └── validation.spec.ts
├── structure/
│   ├── attribute.spec.ts
│   ├── family.spec.ts
│   └── group-type.spec.ts
├── platform/
│   ├── security.spec.ts
│   └── import-export.spec.ts
└── critical/
    └── smoke.spec.ts       # Batch 1: all @critical scenarios
```

### Context → Page Object Mapping

| Behat Context | Playwright Equivalent | Strategy |
|---------------|----------------------|----------|
| `WebUser.php` (167 steps) | `LoginPage.ts` + individual Page Objects | Split by domain: login → LoginPage, forms → per-page PO |
| `FixturesContext.php` (93 steps) | `data-seeder.ts` + `api-client.ts` | **API calls**, not UI. Use PIM REST API to create products/families/attributes |
| `DataGridContext.php` (80 steps) | `DataGridPage.ts` | Reusable PO: filter, sort, paginate, check columns |
| `SecurityContext.php` (53 steps) | `LoginPage.ts` + Playwright `storageState` | Auth via `storageState` (login once, reuse session) |
| `NavigationContext.php` (60 steps) | Built into each Page Object | `page.goto()` + route helpers |
| `JobContext.php` (9 steps) | `job-runner.ts` | Poll `/api/rest/v1/job-executions` until complete |

### Data Seeding Strategy

**Key challenge**: Behat uses `FixturesContext` to create data via Doctrine (direct DB access). Playwright cannot do this.

**Options**:
1. **REST API seeding** (recommended): Use PIM's REST API to create products, families, attributes before each test
2. **CLI seeding**: `docker-compose exec php php bin/console` to run data commands
3. **SQL fixtures**: Pre-loaded database snapshot per test suite

**Recommendation**: Option 1 (REST API) for most data, Option 2 for catalog configuration that requires console commands.

```typescript
// data-seeder.ts
export async function seedProduct(request: APIRequestContext, data: ProductData) {
  const response = await request.post('/api/rest/v1/products', {
    data: { identifier: data.sku, family: data.family, values: data.values },
    headers: { Authorization: `Bearer ${await getToken(request)}` },
  });
  expect(response.ok()).toBeTruthy();
}
```

---

## Migration Batches

### Batch 1 — `@critical` (~104 scenarios)

**Goal**: Replace the entire `critical` Behat suite with Playwright.

| Sub-batch | Scenarios (est.) | Flows |
|-----------|------------------|-------|
| Login + navigation | ~10 | Authentication, menu navigation, breadcrumbs |
| Product CRUD | ~30 | Create, edit, save, delete products |
| Import/Export | ~20 | Launch import, verify results, export products |
| Family + Attributes | ~15 | Structure management |
| Category tree | ~10 | Tree navigation, create/move categories |
| Other critical flows | ~19 | ACL, locales, channels |

**Estimated effort**: 2-3 sessions with Claude.
**Validation**: Run both Behat `critical` and Playwright in parallel for 3+ CI runs, then disable Behat `critical`.

### Batch 2 — Product enrichment (~180 `@javascript` scenarios)

Largest batch. Subgroups:
- Product validation (~91 scenarios)
- Product import (~84 scenarios, many with data tables)
- Product model (~54 scenarios)
- Product edit form (~45 scenarios)
- Mass edit/actions (~36 scenarios)

### Batch 3 — Structure (~40 `@javascript` scenarios)
- Families, attributes, group types, association types

### Batch 4 — Platform + Channel (~40 `@javascript` scenarios)
- CSRF security tests (18 scenarios — may convert to API tests instead)
- Import/export profiles
- Channel management

### Batch 5 — User Management + Connectivity (~14 `@javascript` scenarios)
- User CRUD, roles, ACL
- Connection settings

---

## CI Integration

### During Migration (dual-run)

```yaml
# Behat continues as-is (shrinking)
behat-legacy:
  strategy:
    matrix:
      shard: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]  # reduces as scenarios migrate

# Playwright grows
playwright:
  strategy:
    matrix:
      shard: [1]  # grows to 2-3 as more tests added
```

### After Migration (target)

```yaml
# Behat removed entirely
playwright:
  strategy:
    matrix:
      shard: [1, 2, 3]  # Playwright is 3-5x faster, fewer shards needed
  timeout-minutes: 30    # down from 120
```

### Expected CI Time Impact

| Milestone | Behat time | Playwright time | Total E2E |
|-----------|-----------|----------------|-----------|
| Today | ~53 min (10 shards) | ~5 min (1 spec) | ~53 min |
| After Batch 1 | ~45 min (10 shards, fewer scenarios) | ~10 min | ~45 min |
| After 50% | ~25 min (5-6 shards) | ~12 min | ~25 min |
| After 100% | 0 | ~12-15 min (3 shards) | ~15 min |

---

## Sample Translations

### Simple CRUD → Playwright

**Behat** (group type creation):
```gherkin
Scenario: Successfully create a group type
  Given I am logged in as "Peter"
  And I am on the group types page
  And I create a new group type
  When I fill in the following information in the popin:
    | Code | special |
  And I press the "Save" button
  Then I should see the text "Group type successfully created"
```

**Playwright**:
```typescript
test('Create a group type', async ({ page }) => {
  await login(page, 'peter', 'peter');
  await page.goto('/#/configuration/group-type');
  await page.getByRole('button', { name: /create/i }).click();
  await page.locator('.modal input[name="code"]').fill('special');
  await page.getByRole('button', { name: 'Save' }).click();
  await expect(page.getByText('Group type successfully created')).toBeVisible();
});
```

### Scenario Outline → `test.each`

**Behat**:
```gherkin
Scenario Outline: Display fields for attribute types
  When I create a "<type>" attribute
  Then I should see the <fields> fields
  Examples:
    | type   | fields                         |
    | Date   | Min date, Max date             |
    | Number | Min number, Max number, ...    |
```

**Playwright**:
```typescript
const attributeTypes = [
  { type: 'Date', fields: ['Min date', 'Max date'] },
  { type: 'Number', fields: ['Min number', 'Max number'] },
];
for (const { type, fields } of attributeTypes) {
  test(`${type} attribute shows correct fields`, async ({ page }) => {
    // ...
    for (const field of fields) {
      await expect(page.getByLabel(field)).toBeVisible();
    }
  });
}
```

### Data Table Setup → API Seeding

**Behat**:
```gherkin
Given the following products:
  | sku | family | name-en_US |
  | foo | bar    | Foo Product |
```

**Playwright**:
```typescript
test.beforeEach(async ({ request }) => {
  await seedProduct(request, {
    identifier: 'foo',
    family: 'bar',
    values: { name: [{ locale: 'en_US', scope: null, data: 'Foo Product' }] },
  });
});
```

---

## Progress Tracking

| Batch | Status | Migrated | Total | CI saved |
|-------|--------|----------|-------|----------|
| 1 — @critical | Pending | 0 | ~104 | — |
| 2 — Product enrichment | Pending | 0 | ~180 | — |
| 3 — Structure | Pending | 0 | ~40 | — |
| 4 — Platform + Channel | Pending | 0 | ~40 | — |
| 5 — User Mgmt + Connectivity | Pending | 0 | ~14 | — |
| **Total @javascript** | | **0** | **~294** | **0 min** |

Non-`@javascript` legacy scenarios (366): evaluate after E2E migration — some may be redundant with back-end acceptance tests.
