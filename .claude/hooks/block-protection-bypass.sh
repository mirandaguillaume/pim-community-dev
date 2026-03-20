#!/usr/bin/env bash
# Hook: PreToolUse on Bash
# BLOCKS any attempt to disable GitHub branch protections or push directly to master/main

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0

# Block ANY command that references branch protection mutation keywords
# Covers: gh api, curl, wget, python requests, httpie, etc.
PROTECTION_KEYWORDS='(rulesets|enforce_admins|/protection)'
if echo "$COMMAND" | grep -qiE "$PROTECTION_KEYWORDS"; then
    # Allow read-only operations (GET, view, list)
    if echo "$COMMAND" | grep -qiE '(-X\s*GET\b|--method\s*GET\b)'; then
        : # GET is safe, allow through
    elif echo "$COMMAND" | grep -qiE 'gh\s+api\b' && ! echo "$COMMAND" | grep -qiE '(-X\s|-d\s|--field|--method|--input)'; then
        : # gh api without write flags defaults to GET, allow
    else
        echo "BLOCKED: Modifying branch protections or rulesets is forbidden." >&2
        exit 2
    fi
fi

# Block git push to master/main directly
if echo "$COMMAND" | grep -qiE 'git\s+push\b.*\b(master|main)\b'; then
    echo "BLOCKED: Direct push to master/main is forbidden. Create a branch and open a PR instead." >&2
    exit 2
fi

exit 0
