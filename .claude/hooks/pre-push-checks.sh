#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git push)
# Consolidated pre-push checks — runs only the checks relevant to changed file types.
# Playwright section BLOCKS the push if any test fails (starts PIM if needed).

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+push' || exit 0

WARNINGS=""
BLOCK=""

# Compare against remote tracking branch if it exists (unpushed commits only),
# otherwise fall back to origin/master (entire branch diff).
REMOTE_BRANCH=$(git rev-parse --abbrev-ref --symbolic-full-name "@{upstream}" 2>/dev/null || true)
if [ -n "$REMOTE_BRANCH" ]; then
    BASE="$REMOTE_BRANCH"
else
    BASE="origin/master"
fi

# ── Detect changed file types ──
CHANGED_PHP=$(git diff --name-only "$BASE"...HEAD -- '*.php' 2>/dev/null || true)
CHANGED_JS=$(git diff --name-only "$BASE"...HEAD -- '*.js' '*.jsx' 2>/dev/null || true)
CHANGED_SPECS=$(git diff --name-only "$BASE"...HEAD -- '*.spec.ts' 2>/dev/null || true)
CHANGED_FIXTURES=$(git diff --name-only "$BASE"...HEAD -- 'tests/front/e2e/fixtures/*.ts' 2>/dev/null || true)

# ── 1. Coupling check (only if PHP files changed) ──
if [ -n "$CHANGED_PHP" ]; then
    for ctx in "Pim/Structure" "Pim/Enrichment" "Channel" "Connectivity/Connection" "Platform/Job" "Platform/Installer"; do
        if echo "$CHANGED_PHP" | grep -q "src/Akeneo/$ctx/"; then
            CD_CONFIG=$(find "src/Akeneo/$ctx" -name ".php_cd.php" -maxdepth 3 2>/dev/null | head -1 || true)
            if [ -n "$CD_CONFIG" ]; then
                RESULT=$(docker-compose run --rm -T php php vendor/bin/php-coupling-detector detect --config-file="$CD_CONFIG" 2>&1 || true)
                if echo "$RESULT" | grep -qiE 'violation|error'; then
                    CTX_NAME=$(dirname "$CD_CONFIG" | sed 's|src/Akeneo/||')
                    WARNINGS="$WARNINGS\n- COUPLING ($CTX_NAME): violations detected"
                fi
            fi
        fi
    done
fi

# ── 2. ESLint (only if JS/JSX files changed) ──
if [ -n "$CHANGED_JS" ] && command -v npx >/dev/null 2>&1; then
    FILE_COUNT=$(echo "$CHANGED_JS" | wc -l)
    RESULT=$(npx eslint $CHANGED_JS --no-error-on-unmatched-pattern 2>&1 || true)
    if echo "$RESULT" | grep -qE '[0-9]+ error'; then
        ERROR_SUMMARY=$(echo "$RESULT" | grep -oE '[0-9]+ error' | tail -1)
        WARNINGS="$WARNINGS\n- ESLINT: $ERROR_SUMMARY in $FILE_COUNT front-end files. Run: yarn lint-fix"
    fi
fi

# ── 3. Playwright (BLOCKING — only runs specs directly modified in the branch) ──
# When only fixtures changed (no spec files), warn but don't block — the full
# suite runs in CI. Running all specs locally for a fixture change is too slow
# and unrelated failures would block the push.
if [ -z "$CHANGED_SPECS" ] && [ -n "$CHANGED_FIXTURES" ]; then
    WARNINGS="$WARNINGS\n- PLAYWRIGHT: fixture files changed but no specs modified — full suite will run in CI"
fi
if [ -n "$CHANGED_SPECS" ] && command -v npx >/dev/null 2>&1; then
    # Ensure PIM is running — start it if not
    PIM_WAS_DOWN=""
    if ! curl -sf -o /dev/null --max-time 5 http://localhost:8080/user/login 2>/dev/null; then
        PIM_WAS_DOWN="1"
        docker compose up -d 2>/dev/null
        # Wait for PIM to be ready (login page responds)
        TRIES=0
        MAX_TRIES=60
        while [ $TRIES -lt $MAX_TRIES ]; do
            if curl -sf -o /dev/null --max-time 3 http://localhost:8080/user/login 2>/dev/null; then
                break
            fi
            sleep 5
            TRIES=$((TRIES + 1))
        done
        if [ $TRIES -eq $MAX_TRIES ]; then
            WARNINGS="$WARNINGS\n- PLAYWRIGHT: PIM failed to start after ${MAX_TRIES}x5s — skipped"
            # Don't block if PIM can't start — just warn
            CHANGED_SPECS=""
        fi
    fi

    if [ -n "$CHANGED_SPECS" ]; then
        SPEC_COUNT=$(echo "$CHANGED_SPECS" | wc -l)
        RESULT=$(npx playwright test --reporter=line $CHANGED_SPECS 2>&1 || true)
        PASSED=$(echo "$RESULT" | grep -oP '\d+ passed' | head -1 || echo "0 passed")
        FAILED=$(echo "$RESULT" | grep -oP '\d+ failed' | head -1 || true)
        SKIPPED=$(echo "$RESULT" | grep -oP '\d+ skipped' | head -1 || true)
        if [ -n "$FAILED" ]; then
            BLOCK="PLAYWRIGHT: $FAILED ($PASSED, ${SKIPPED:-0 skipped}) — $SPEC_COUNT spec(s) tested. Fix the failing tests before pushing."
        fi
    fi
fi

# ── Output ──
if [ -n "$BLOCK" ]; then
    echo "{\"decision\":\"block\",\"reason\":\"$BLOCK\"}"
    exit 0
fi

if [ -n "$WARNINGS" ]; then
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"PRE-PUSH CHECKS:$WARNINGS\"}}"
fi

exit 0
