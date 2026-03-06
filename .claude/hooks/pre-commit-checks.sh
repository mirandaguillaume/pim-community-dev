#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git commit)
# Consolidated pre-commit checks — runs only the checks relevant to staged file types.
# Replaces: warn-large-diff, no-debug-statements, cs-fixer-staged, phpstan-staged,
#           phpspec-staged, eslint-staged, jest-staged

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+commit' || exit 0

WARNINGS=""

# ── 1. Large diff check (fast, always runs) ──
STAGED_COUNT=$(git diff --cached --name-only 2>/dev/null | wc -l)
if [ "$STAGED_COUNT" -gt 20 ]; then
    WARNINGS="$WARNINGS\n- LARGE COMMIT: $STAGED_COUNT files staged. Consider smaller commits."
fi

# ── 2. Debug statements (fast, always runs) ──
DEBUG_FOUND=$(git diff --cached -U0 2>/dev/null \
    | grep -E '^\+' \
    | grep -v '^\+\+\+' \
    | grep -E '(dump\(|dd\(|var_dump\(|console\.log\(|debugger;)' \
    || true)
if [ -n "$DEBUG_FOUND" ]; then
    DEBUG_COUNT=$(echo "$DEBUG_FOUND" | wc -l)
    WARNINGS="$WARNINGS\n- DEBUG STATEMENTS: $DEBUG_COUNT occurrence(s) of dump()/dd()/var_dump()/console.log()/debugger"
fi

# ── Detect staged file types ──
STAGED_PHP=$(git diff --cached --name-only --diff-filter=ACMR -- '*.php' 2>/dev/null || true)
STAGED_JS=$(git diff --cached --name-only --diff-filter=ACMR -- '*.js' '*.jsx' 2>/dev/null || true)
STAGED_FRONT=$(git diff --cached --name-only --diff-filter=ACMR -- '*.ts' '*.tsx' '*.js' '*.jsx' 2>/dev/null \
    | grep -v '\.test\.' | grep -v '\.spec\.' | grep -v '__tests__/' | grep -v '__mocks__/' || true)

# ── 3. PHP checks (only if PHP files staged) ──
if [ -n "$STAGED_PHP" ]; then
    # CS-Fixer (exclude Spec.php and Integration.php)
    CS_FILES=$(echo "$STAGED_PHP" | grep -v 'Spec\.php$' | grep -v 'Integration\.php$' || true)
    if [ -n "$CS_FILES" ]; then
        CS_RESULT=$(docker-compose run --rm -T php php tools/php-cs-fixer fix \
            --dry-run --config=.php-cs-fixer.dist.php --path-mode=intersection \
            $CS_FILES 2>&1 || true)
        if echo "$CS_RESULT" | grep -qE 'that can be fixed'; then
            CS_COUNT=$(echo "$CS_RESULT" | grep -oE '[0-9]+\)' | wc -l)
            WARNINGS="$WARNINGS\n- CS-FIXER: $CS_COUNT file(s) need formatting. Run: make fix-cs-back"
        fi
    fi

    # PHPStan (exclude specs and tests)
    STAN_FILES=$(echo "$STAGED_PHP" | grep -v 'Spec\.php$' | grep -v '/tests/' | grep -v '/Test/' || true)
    if [ -n "$STAN_FILES" ]; then
        # Migrations with their own config
        STAN_MIGRATIONS=$(echo "$STAN_FILES" | grep '^upgrades/' || true)
        if [ -n "$STAN_MIGRATIONS" ] && [ -f "upgrades/phpstan.neon" ]; then
            STAN_RESULT=$(docker-compose run --rm -T php php -d memory_limit=1G \
                vendor/bin/phpstan analyse --configuration upgrades/phpstan.neon --no-progress 2>&1 || true)
            if echo "$STAN_RESULT" | grep -qE '\[ERROR\]|Found [0-9]+ error'; then
                STAN_SUMMARY=$(echo "$STAN_RESULT" | grep -E 'Found [0-9]+ error' | head -1 || echo "errors found")
                WARNINGS="$WARNINGS\n- PHPSTAN (migrations): $STAN_SUMMARY"
            fi
        fi
        # Source files at level 2
        STAN_SRC=$(echo "$STAN_FILES" | grep -v '^upgrades/' || true)
        if [ -n "$STAN_SRC" ]; then
            STAN_RESULT=$(docker-compose run --rm -T php php -d memory_limit=1G \
                vendor/bin/phpstan analyse --level 2 --no-progress $STAN_SRC 2>&1 || true)
            if echo "$STAN_RESULT" | grep -qE '\[ERROR\]|Found [0-9]+ error'; then
                STAN_SUMMARY=$(echo "$STAN_RESULT" | grep -E 'Found [0-9]+ error' | head -1 || echo "errors found")
                WARNINGS="$WARNINGS\n- PHPSTAN (src): $STAN_SUMMARY"
            fi
        fi
    fi

    # PHPSpec (find related specs for staged source files)
    SPEC_SRC=$(echo "$STAGED_PHP" | grep -v 'Spec\.php$' | grep -v 'Integration\.php$' \
        | grep -v 'EndToEnd\.php$' | grep -v '/tests/' | grep -v '/Test/' || true)
    if [ -n "$SPEC_SRC" ]; then
        SPECS=""
        for src in $SPEC_SRC; do
            BASENAME=$(basename "$src" .php)
            SPEC=$(find . -path "*/spec/*" -name "${BASENAME}Spec.php" 2>/dev/null | head -1 || true)
            [ -n "$SPEC" ] && SPECS="$SPECS $SPEC"
        done
        if [ -n "$SPECS" ]; then
            SPEC_RESULT=$(docker-compose run --rm -T php php vendor/bin/phpspec run $SPECS --no-interaction 2>&1 || true)
            if echo "$SPEC_RESULT" | grep -qE 'failed|broken'; then
                SPEC_FAILURES=$(echo "$SPEC_RESULT" | grep -cE 'failed|broken' || echo "?")
                WARNINGS="$WARNINGS\n- PHPSPEC: $SPEC_FAILURES spec(s) failed"
            fi
        fi
    fi
fi

# ── 4. JS checks (only if JS/JSX files staged) ──
if [ -n "$STAGED_JS" ] && command -v npx >/dev/null 2>&1; then
    ESLINT_RESULT=$(npx eslint --no-error-on-unmatched-pattern $STAGED_JS 2>&1 || true)
    if echo "$ESLINT_RESULT" | grep -qE '[0-9]+ error'; then
        ESLINT_SUMMARY=$(echo "$ESLINT_RESULT" | grep -oE '[0-9]+ error' | tail -1)
        WARNINGS="$WARNINGS\n- ESLINT: $ESLINT_SUMMARY in staged JS files. Run: yarn lint-fix"
    fi
fi

# ── 5. Jest (only if front-end source files staged) ──
if [ -n "$STAGED_FRONT" ] && command -v npx >/dev/null 2>&1; then
    JEST_RESULT=$(npx jest --findRelatedTests --passWithNoTests $STAGED_FRONT 2>&1 || true)
    if echo "$JEST_RESULT" | grep -qE 'Tests:.*failed'; then
        JEST_SUMMARY=$(echo "$JEST_RESULT" | grep -E 'Tests:' | head -1)
        WARNINGS="$WARNINGS\n- JEST: $JEST_SUMMARY"
    fi
fi

# ── Output ──
if [ -n "$WARNINGS" ]; then
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"PRE-COMMIT CHECKS:$WARNINGS\"}}"
fi

exit 0
