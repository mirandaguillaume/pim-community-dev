# SGBD Abstraction — Channel Pilot PR

**Date:** 2026-03-19
**Branch:** `upgrade/sgbd-abstraction`
**Scope:** Channel bounded context (pilot for Strangler Fig migration)

## Context

The Akeneo PIM codebase contains 591 raw SQL queries across 398 files. 51% of these reside
outside the Infrastructure layer (anti-pattern). Many use MySQL-specific functions that prevent
future PostgreSQL migration.

This pilot PR establishes the abstraction pattern on the Channel bounded context (~10-15 queries,
low risk) before extending it to larger domains (Product, Attribute, DQI).

## Strategy

- **Strangler Fig progressif**: one PR per functional domain
- **DBAL QueryBuilder + platform-aware helpers**: a shared trait generates native SQL per platform
- **COALESCE used directly**: no helper needed (ANSI SQL)

## Design

### 1. Shared Trait: `DatabasePlatformTrait`

**Location:** `src/Akeneo/Tool/Component/StorageUtils/Database/DatabasePlatformTrait.php`

Provides 5 helper methods that generate platform-native SQL:

| Helper | MySQL | PostgreSQL |
|--------|-------|------------|
| `jsonArrayAgg(expr)` | `JSON_ARRAYAGG(expr)` | `jsonb_agg(expr)` |
| `jsonObjectAgg(key, val)` | `JSON_OBJECTAGG(key, val)` | `jsonb_object_agg(key, val)` |
| `jsonRemoveKey(doc, key)` | `JSON_REMOVE(doc, '$.key')` | `(doc - 'key')` |
| `regexpMatch(col, pattern)` | `col REGEXP pattern` | `col ~ pattern` |
| `groupConcat(expr, sep, order)` | `GROUP_CONCAT(expr ORDER BY order SEPARATOR sep)` | `STRING_AGG(expr, sep ORDER BY order)` |

The trait requires an abstract `getConnection(): Connection` method implemented by the using class.

### 2. Channel Classes Modified

| Class | MySQL-ism removed | Change |
|-------|-------------------|--------|
| `SqlFindChannels` | `JSON_OBJECTAGG`, `JSON_REMOVE`, `IFNULL` | Use trait helpers + COALESCE |
| `FindActivatedCurrencies` | `JSON_ARRAYAGG`, `IS TRUE` | Use trait helper, modernize to DBAL Connection |
| `SqlGetChannelCodeWithLocaleCodes` | `JSON_ARRAYAGG` | Use trait helper |
| `IsChannelUsedInProductProductExportJob` | `REGEXP` | Use trait helper + parameterized binding |

### 3. Files

| File | Action |
|------|--------|
| `Tool/Component/StorageUtils/Database/DatabasePlatformTrait.php` | Create |
| `Tool/Component/StorageUtils/tests/Spec/Database/DatabasePlatformTraitSpec.php` | Create |
| `Channel/back/Infrastructure/Query/Sql/SqlFindChannels.php` | Modify |
| `Channel/back/Infrastructure/Doctrine/Query/FindActivatedCurrencies.php` | Modify |
| `Channel/back/Infrastructure/Query/Sql/SqlGetChannelCodeWithLocaleCodes.php` | Modify |
| `Channel/back/Infrastructure/Query/Sql/IsChannelUsedInProductProductExportJob.php` | Modify |

### 4. Out of Scope

- Channel queries in DQI, Enrichment, Platform (future PRs)
- ORM repositories (already portable via DQL)
- Application/Command layer (does not exist for Channel)
- PostgreSQL CI tests (no PG available; trait tested via unit mocks)

## Future PRs (Strangler Fig sequence)

1. **Channel** (this PR) — pilot, validates pattern
2. **Attribute** — ~25-30 queries, JSON operations
3. **Product** — ~80 queries, heaviest domain
4. **DQI** — GROUP_CONCAT, IF(), complex mask queries
5. **Category** — complete the existing hexagonal BC
6. **Remaining Bundle/Component** — long tail
