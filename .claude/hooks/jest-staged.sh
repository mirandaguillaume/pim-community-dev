#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git commit)
# Runs Jest on test files related to staged front-end sources before committing.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+commit' || exit 0

# Get staged front-end source files (not test files)
STAGED_FRONT=$(git diff --cached --name-only --diff-filter=ACMR -- '*.ts' '*.tsx' '*.js' '*.jsx' 2>/dev/null \
    | grep -v '\.test\.' \
    | grep -v '\.spec\.' \
    | grep -v '__tests__/' \
    | grep -v '__mocks__/' \
    || true)

[ -z "$STAGED_FRONT" ] && exit 0

command -v npx >/dev/null 2>&1 || exit 0

# Run Jest with --findRelatedTests for staged files only
RESULT=$(npx jest --findRelatedTests --passWithNoTests $STAGED_FRONT 2>&1 || true)

if echo "$RESULT" | grep -qE 'Tests:.*failed'; then
    FAIL_SUMMARY=$(echo "$RESULT" | grep -E 'Tests:' | head -1)
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"JEST FAILURES for staged files: $FAIL_SUMMARY\nFix before committing.\"}}"
fi

exit 0
