#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git commit)
# Runs PHPSpec for staged source files before committing.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+commit' || exit 0

# Get staged PHP source files (not specs, not tests)
STAGED_SRC=$(git diff --cached --name-only --diff-filter=ACMR -- '*.php' 2>/dev/null \
    | grep -v 'Spec\.php$' \
    | grep -v 'Integration\.php$' \
    | grep -v 'EndToEnd\.php$' \
    | grep -v '/tests/' \
    | grep -v '/Test/' \
    || true)

[ -z "$STAGED_SRC" ] && exit 0

# Find matching spec files
SPECS=""
for src in $STAGED_SRC; do
    # Convert source path to spec path patterns
    BASENAME=$(basename "$src" .php)
    SPEC=$(find . -path "*/spec/*" -name "${BASENAME}Spec.php" 2>/dev/null | head -1 || true)
    if [ -n "$SPEC" ]; then
        SPECS="$SPECS $SPEC"
    fi
done

[ -z "$SPECS" ] && exit 0

SPEC_COUNT=$(echo "$SPECS" | wc -w)
RESULT=$(docker-compose run --rm -T php php vendor/bin/phpspec run $SPECS --no-interaction 2>&1 || true)

if echo "$RESULT" | grep -qE 'failed|broken'; then
    FAILURES=$(echo "$RESULT" | grep -cE 'failed|broken' || echo "?")
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"PHPSPEC FAILURES: $FAILURES spec(s) failed out of $SPEC_COUNT related to staged files.\nFix before committing.\"}}"
fi

exit 0
