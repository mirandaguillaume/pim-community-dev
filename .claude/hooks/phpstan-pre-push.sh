#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git push)
# Runs PHPStan on changed PHP files before pushing.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+push' || exit 0

BASE="origin/master"

# Get changed PHP files compared to base (only in src/)
CHANGED_PHP=$(git diff --name-only "$BASE"...HEAD -- 'src/Akeneo/Pim/**.php' 2>/dev/null \
    | grep -v 'Spec\.php$' \
    | grep -v '/tests/' \
    | grep -v '/Test/' \
    || true)

[ -z "$CHANGED_PHP" ] && exit 0

FILE_COUNT=$(echo "$CHANGED_PHP" | wc -l)

# Run PHPStan in a single Docker invocation
RESULT=$(docker-compose run --rm -T php php -d memory_limit=1G \
    vendor/bin/phpstan analyse --level 2 $CHANGED_PHP 2>&1 || true)

if echo "$RESULT" | grep -qE '\[ERROR\]|Found [0-9]+ error'; then
    ERROR_SUMMARY=$(echo "$RESULT" | grep -E 'Found [0-9]+ error' || echo "PHPStan errors found")
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"PHPSTAN ERRORS in $FILE_COUNT changed files: $ERROR_SUMMARY\nFix before pushing — lint-back CI takes ~40min.\"}}"
fi

exit 0
