#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git commit)
# Runs PHPStan on staged PHP files before committing.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+commit' || exit 0

# Get staged PHP source files (exclude specs and tests)
STAGED_PHP=$(git diff --cached --name-only --diff-filter=ACMR -- '*.php' 2>/dev/null \
    | grep -v 'Spec\.php$' \
    | grep -v '/tests/' \
    | grep -v '/Test/' \
    || true)

[ -z "$STAGED_PHP" ] && exit 0

FILE_COUNT=$(echo "$STAGED_PHP" | wc -l)

RESULT=$(docker-compose run --rm -T php php -d memory_limit=1G \
    vendor/bin/phpstan analyse --level 2 --no-progress $STAGED_PHP 2>&1 || true)

if echo "$RESULT" | grep -qE '\[ERROR\]|Found [0-9]+ error'; then
    ERROR_SUMMARY=$(echo "$RESULT" | grep -E 'Found [0-9]+ error' | head -1 || echo "PHPStan errors found")
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"PHPSTAN ERRORS in $FILE_COUNT staged file(s): $ERROR_SUMMARY\nFix before committing — saves 40min CI wait.\"}}"
fi

exit 0
