# Phase 1 — MySQL 8.0→8.4 + Node 20→22

**Date**: 2026-02-27
**Status**: Ready for implementation
**Effort**: S (1 session with Claude)
**Deadline**: April 2026 (MySQL 8.0 + Node 20 EOL)

## MySQL 8.0 → 8.4

### Critical Blockers

#### 1. `--default-authentication-plugin` removed in 8.4

The `docker-compose.yml:72` uses:
```yaml
command: '--default-authentication-plugin=mysql_native_password --log_bin_trust_function_creators=1'
```

**This flag was fully removed in MySQL 8.4.** The container will refuse to start.

**Fix**: Replace with `--mysql-native-password=ON` (MySQL 8.4 syntax) or migrate all users to `caching_sha2_password` and remove the flag entirely.

#### 2. `PimRequirements.php` version hard-block

`src/Akeneo/Platform/PimRequirements.php:28-29`:
```php
final public const LOWEST_REQUIRED_MYSQL_VERSION = '8.0.30';
final public const GREATEST_REQUIRED_MYSQL_VERSION = '8.1.0';  // blocks 8.4!
```

The application checks `$version >= 8.0.30 && $version < 8.1.0`. MySQL 8.4 fails this check.

**Fix**: Raise upper bound to `'9.0.0'` (or `'8.5.0'`).

#### 3. Doctrine `server_version`

`config/packages/doctrine.yml:18`:
```yaml
server_version: '8.0'
```

Controls SQL generation. Must be updated to `'8.4'`.

### All Files to Modify

| File | Line | Current | Target |
|------|------|---------|--------|
| `docker-compose.yml` | 71 | `mysql:8.0.30` | `mysql:8.4` |
| `docker-compose.yml` | 72 | `--default-authentication-plugin=mysql_native_password` | `--mysql-native-password=ON` |
| `config/packages/doctrine.yml` | 18 | `server_version: '8.0'` | `server_version: '8.4'` |
| `src/Akeneo/Platform/PimRequirements.php` | 28 | `'8.0.30'` | `'8.0.30'` (keep) |
| `src/Akeneo/Platform/PimRequirements.php` | 29 | `'8.1.0'` | `'9.0.0'` |
| `.github/actions/setup-pim-job/action.yml` | 72 | `mysql:8.0.30` | `mysql:8.4` |
| `.github/workflows/php-compat.yml` | 106, 176 | `mysql:8.0.30` | `mysql:8.4` |
| `.github/docs/self-hosted-runner-setup.md` | 77, 203 | `mysql:8.0.30` | `mysql:8.4` |

### Non-Blocking Observations

- **Charset**: Already `utf8mb4` + `utf8mb4_unicode_ci` everywhere. Two migrations use `utf8mb4_0900_ai_ci` (MySQL 8.0 default) — valid in 8.4.
- **SQL features**: `WITH RECURSIVE`, `ROW_NUMBER()`, `JSON_TABLE` all used — all supported in 8.4.
- **`--log_bin_trust_function_creators=1`**: Deprecated in 8.0, behavior changed in 8.4. Investigate if still needed, or remove.
- **`json → string` Doctrine mapping** (`doctrine.yml:20`): Standard workaround, works in 8.4.
- **INSTANT columns migration** (`V20230622175500`): MySQL 8.4 changed INSTANT column behavior — verify this migration still works.

### Validation

```bash
docker-compose up -d mysql
docker-compose run --rm php php bin/console akeneo:system-requirements
docker-compose run --rm php php bin/console doctrine:schema:validate
PIM_CONTEXT=test make unit-back
PIM_CONTEXT=test make acceptance-back
```

---

## Node 20 → 22

### Critical Blocker: Jest 26

**Jest 26 does not support Node 22.** Node 22 requires Jest 29+.

This is a **shared blocker with Phase 3 (React 18)** — Jest must be upgraded regardless. Consider doing it here as a foundation for Phase 3.

| Package | Current | Target |
|---------|---------|--------|
| `jest` | `^26.4.2` | `^29.7.0` |
| `ts-jest` | `^26.4.0` | `^29.1.0` |
| `@types/node` | `11.9.5` | `^22.0.0` |
| `jest-environment-jsdom` | (bundled) | `^29.7.0` (separate package in Jest 29) |

### All Files to Modify

| File | Line | Current | Target |
|------|------|---------|--------|
| `docker-compose.yml` | 45 | `node:20` | `node:22` |
| `docker/Dockerfile_node` | 14-16 | `node_20.x bullseye` | `node_22.x bookworm` |
| `.github/workflows/ci.yml` | 168 | `node-version: 20` | `node-version: 22` |
| `.github/workflows/dsm-test.yml` | 20 | `node-version: '20'` | `node-version: '22'` |
| `.github/workflows/dsm-extract.yml` | 21 | `node-version: '20'` | `node-version: '22'` |
| `.github/actions/setup-pim-job/action.yml` | 74 | `node:20` | `node:22` |
| `.github/workflows/php-compat.yml` | 107, 177 | `node:20` | `node:22` |
| `.github/docs/self-hosted-runner-setup.md` | 77, 83 | `node:20` | `node:22` |
| `package.json` | 79 | `"@types/node": "11.9.5"` | `"@types/node": "^22.0.0"` |
| `package.json` | — | `"jest": "^26.4.2"` | `"jest": "^29.7.0"` |
| `package.json` | — | `"ts-jest": "^26.4.0"` | `"ts-jest": "^29.1.0"` |

### Additional Concerns

- **Dockerfile base**: `debian:bullseye-slim` is nearing EOL. With Node 22, switch to `bookworm` (Debian 12).
- **No `.nvmrc` or `.node-version`** exists — consider adding `.nvmrc` with `22` for local dev.
- **No native addons** — no `node-gyp`, no `node-sass`. Pure JS stack. Low risk.
- **Webpack 5** (`^5.75.0`) — compatible with Node 22.
- **`cucumber: 4.0.0`** in package.json — very old (2017), replaced by `@cucumber/cucumber`. Unrelated to Node upgrade but worth flagging.

### Jest 26→29 Migration Notes

Key breaking changes in Jest 27/28/29:
- `testEnvironment` default changed from `jsdom` to `node` → must add `testEnvironment: 'jsdom'` to all jest configs
- `jest-environment-jsdom` is now a separate package
- `jest.useFakeTimers()` API changed (modern timers default)
- `done` callback in async tests deprecated
- `ts-jest` config format changed

All jest config files to update:
- `tests/front/common/base.jest.json` — add `testEnvironment: 'jsdom'`
- `src/Akeneo/Platform/Bundle/ImportExportBundle/front/jest.config.json` — already has it
- `tests/front/integration/jest/integration.jest.js` — uses custom environment, verify
- All workspace package jest configs

### Validation

```bash
yarn install
yarn lint
yarn unit
npx playwright test
```
