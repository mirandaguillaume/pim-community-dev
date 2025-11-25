# Repository Guidelines

## Project Structure & Module Organization
- `src/` holds PHP application code (Akeneo bundles, domain components).
- `tests/back/` contains PHPUnit and PhpSpec suites; `cypress/` covers end-to-end tests.
- `frontend/` and `front-packages/` store React/TypeScript assets built via Webpack.
- `docker/`, `docker-compose*.yml`, and `Makefile` govern local containers and automation.
- Shared tooling lives in the repository root (`composer.json`, `package.json`, `phpstan*.neon`).

## Build, Test, and Development Commands
- `composer install` / `yarn install` bootstrap PHP and JS dependencies.
- `make pim-dev` provisions the full dev stack (Docker services, cache warmup, demo catalog).
- `make pim-test` prepares the test database/indexes under PHP 8.2 (default stack).
- Static analysis: `make phpstan` (see `Makefile`) and `vendor/bin/php-cs-fixer fix --dry-run`.

## Coding Style & Naming Conventions
- PHP: PSR-12 formatting, 4-space indentation, typed properties, `declare(strict_types=1);` at file top.
- JS/TS: Prettier defaults with ESLint (configured via `package.json`).
- Service IDs follow Symfony naming (`akeneo.<domain>.<purpose>`); PHP classes use PascalCase; methods camelCase.
- Run `composer normalize` after editing `composer.json`; prefer constructor injection over service locator usage.

## Testing Guidelines
- PHPUnit lives in `tests/back/`; select suites with `vendor/bin/phpunit --testsuite <SuiteName>`.
- Behaviour specs in `tests/back/**/Specification` run via `vendor/bin/phpspec run`.
- Web UI tests: `yarn test:e2e` (wraps Cypress) once the Selenium stack is up (`make pim-behat`).
- For PHP 8.2 runs, use the default stack and add `--no-deps --use-aliases` when chaining `docker compose run` commands to avoid restarting services.

## Commit & Pull Request Guidelines
- Reference issues in messages (`PIM-1234: short imperative summary`). Keep body lines ≤72 chars.
- Squash fixup commits locally; leave meaningful history per feature branch.
- Pull requests should include: problem statement, solution outline, test evidence (`phpunit`/`phpspec`/`phpstan` excerpts), and screenshots for UI changes.
- Ensure CI scripts (Makefile targets, Docker overrides) remain executable; update `UPGRADE-*.md` when altering runtime requirements.

## Documentation & Reference
- When an uncertainty arises about technical APIs or library behaviour, query Context7 documentation first (via the `resolve-library-id` and `get-library-docs` workflow) before relying on external sources.

## Assistant Automation (MCP)
- When executing tasks through the assistant, prefer the dedicated MCP tools (Git, Composer, Context7 docs, Chrome DevTools, etc.) before falling back to raw shell commands.
- Maintain upgrade trackers (e.g., `UPGRADE-PHP82.md`) by updating them whenever new steps are discovered or completed.
- Create and push incremental commits at each significant step of work to keep progress traceable.

## Security & Configuration Tips
- Secrets live outside the repo; use environment overrides (`.env.local`, Docker env vars).
- Keep Docker images current; mirror any Compose tweaks across local/CI configurations.
- Verify new third-party bundles against Symfony/PHP compatibility (`composer why-not`).
- If a task would normally require `sudo`, pause and ask the user to run the privileged command themselves instead of invoking it directly.
