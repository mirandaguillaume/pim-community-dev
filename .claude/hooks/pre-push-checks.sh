#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git push)
# Lightweight pre-push — lint ONLY changed files.
# Heavy checks (phpunit, coupling, tsc, jest) run in CI.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+push' || exit 0

BASE="origin/master"
ERRORS=""

# ── Detect changed files ──
CHANGED_PHP=$(git diff --name-only "$BASE"...HEAD -- '*.php' 2>/dev/null | grep -v 'Spec\.php$' | grep -v 'Integration\.php$' || true)
CHANGED_JS=$(git diff --name-only "$BASE"...HEAD -- '*.js' '*.jsx' '*.ts' '*.tsx' 2>/dev/null || true)

# ── PHP: cs-fixer dry-run on changed files only (Docker, ~2-3s) ──
if [ -n "$CHANGED_PHP" ]; then
    CS_FILES=$(echo "$CHANGED_PHP" | grep -v 'Spec\.php$' | grep -v 'Integration\.php$' || true)
    if [ -n "$CS_FILES" ] && command -v docker-compose >/dev/null 2>&1; then
        CS_RESULT=$(docker-compose run --rm -T php php vendor/bin/php-cs-fixer fix \
            --dry-run --config=.php_cs.php --path-mode=intersection \
            $CS_FILES 2>&1 || true)
        if echo "$CS_RESULT" | grep -qE 'that can be fixed'; then
            CS_COUNT=$(echo "$CS_RESULT" | grep -oE '[0-9]+\)' | wc -l)
            ERRORS="$ERRORS\n- CS-FIXER: $CS_COUNT file(s) need formatting. Run: make fix-cs-back"
        fi
    fi
fi

# ── PHP: PHPStan on changed files only (Docker, ~3-5s) ──
if [ -n "$CHANGED_PHP" ]; then
    STAN_FILES=$(echo "$CHANGED_PHP" | grep -v '/tests/' | grep -v '/Test/' | grep -v '/spec/' || true)
    if [ -n "$STAN_FILES" ] && command -v docker-compose >/dev/null 2>&1; then
        STAN_RESULT=$(docker-compose run --rm -T php php -d memory_limit=512M \
            vendor/bin/phpstan analyse --level 2 --no-progress --error-format=raw \
            $STAN_FILES 2>&1 || true)
        if echo "$STAN_RESULT" | grep -qE ':\d+:'; then
            STAN_COUNT=$(echo "$STAN_RESULT" | grep -cE ':\d+:' || echo "?")
            ERRORS="$ERRORS\n- PHPSTAN: $STAN_COUNT error(s) in changed files"
        fi
    fi
fi

# ── PHP: PHPUnit unit tests for changed files (no MySQL, APP_ENV=test_fake) ──
if [ -n "$CHANGED_PHP" ]; then
    UNIT_SRC=$(echo "$CHANGED_PHP" | grep -v 'Spec\.php$' | grep -v 'Test\.php$' \
        | grep -v '/tests/' | grep -v '/Test/' | grep -v '/spec/' || true)
    if [ -n "$UNIT_SRC" ] && command -v docker-compose >/dev/null 2>&1; then
        FILTERS=""
        for src in $UNIT_SRC; do
            BASENAME=$(basename "$src" .php)
            # Search for matching unit test (not integration/end-to-end)
            TEST=$(find . \( -path "*/tests/*" -o -path "*/Test/*" \) \
                -name "${BASENAME}Test.php" ! -name "*Integration*" ! -name "*EndToEnd*" \
                -print -quit 2>/dev/null || true)
            [ -n "$TEST" ] && FILTERS="$FILTERS|$BASENAME"
        done
        if [ -n "$FILTERS" ]; then
            FILTERS="${FILTERS#|}"  # remove leading pipe
            PHPUNIT_RESULT=$(APP_ENV=test_fake docker-compose run --rm -T php php vendor/bin/phpunit \
                -c . --testsuite PHPUnit_Unit_Test --filter "$FILTERS" --no-coverage 2>&1 || true)
            if echo "$PHPUNIT_RESULT" | grep -qE 'FAILURES|ERRORS'; then
                PHPUNIT_SUMMARY=$(echo "$PHPUNIT_RESULT" | grep -E '^(FAILURES|Tests:|OK)' | tail -1 || echo "failures")
                ERRORS="$ERRORS\n- PHPUNIT-UNIT: $PHPUNIT_SUMMARY"
            fi
        fi
    fi
fi

# ── JS/TS: ESLint on changed files only ──
# Root .eslintrc uses @babel/eslint-parser — only handles .js/.jsx; skip .ts/.tsx
if [ -n "$CHANGED_JS" ] && command -v npx >/dev/null 2>&1; then
    # Filter to existing .js/.jsx files only (root ESLint config doesn't support TS)
    EXISTING_JS=""
    for f in $CHANGED_JS; do
        [[ "$f" =~ \.(js|jsx)$ ]] && [ -f "$f" ] && EXISTING_JS="$EXISTING_JS $f"
    done
    if [ -n "$EXISTING_JS" ]; then
        ESLINT_RESULT=$(npx eslint --no-error-on-unmatched-pattern --quiet $EXISTING_JS 2>&1 || true)
        if echo "$ESLINT_RESULT" | grep -qE '[0-9]+ error'; then
            ESLINT_SUMMARY=$(echo "$ESLINT_RESULT" | grep -oE '[0-9]+ error' | tail -1)
            ERRORS="$ERRORS\n- ESLINT: $ESLINT_SUMMARY. Run: yarn lint-fix"
        fi
    fi
fi

# ── JS/TS: Prettier on changed files only ──
if [ -n "$CHANGED_JS" ] && command -v npx >/dev/null 2>&1; then
    EXISTING_JS=""
    for f in $CHANGED_JS; do
        [ -f "$f" ] && EXISTING_JS="$EXISTING_JS $f"
    done
    if [ -n "$EXISTING_JS" ]; then
        PRETTIER_RESULT=$(npx prettier --check $EXISTING_JS 2>&1 || true)
        if echo "$PRETTIER_RESULT" | grep -qE 'Code style issues found'; then
            ERRORS="$ERRORS\n- PRETTIER: formatting issues. Run: npx prettier --write <files>"
        fi
    fi
fi

# ── JS/TS: RSPack build (catches module resolution and ESM linking errors) ──
CHANGED_DEPS=$(git diff --name-only "$BASE"...HEAD -- 'package.json' 'yarn.lock' 2>/dev/null || true)
if [ -n "$CHANGED_JS" ] || [ -n "$CHANGED_DEPS" ]; then
    echo "Running RSPack build check (yarn webpack-dev)..." >&2
    RSPACK_RESULT=$(yarn webpack-dev 2>&1 || true)
    if ! echo "$RSPACK_RESULT" | grep -q "compiled successfully"; then
        RSPACK_ERRORS=$(echo "$RSPACK_RESULT" | grep -E "^.*(ERROR|error TS)" | head -5 | sed 's/^/    /')
        ERRORS="$ERRORS\n- RSPACK BUILD FAILED. Run: yarn webpack-dev\n$RSPACK_ERRORS"
    fi
fi

# ── JS/TS: Workspace TypeScript lint for impacted workspaces ──
CHANGED_CATEGORY=$(echo "$CHANGED_JS" | grep 'src/Akeneo/Category/front/' || true)
if [ -n "$CHANGED_CATEGORY" ]; then
    echo "Running Category TypeScript check (yarn category:lint:check)..." >&2
    CATEGORY_RESULT=$(yarn category:lint:check 2>&1 || true)
    if echo "$CATEGORY_RESULT" | grep -qE 'error TS|[0-9]+ error(s)?'; then
        TS_SUMMARY=$(echo "$CATEGORY_RESULT" | grep -oE '[0-9]+ error' | tail -1 || echo "errors")
        ERRORS="$ERRORS\n- CATEGORY TS: $TS_SUMMARY. Run: yarn category:lint:check"
    fi
fi

CHANGED_CONNECTIVITY=$(echo "$CHANGED_JS" | grep 'src/Akeneo/Connectivity/Connection/front/src/' || true)
if [ -n "$CHANGED_CONNECTIVITY" ]; then
    echo "Running Connectivity TypeScript check (tsc)..." >&2
    CONN_RESULT=$(cd src/Akeneo/Connectivity/Connection/front && npx tsc --noEmit 2>&1 || true)
    if echo "$CONN_RESULT" | grep -qE 'error TS'; then
        CONN_SUMMARY=$(echo "$CONN_RESULT" | grep -c 'error TS' || echo "?")
        ERRORS="$ERRORS\n- CONNECTIVITY TS: $CONN_SUMMARY error(s). Run: tsc --noEmit in Connection/front"
    fi
fi

# ── Output ──
if [ -n "$ERRORS" ]; then
    echo "PRE-PUSH BLOCKED — fix before pushing:$ERRORS" >&2
    exit 2
fi

exit 0
