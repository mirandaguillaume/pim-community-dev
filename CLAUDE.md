# CLAUDE.md

## Context

Akeneo PIM Community Edition. PHP 8.2 / Symfony / React 17. Docker mandatory for PHP (`enforce-docker-php.sh` hook).

Two architecture patterns coexist: modern hexagonal (`back/Application|Domain|Infrastructure`) and legacy (`Component/Bundle`).

## Constraints

- PHP runs in Docker: `docker-compose run --rm php php <command>`
- PHPStan levels vary per context — check each `phpstan.neon`
- `*Spec.php` and `*Integration.php` excluded from php-cs-fixer

## Commands

```bash
# CI checks (must pass before push)
PIM_CONTEXT=test make lint-back          # PHPStan + php-cs-fixer
PIM_CONTEXT=test make unit-back          # PHPSpec
PIM_CONTEXT=test make coupling-back      # Architecture coupling
PIM_CONTEXT=test make acceptance-back    # Behat acceptance

# Single test
docker-compose run --rm php php vendor/bin/phpspec run src/path/to/Spec.php
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter TestClassName
make end-to-end-legacy O=path/to/feature.feature:23

# Bounded context pattern: <context>-<type>-back (unit|acceptance|integration|lint|coupling)
make connectivity-connection-unit-back O=path/to/spec

# Frontend
yarn lint && yarn lint-fix
yarn unit
npx playwright test                      # E2E (config: playwright.config.ts)

# Code style auto-fix
make fix-cs-back
```

## CI

GitHub Actions (`.github/workflows/ci.yml`). Self-hosted runners (5x on Hetzner CCX33). Path-filtered: backend/frontend/ci changes skip irrelevant jobs. Behat uses `--rerun` for flaky retry.

## MCP Servers

Prefer MCP tools over CLI equivalents: **serena** (symbolic code editing), **grepai** (semantic search), **context7** (library docs), **git**, **composer**.
