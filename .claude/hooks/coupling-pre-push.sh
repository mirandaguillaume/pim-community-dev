#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git push)
# Runs php-coupling-detector on changed bounded contexts before pushing.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+push' || exit 0

BASE="origin/master"

# Get changed PHP files
CHANGED_PHP=$(git diff --name-only "$BASE"...HEAD -- '*.php' 2>/dev/null || true)
[ -z "$CHANGED_PHP" ] && exit 0

# Detect which bounded contexts are affected
CONTEXTS=""
for ctx in "Pim/Structure" "Pim/Enrichment" "Channel" "Connectivity/Connection" "Platform/Job" "Platform/Installer"; do
    if echo "$CHANGED_PHP" | grep -q "src/Akeneo/$ctx/"; then
        # Find the coupling config for this context
        CD_CONFIG=$(find "src/Akeneo/$ctx" -name ".php_cd.php" -maxdepth 3 2>/dev/null | head -1 || true)
        if [ -n "$CD_CONFIG" ]; then
            CONTEXTS="$CONTEXTS $CD_CONFIG"
        fi
    fi
done

[ -z "$CONTEXTS" ] && exit 0

ERRORS=""
for config in $CONTEXTS; do
    RESULT=$(docker-compose run --rm -T php php vendor/bin/php-coupling-detector detect --config-file="$config" 2>&1 || true)
    if echo "$RESULT" | grep -qiE 'violation|error'; then
        CTX_NAME=$(dirname "$config" | sed 's|src/Akeneo/||')
        ERRORS="$ERRORS\n- $CTX_NAME: coupling violations detected"
    fi
done

if [ -n "$ERRORS" ]; then
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"COUPLING VIOLATIONS detected:${ERRORS}\nFix before pushing — coupling-back CI will fail.\"}}"
fi

exit 0
