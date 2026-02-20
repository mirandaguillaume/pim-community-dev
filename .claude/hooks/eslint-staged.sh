#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git commit)
# Runs ESLint on staged JS/TS files before committing.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+commit' || exit 0

STAGED_FRONT=$(git diff --cached --name-only --diff-filter=ACMR -- '*.js' '*.jsx' '*.ts' '*.tsx' 2>/dev/null || true)
[ -z "$STAGED_FRONT" ] && exit 0

command -v npx >/dev/null 2>&1 || exit 0

FILE_COUNT=$(echo "$STAGED_FRONT" | wc -l)

RESULT=$(npx eslint --no-error-on-unmatched-pattern $STAGED_FRONT 2>&1 || true)

if echo "$RESULT" | grep -qE '[0-9]+ error'; then
    ERROR_SUMMARY=$(echo "$RESULT" | grep -oE '[0-9]+ error' | tail -1)
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"ESLINT ERRORS in $FILE_COUNT staged file(s): $ERROR_SUMMARY\nRun: yarn lint-fix to auto-fix.\"}}"
fi

exit 0
