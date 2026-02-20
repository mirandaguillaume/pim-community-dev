# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Akeneo PIM (Product Information Management) Community Edition. PHP 8.2/Symfony backend with React 17 frontend. Dockerized development environment with MySQL 8, Elasticsearch 8.4, MinIO (object storage), and Google PubSub emulator for async messaging.

## Architecture

### Two Architectural Patterns Coexist

**Modern bounded contexts** (`Connectivity/Connection`, `Channel`, `Category`, `UserManagement`, `Platform/Job`, `Pim/Enrichment/Product`, `Pim/Automation/DataQualityInsights`, `Platform/Installer`, `Tool/Bundle/MeasureBundle`, `identifier-generator` in `components/`) follow hexagonal architecture under a `back/` directory:
- `Application/` — Use cases, command/query handlers (CQRS pattern)
- `Domain/` — Business logic, value objects, interfaces (no framework dependency)
- `Infrastructure/` — Symfony bundles, Doctrine persistence, external service adapters
- PSR-4 autoloading configured in `composer.json` per bounded context

**Legacy code** (`src/Akeneo/Pim/Enrichment/Component/`, `src/Akeneo/Pim/Structure/Component/`, Oro bundles) uses the older Symfony `Component/Bundle` pattern:
- `Component/` — Business logic (models, normalizers, validators, repositories)
- `Bundle/` — Symfony DI wiring, controllers, templates, DIC service definitions

### Key Domain Directories
- `src/Akeneo/Pim/Enrichment/` — Products, product models, values, completeness (the core domain)
- `src/Akeneo/Pim/Structure/` — Attributes, families, family variants, attribute groups
- `src/Akeneo/Connectivity/Connection/` — API connections, apps, marketplace, webhooks
- `src/Akeneo/Pim/Automation/DataQualityInsights/` — Data quality scoring
- `src/Akeneo/Platform/` — Cross-cutting concerns (jobs, installer, communication channel)
- `src/Akeneo/Tool/` — Shared bundles (BatchQueue, FileStorage, Measure, Elasticsearch, API)
- `components/identifier-generator/` — Standalone component with its own test/lint config

### Frontend Structure
- **front-packages/**: Shared workspaces (akeneo-design-system, shared) built via `yarn packages:build`
- **src/**/front/**: Feature-specific frontend code colocated with its bounded context
- **frontend/**: Webpack build config and acceptance test infrastructure
- React 17, styled-components, React Query, Redux. Yarn workspaces.

### Service Wiring
- Symfony services defined in `src/*/Resources/config/` (YAML files)
- Configuration loaded via `config/services/` and bundle extensions
- Most services use constructor injection via DIC; newer bounded contexts use autowiring

## Common Commands

### Environment
```bash
make dependencies       # Install composer + yarn dependencies
make up                 # Start Docker services (use C='httpd mysql elasticsearch' to select)
make down               # Stop Docker services
make pim-dev            # Full dev setup (cache, assets, DB with demo data)
make pim-test           # Prepare test environment (APP_ENV=test)
make cache              # Warm Symfony cache
make database           # Drop and recreate database
```

### Frontend
```bash
make javascript-dev     # Webpack watch mode
make javascript-prod    # Production build
make css                # Less -> CSS
make front-packages     # Build shared workspaces
make assets             # Refresh Symfony public bundles (symlinks)
```

### Backend
```bash
make fix-cs-back                          # php-cs-fixer auto-fix (main config)
make lint-fix-back O=src/path/to/file.php # Symfony-style auto-fix on specific path
```

### CI Checks (must pass before pushing)
```bash
PIM_CONTEXT=test make find-legacy-translations  # Legacy translation usage
PIM_CONTEXT=test make static-back               # Container lint + pullup checks
PIM_CONTEXT=test make deprecation-back          # PHPStan deprecation analysis
PIM_CONTEXT=test make lint-back                 # PHPStan + php-cs-fixer dry-run
PIM_CONTEXT=test make coupling-back             # Architecture coupling (php-coupling-detector)
PIM_CONTEXT=test make unit-back                 # PHPSpec unit tests
PIM_CONTEXT=test make acceptance-back           # Behat acceptance tests
```

### Testing

**Running a single test:**
```bash
# PHPSpec — single spec file
docker-compose run --rm php php vendor/bin/phpspec run src/path/to/Spec.php

# PHPUnit — single test class or filter
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter TestClassName

# PHPUnit — specific test suite
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --testsuite Akeneo_Connectivity_Connection_Integration

# Behat legacy — specific feature/line
make end-to-end-legacy O=path/to/feature.feature:23
```

**Bounded context make targets** (see `make-file/*.mk`):
```bash
# Pattern: <context>-<type>-back where type is unit|acceptance|integration|e2e|lint|coupling
make connectivity-connection-unit-back
make connectivity-connection-integration-back
make channel-acceptance-back
make data-quality-insights-lint-back
make identifier-generator-acceptance-back
# Pass options with O=
make connectivity-connection-unit-back O=path/to/spec
```

**Frontend tests:**
```bash
yarn lint && yarn lint-fix    # ESLint + Prettier
yarn unit                     # Jest unit tests
yarn integration              # Jest integration tests
yarn acceptance tests/features # Cucumber acceptance tests
```

### Docker
```bash
# Node/Yarn commands go through the node service
docker-compose run -u node --rm node yarn <command>
# Enable Xdebug
make xdebug-on
# Services needed for integration tests
APP_ENV=test C='httpd mysql elasticsearch object-storage pubsub-emulator' make up
```

> **Note:** PHP/Composer Docker usage is enforced by the Claude Code hook `enforce-docker-php.sh`.

## CI Pipeline (GitHub Actions)

Defined in `.github/workflows/ci.yml`. Job dependency graph:
```
image → prepare → front-build, front-lint, front-unit, lint-back, phpspec, static-checks
                → acceptance-back → phpunit (6 shards, needs db-seed), behat-legacy, cypress
                → data-migrations, deptrac (6 matrix configs)
```

All jobs converge to `ci-success` gate. Deptrac runs parallelized for: structure, core, job, enrichment, channel, importexport.

## Code Style

### PHP
- PSR-2 + ordered imports via php-cs-fixer (config: `.php_cs.php`)
- `*Spec.php` and `*Integration.php` files are **excluded** from cs-fixer
- PHPStan levels vary: level 2 for `src/Akeneo/Pim`, level 8 for Connectivity Application/Domain, level 5 for Connectivity Infrastructure. Check each context's `phpstan.neon`.
- Architecture coupling enforced by `php-coupling-detector` with per-context `.php_cd.php` config files

### JavaScript/TypeScript
- ESLint + Prettier (2-space indent, single quotes)
- React components: PascalCase. Hooks: `use*` prefix.
- Run `yarn lint-fix` for auto-formatting

## PHPUnit Test Suites (phpunit.xml.dist)
- `PIM_Integration_Test` — Main integration tests (files ending `Integration.php` in `src/` and `tests/`)
- `PIM_Migration_Test` — Database migration tests (`upgrades/test_schema/`)
- `End_to_End` — E2E backend tests (files ending `EndToEnd.php`)
- Per-context: `Akeneo_Connectivity_Connection_Integration`, `Data_Quality_Insights`, `Category`, `Enrichment_Product`, `Identifier_Generator_PhpUnit`, `Akeneo_Measurement_*`

## Naming Conventions for Tests
- PHPSpec files: `*Spec.php` (mirror source tree under `spec/`)
- PHPUnit integration: `*Integration.php`
- PHPUnit E2E: `*EndToEnd.php`
- PHPUnit acceptance: `*Test.php` (in `tests/Acceptance/` directories)
- Behat features: `*.feature` in `tests/features/` or context-specific `tests/` dirs

## MCP Servers (prefer over CLI equivalents)

- **github** — Use MCP GitHub tools (`pull_request_read`, `create_pull_request`, `actions_list`, `get_job_logs`, etc.) instead of the `gh` CLI for all GitHub operations (PRs, issues, CI logs).
- **serena** — Use symbolic editing tools (`find_symbol`, `replace_symbol_body`, `insert_after_symbol`, `find_referencing_symbols`) for PHP/JS code modifications instead of line-based edits.
- **grepai** — Use `grepai_search` as the primary tool for semantic code exploration (search by intent, not exact text). Use `grepai_trace_callers`/`grepai_trace_callees` to understand call graphs. Fall back to Grep/Glob only for exact string matching.
- **context7** — Use for fetching up-to-date library documentation.
- **git** — Use MCP git tools for status, diff, commit, log operations.
- **composer** — Use for installing PHP packages.
