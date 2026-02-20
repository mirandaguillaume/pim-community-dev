#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git push)
# Runs PHPStan level 2 on changed PHP files before pushing.
# Catches the most common CI failure (lint-back PHPStan errors).

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty')

if [ -z "$COMMAND" ]; then
    exit 0
fi

# Only trigger on git push commands
if ! echo "$COMMAND" | grep -qE 'git\s+push'; then
    exit 0
fi

# Find the base branch (usually master)
BASE="origin/master"

# Get changed PHP files compared to base (only in src/)
CHANGED_PHP=$(git diff --name-only "$BASE"...HEAD -- 'src/Akeneo/Pim/**.php' 2>/dev/null \
    | grep -v 'Spec\.php$' \
    | grep -v '/tests/' \
    | grep -v '/Test/' \
    || true)

if [ -z "$CHANGED_PHP" ]; then
    exit 0
fi

FILE_COUNT=$(echo "$CHANGED_PHP" | wc -l)

# Run PHPStan on changed files only (much faster than full analysis)
TMPFILE=$(mktemp /tmp/phpstan-files-XXXXXX)
echo "$CHANGED_PHP" > "$TMPFILE"

RESULT=$(docker-compose run --rm -T php php -d memory_limit=1G vendor/bin/phpstan analyse --level 2 $(cat "$TMPFILE" | tr '\n' ' ') 2>&1 || true)
rm -f "$TMPFILE"

if echo "$RESULT" | grep -qE '\[ERROR\]|errors'; then
    ERROR_SUMMARY=$(echo "$RESULT" | grep -E 'Found [0-9]+ error' || echo "PHPStan errors found")
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"PHPSTAN ERRORS in $FILE_COUNT changed files:\n$ERROR_SUMMARY\nFix these before pushing — they will fail lint-back in CI (~40min wait).\"}}"
fi

exit 0
