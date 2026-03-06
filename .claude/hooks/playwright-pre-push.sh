#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git push)
# Runs Playwright tests when .spec.ts or fixture files have changed vs origin/master.
# Only runs the changed specs (not the entire suite) to stay fast.
# Advisory — warns but does not block.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+push' || exit 0

BASE="origin/master"

CHANGED_SPECS=$(git diff --name-only "$BASE"...HEAD -- '*.spec.ts' 2>/dev/null || true)
CHANGED_FIXTURES=$(git diff --name-only "$BASE"...HEAD -- 'tests/front/e2e/fixtures/*.ts' 2>/dev/null || true)

# If fixtures changed but no specs, run all specs that import from fixtures
if [ -z "$CHANGED_SPECS" ] && [ -n "$CHANGED_FIXTURES" ]; then
    CHANGED_SPECS=$(find tests/front/e2e -name '*.spec.ts' 2>/dev/null || true)
fi

[ -z "$CHANGED_SPECS" ] && exit 0

SPEC_COUNT=$(echo "$CHANGED_SPECS" | wc -l)

command -v npx >/dev/null 2>&1 || exit 0

# Only run the changed specs (not the entire suite)
RESULT=$(npx playwright test --reporter=line $CHANGED_SPECS 2>&1 || true)

PASSED=$(echo "$RESULT" | grep -oP '\d+ passed' | head -1 || echo "0 passed")
FAILED=$(echo "$RESULT" | grep -oP '\d+ failed' | head -1 || true)
SKIPPED=$(echo "$RESULT" | grep -oP '\d+ skipped' | head -1 || true)

if [ -n "$FAILED" ]; then
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"PLAYWRIGHT FAILURES: $FAILED ($PASSED, $SKIPPED) — $SPEC_COUNT spec(s) tested.\nRun: npx playwright test to see details.\"}}"
fi

exit 0
