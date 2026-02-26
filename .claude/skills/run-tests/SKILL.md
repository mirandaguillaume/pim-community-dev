---
name: run-tests
description: Smart test runner that detects the right test framework and runs tests. Use after writing code or when asked to run tests.
---

Run tests for `$ARGUMENTS` (file path, class name, or bounded context).

## Detection Rules

Detect the test type from the file path or name and run the appropriate command:

| Pattern | Framework | Command |
|---------|-----------|---------|
| `*Spec.php` | PHPSpec | `docker-compose run --rm php php vendor/bin/phpspec run <file>` |
| `*Integration.php` or `*Test.php` (in `tests/Integration/`) | PHPUnit Integration | `APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter <class>` |
| `*Test.php` (in `tests/Acceptance/`) | PHPUnit Acceptance | `APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter <class>` |
| `*.feature` | Behat | `make end-to-end-legacy O=<file>` |
| `*.test.ts` or `*.test.tsx` | Jest | `npx jest <file>` |
| `*.spec.ts` or `*.spec.tsx` | Jest | `npx jest <file>` |
| Tests in `tests/e2e/` | Playwright | `npx playwright test <file>` |

## If no file specified

1. Detect changed files: `git diff --name-only HEAD`
2. For each changed source file, find related test files
3. Run the detected tests grouped by framework

## If a source file (not a test) is specified

1. Find the matching test file(s):
   - PHP class -> look for `*Spec.php` with matching name
   - TS/JS file -> look for `*.test.ts(x)` with matching name
2. Run the found test(s)

## Bounded context shorthand

If argument is a context name (e.g., `Channel`, `Connectivity/Connection`):
- Run: `make {context-slug}-unit-back` for PHPSpec
- Run: `make {context-slug}-acceptance-back` for acceptance
