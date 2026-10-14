# CLAUDE.md

## Context

Akeneo PIM Community Edition. PHP 8.3 / Symfony / React 17. Docker mandatory for PHP (`enforce-docker-php.sh` hook).

Two architecture patterns coexist: modern hexagonal (`back/Application|Domain|Infrastructure`) and legacy (`Component/Bundle`).

## Constraints

- PHP runs in Docker: `docker-compose run --rm php php <command>`
- PHPStan levels vary per context — check each `phpstan.neon`
- `*Integration.php` excluded from php-cs-fixer

## Commands

Tasks run through **Castor**, not `make` — the root Makefile was removed in #128 and
`castor list` enumerates every task with its description.

```bash
# CI checks (must pass before push)
castor test:lint-back                    # PHPStan + php-cs-fixer + every per-context lint
castor test:unit-back                    # PHPUnit
castor test:coupling-back                # Architecture coupling
castor test:acceptance-back              # Behat acceptance

# Single test
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter TestClassName
castor test:end-to-end-legacy path/to/feature.feature:23   # positional argument

# Bounded context pattern: <namespace>:<task>, e.g. unit|acceptance|integration|lint|coupling-back
castor connectivity-connection:unit-back
castor category:lint-back

# Frontend
yarn lint && yarn lint-fix
yarn unit
npx playwright test                      # E2E (config: playwright.config.ts)

# Code style auto-fix
castor back:fix-cs-back
```

Castor tasks take named options or positional arguments (see `castor help <task>`); there is
no `O=…` convention, and nothing in this repository reads `PIM_CONTEXT`.

## CI

GitHub Actions (`.github/workflows/ci.yml`). Self-hosted runners (5x on Hetzner CCX33). Path-filtered: backend/frontend/ci changes skip irrelevant jobs. Behat uses `--rerun` for flaky retry.

## MCP Servers

Prefer MCP tools over CLI equivalents: **serena** (symbolic code editing), **grepai** (semantic search), **context7** (library docs), **git**, **composer**.
