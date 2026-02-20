#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git commit)
# Warns when committing with a large number of staged files.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+commit' || exit 0

STAGED_COUNT=$(git diff --cached --name-only 2>/dev/null | wc -l)
if [ "$STAGED_COUNT" -gt 20 ]; then
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"WARNING: $STAGED_COUNT files staged. Consider smaller commits.\"}}"
fi

exit 0
