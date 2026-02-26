#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git commit)
# Warns about debug statements in staged changes.
set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+commit' || exit 0

# Scan staged diff for debug statements
FOUND=$(git diff --cached -U0 2>/dev/null \
    | grep -E '^\+' \
    | grep -v '^\+\+\+' \
    | grep -E '(dump\(|dd\(|var_dump\(|console\.log\(|debugger;)' \
    || true)

if [ -n "$FOUND" ]; then
    COUNT=$(echo "$FOUND" | wc -l)
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"DEBUG STATEMENTS: $COUNT occurrence(s) of dump()/dd()/var_dump()/console.log()/debugger in staged changes. Remove before committing.\"}}"
fi
exit 0
