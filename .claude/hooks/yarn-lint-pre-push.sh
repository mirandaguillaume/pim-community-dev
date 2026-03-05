#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git push)
# Runs ESLint on changed front-end files before pushing.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+push' || exit 0

BASE="origin/master"

# Only lint .js/.jsx files with the root eslintrc (which uses @babel/eslint-parser).
# TypeScript files (.ts/.tsx) are linted by workspace-specific configs via `yarn lint`.
CHANGED_FRONT=$(git diff --name-only "$BASE"...HEAD -- '*.js' '*.jsx' 2>/dev/null || true)
[ -z "$CHANGED_FRONT" ] && exit 0

FILE_COUNT=$(echo "$CHANGED_FRONT" | wc -l)

# Check if npx/eslint is available
command -v npx >/dev/null 2>&1 || exit 0

RESULT=$(npx eslint $CHANGED_FRONT --no-error-on-unmatched-pattern 2>&1 || true)

if echo "$RESULT" | grep -qE '[0-9]+ error'; then
    ERROR_SUMMARY=$(echo "$RESULT" | grep -oE '[0-9]+ error' | tail -1)
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"ESLINT ERRORS in $FILE_COUNT front-end files: $ERROR_SUMMARY\nRun: yarn lint-fix to auto-fix.\"}}"
fi

exit 0
