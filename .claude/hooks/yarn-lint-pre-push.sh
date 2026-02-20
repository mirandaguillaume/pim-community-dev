#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git push)
# Runs yarn lint on changed front-end files before pushing.
# Catches front-lint CI failures before the 40min CI wait.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty')

if [ -z "$COMMAND" ]; then
    exit 0
fi

# Only trigger on git push commands
if ! echo "$COMMAND" | grep -qE 'git\s+push'; then
    exit 0
fi

BASE="origin/master"

# Get changed front-end files
CHANGED_FRONT=$(git diff --name-only "$BASE"...HEAD -- '*.js' '*.jsx' '*.ts' '*.tsx' 2>/dev/null || true)

if [ -z "$CHANGED_FRONT" ]; then
    exit 0
fi

FILE_COUNT=$(echo "$CHANGED_FRONT" | wc -l)

# Run yarn lint on changed files
RESULT=$(npx eslint $CHANGED_FRONT --no-error-on-unmatched-pattern 2>&1 || true)

if echo "$RESULT" | grep -qE '[0-9]+ error'; then
    ERROR_SUMMARY=$(echo "$RESULT" | grep -oE '[0-9]+ error' | tail -1)
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"ESLINT ERRORS in $FILE_COUNT changed front-end files: $ERROR_SUMMARY\nRun: yarn lint-fix to auto-fix, or fix manually before pushing.\"}}"
fi

exit 0
