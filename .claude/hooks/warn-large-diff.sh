#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git commit/push)
# Warns when committing or pushing with a large number of changed files.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty')

if [ -z "$COMMAND" ]; then
    exit 0
fi

# Only check git commit and push commands
if ! echo "$COMMAND" | grep -qE 'git\s+(commit|push)'; then
    exit 0
fi

# For commits: check staged file count
if echo "$COMMAND" | grep -qE 'git\s+commit'; then
    STAGED_COUNT=$(git diff --cached --name-only 2>/dev/null | wc -l)
    if [ "$STAGED_COUNT" -gt 20 ]; then
        echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"WARNING: $STAGED_COUNT files are staged for commit. Consider breaking this into smaller, focused commits.\"}}"
    fi
fi

exit 0
