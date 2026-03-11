#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git push)
# Runs the same front-end checks as CI before pushing.
# Detects which workspaces have changed files and runs only relevant checks.
# Falls back to castor for PHP checks when available.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+push' || exit 0

BASE="origin/master"
WARNINGS=""
BLOCKED=""

# ── Detect changed file types since base branch ──
CHANGED_PHP=$(git diff --name-only "$BASE"...HEAD -- '*.php' 2>/dev/null || true)
CHANGED_JS=$(git diff --name-only "$BASE"...HEAD -- '*.js' '*.jsx' '*.ts' '*.tsx' 2>/dev/null || true)

# ── 1. Front-end checks (direct yarn, no castor dependency) ──
if [ -n "$CHANGED_JS" ]; then

    # ── 1a. Root ESLint + Prettier (mirrors CI front-lint: yarn lint) ──
    LINT_RESULT=$(yarn lint 2>&1 || true)
    if echo "$LINT_RESULT" | grep -qE '✖ [0-9]+ problem|error.*Command failed'; then
        LINT_SUMMARY=$(echo "$LINT_RESULT" | grep -oE '✖ [0-9]+ problem[^)]*' | tail -1 || echo "lint errors")
        BLOCKED="$BLOCKED\n- FRONT-LINT: $LINT_SUMMARY"
    fi

    # ── 1b. Connectivity Connection Jest (mirrors CI front-unit shard 1) ──
    CONN_CHANGED=$(echo "$CHANGED_JS" | grep -i 'Connectivity/Connection' || true)
    if [ -n "$CONN_CHANGED" ]; then
        CONN_RESULT=$(yarn workspace @akeneo-pim-community/connectivity-connection jest --ci 2>&1 || true)
        if echo "$CONN_RESULT" | grep -qE 'Test Suites:.*failed'; then
            CONN_SUMMARY=$(echo "$CONN_RESULT" | grep 'Test Suites:' | tail -1)
            BLOCKED="$BLOCKED\n- CONNECTIVITY JEST: $CONN_SUMMARY"
        fi
    fi

    # ── 1c. MeasureBundle Jest (mirrors CI front-unit shard 2) ──
    MEASURE_CHANGED=$(echo "$CHANGED_JS" | grep -i 'MeasureBundle' || true)
    if [ -n "$MEASURE_CHANGED" ]; then
        MEASURE_RESULT=$(yarn workspace @akeneo-pim-community/measurement test:unit:run --no-coverage 2>&1 || true)
        if echo "$MEASURE_RESULT" | grep -qE 'Test Suites:.*failed'; then
            MEASURE_SUMMARY=$(echo "$MEASURE_RESULT" | grep 'Test Suites:' | tail -1)
            BLOCKED="$BLOCKED\n- MEASURE JEST: $MEASURE_SUMMARY"
        fi
    fi

    # ── 1d. Root Jest (mirrors CI front-unit: yarn unit) ──
    ROOT_CHANGED=$(echo "$CHANGED_JS" | grep -vE 'Connectivity/Connection|MeasureBundle|e2e/' || true)
    if [ -n "$ROOT_CHANGED" ]; then
        ROOT_RESULT=$(yarn jest --config tests/front/unit/jest/unit.jest.js --passWithNoTests --ci 2>&1 || true)
        if echo "$ROOT_RESULT" | grep -qE 'Test Suites:.*failed'; then
            ROOT_SUMMARY=$(echo "$ROOT_RESULT" | grep 'Test Suites:' | tail -1)
            BLOCKED="$BLOCKED\n- ROOT JEST: $ROOT_SUMMARY"
        fi
    fi
fi

# ── 2. PHP checks (castor if available, else skip with warning) ──
if [ -n "$CHANGED_PHP" ]; then
    CASTOR=""
    if command -v castor >/dev/null 2>&1; then
        CASTOR="castor"
    elif [ -x "$HOME/.local/bin/castor" ]; then
        CASTOR="$HOME/.local/bin/castor"
    else
        # Auto-install castor
        mkdir -p "$HOME/.local/bin"
        if curl -fsSL "https://github.com/jolicode/castor/releases/latest/download/castor.linux-amd64.phar" \
             -o "$HOME/.local/bin/castor" 2>/dev/null; then
            chmod +x "$HOME/.local/bin/castor"
            CASTOR="$HOME/.local/bin/castor"
        fi
    fi

    if [ -n "$CASTOR" ]; then
        # Coupling
        RESULT=$($CASTOR test:coupling-back 2>&1 || true)
        if echo "$RESULT" | grep -qiE 'violation|error|failed|exception'; then
            BLOCKED="$BLOCKED\n- COUPLING: castor test:coupling-back found issues"
        fi
        # Lint-back
        RESULT=$($CASTOR test:lint-back 2>&1 || true)
        if echo "$RESULT" | grep -qiE '\[ERROR\]|Found [0-9]+ error|that can be fixed|failed'; then
            BLOCKED="$BLOCKED\n- LINT-BACK: castor test:lint-back found issues"
        fi
        # Unit-back
        RESULT=$($CASTOR test:unit-back 2>&1 || true)
        if echo "$RESULT" | grep -qiE 'failed|broken'; then
            BLOCKED="$BLOCKED\n- UNIT-BACK: castor test:unit-back found failures"
        fi
    else
        WARNINGS="$WARNINGS\n- PHP checks skipped (castor install failed)"
    fi
fi

# ── Output ──
if [ -n "$BLOCKED" ]; then
    echo "BLOCKED"
    echo "Pre-push checks failed:$BLOCKED"
    exit 1
fi

if [ -n "$WARNINGS" ]; then
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"PRE-PUSH:$WARNINGS\"}}"
fi

exit 0
