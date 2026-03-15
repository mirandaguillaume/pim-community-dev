#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git push)
# Mirrors the fast/cheap CI jobs locally before pushing. Blocks on failure (exit 2).
#
# CI jobs reproduced here:
#   front-lint    → yarn lint (Prettier + ESLint)
#   front-unit    → yarn unit + DSM jest + connectivity jest
#   phpspec       → docker-compose run --rm php php vendor/bin/phpspec run
#   phpunit-unit  → Category_Unit_Test + lightweight acceptance (no MySQL)
#   code-style    → coupling (only for changed bounded contexts)
#   playwright    → only if spec/fixture files changed
#
# Skipped (too slow/expensive or needs MySQL):
#   lint-back (needs MySQL), phpunit (6 shards, 90min, needs MySQL),
#   acceptance-back (behat), behat-legacy, deptrac, db-seed

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+push' || exit 0

BASE="origin/master"
ERRORS=""

# ── Detect changed file types (mirrors detect-changes job) ──
CHANGED_PHP=$(git diff --name-only "$BASE"...HEAD -- '*.php' 2>/dev/null || true)
CHANGED_FRONT=$(git diff --name-only "$BASE"...HEAD -- \
    'front-packages/**' 'frontend/**' 'yarn.lock' 'package.json' \
    'webpack*.js' 'babel.config.*' 'jest.config.*' '.eslintrc*' 'tsconfig*.json' \
    'src/**/*.ts' 'src/**/*.tsx' 'src/**/*.js' 'src/**/*.jsx' \
    'components/**/*.ts' 'components/**/*.tsx' \
    2>/dev/null || true)
CHANGED_SPECS=$(git diff --name-only "$BASE"...HEAD -- '*.spec.ts' 2>/dev/null || true)
CHANGED_FIXTURES=$(git diff --name-only "$BASE"...HEAD -- 'tests/front/e2e/fixtures/*.ts' 2>/dev/null || true)

# ══════════════════════════════════════════════════════════════════════════════
# FRONTEND CHECKS (mirrors: front-lint + front-unit)
# ══════════════════════════════════════════════════════════════════════════════
if [ -n "$CHANGED_FRONT" ] && command -v yarn >/dev/null 2>&1; then

    # ── front-lint: Prettier + ESLint (mirrors `castor test:lint-front`) ──
    LINT_RESULT=$(yarn lint 2>&1 || true)
    if echo "$LINT_RESULT" | grep -qE '[0-9]+ error|Code style issues found'; then
        ERRORS="$ERRORS\n- FRONT-LINT: Prettier or ESLint errors. Run: yarn lint-fix"
    fi

    # ── front-unit shard 1+2: main Jest suite (mirrors `yarn unit`) ──
    UNIT_RESULT=$(yarn unit 2>&1 || true)
    if echo "$UNIT_RESULT" | grep -qE 'FAIL |Tests:.*failed'; then
        FAIL_SUMMARY=$(echo "$UNIT_RESULT" | grep -E 'Tests:' | tail -1 || echo "failures found")
        ERRORS="$ERRORS\n- FRONT-UNIT: $FAIL_SUMMARY"
    fi

    # ── front-unit shard 1: DSM unit tests (separate jest config) ──
    DSM_RESULT=$(cd front-packages/akeneo-design-system && npx jest --config jest.unit.config.js --no-coverage 2>&1 || true)
    if echo "$DSM_RESULT" | grep -qE 'FAIL |Tests:.*failed'; then
        FAIL_SUMMARY=$(echo "$DSM_RESULT" | grep -E 'Tests:' | tail -1 || echo "failures found")
        ERRORS="$ERRORS\n- DSM-UNIT: $FAIL_SUMMARY"
    fi
fi

# ══════════════════════════════════════════════════════════════════════════════
# BACKEND CHECKS (mirrors: phpspec + code-style-back)
# ══════════════════════════════════════════════════════════════════════════════
if [ -n "$CHANGED_PHP" ]; then

    # ── phpspec: full PHPSpec suite (mirrors `castor test:unit-back`, no MySQL) ──
    if command -v docker-compose >/dev/null 2>&1; then
        PHPSPEC_EXIT=0
        PHPSPEC_RESULT=$(docker-compose run --rm -T php php vendor/bin/phpspec run 2>&1) || PHPSPEC_EXIT=$?
        if [ "$PHPSPEC_EXIT" -ne 0 ]; then
            FAIL_SUMMARY=$(echo "$PHPSPEC_RESULT" | grep -E '^[0-9]+ examples' | tail -1 || echo "exit code $PHPSPEC_EXIT")
            ERRORS="$ERRORS\n- PHPSPEC: $FAIL_SUMMARY"
        fi
    fi

    # ── phpunit-unit: unit + acceptance tests via meta-suite (no MySQL) ──
    if command -v docker-compose >/dev/null 2>&1; then
        PHPUNIT_EXIT=0
        PHPUNIT_UNIT_RESULT=$(APP_ENV=test_fake docker-compose run --rm -T php php vendor/bin/phpunit -c . --testsuite PHPUnit_Unit_Test --no-coverage 2>&1) || PHPUNIT_EXIT=$?
        if [ "$PHPUNIT_EXIT" -ne 0 ]; then
            FAIL_SUMMARY=$(echo "$PHPUNIT_UNIT_RESULT" | grep -E '^(FAILURES|Tests:|OK)' | tail -1 || echo "exit code $PHPUNIT_EXIT")
            ERRORS="$ERRORS\n- PHPUNIT-UNIT: $FAIL_SUMMARY"
        fi
    fi

    # ── coupling: architecture coupling checks per bounded context ──
    for ctx in "Pim/Structure" "Pim/Enrichment" "Channel" "Connectivity/Connection" "Platform/Job" "Platform/Installer"; do
        if echo "$CHANGED_PHP" | grep -q "src/Akeneo/$ctx/"; then
            CD_CONFIG=$(find "src/Akeneo/$ctx" -name ".php_cd.php" -maxdepth 3 2>/dev/null | head -1 || true)
            if [ -n "$CD_CONFIG" ]; then
                RESULT=$(docker-compose run --rm -T php php vendor/bin/php-coupling-detector detect --config-file="$CD_CONFIG" 2>&1 || true)
                if echo "$RESULT" | grep -qiE 'violation|error'; then
                    CTX_NAME=$(dirname "$CD_CONFIG" | sed 's|src/Akeneo/||')
                    ERRORS="$ERRORS\n- COUPLING ($CTX_NAME): violations detected"
                fi
            fi
        fi
    done
fi

# ══════════════════════════════════════════════════════════════════════════════
# PLAYWRIGHT (mirrors: playwright job — only if spec/fixture files changed)
# ══════════════════════════════════════════════════════════════════════════════
if [ -z "$CHANGED_SPECS" ] && [ -n "$CHANGED_FIXTURES" ]; then
    CHANGED_SPECS=$(find tests/front/e2e -name '*.spec.ts' 2>/dev/null || true)
fi
if [ -n "$CHANGED_SPECS" ] && command -v npx >/dev/null 2>&1; then
    SPEC_COUNT=$(echo "$CHANGED_SPECS" | wc -l)
    RESULT=$(npx playwright test --reporter=line $CHANGED_SPECS 2>&1 || true)
    FAILED=$(echo "$RESULT" | grep -oP '\d+ failed' | head -1 || true)
    if [ -n "$FAILED" ]; then
        PASSED=$(echo "$RESULT" | grep -oP '\d+ passed' | head -1 || echo "0 passed")
        ERRORS="$ERRORS\n- PLAYWRIGHT: $FAILED ($PASSED) — $SPEC_COUNT spec(s)"
    fi
fi

# ── Output ──
if [ -n "$ERRORS" ]; then
    echo "PRE-PUSH BLOCKED — fix before pushing:$ERRORS" >&2
    exit 2
fi

exit 0
