#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git commit)
# Runs php-cs-fixer dry-run on staged PHP files in a single batch.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+commit' || exit 0

# Get staged PHP files (exclude Spec.php and Integration.php — they're excluded from cs-fixer)
STAGED_PHP=$(git diff --cached --name-only --diff-filter=ACMR -- '*.php' 2>/dev/null \
    | grep -v 'Spec\.php$' \
    | grep -v 'Integration\.php$' \
    || true)

[ -z "$STAGED_PHP" ] && exit 0

# Run cs-fixer in a single Docker invocation for all files at once
RESULT=$(docker-compose run --rm -T php php tools/php-cs-fixer fix \
    --dry-run --config=.php-cs-fixer.dist.php --path-mode=intersection \
    $STAGED_PHP 2>&1 || true)

if echo "$RESULT" | grep -qE 'that can be fixed'; then
    VIOLATION_FILES=$(echo "$RESULT" | grep -oE '[0-9]+\)' | wc -l)
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"CS-FIXER: $VIOLATION_FILES file(s) need formatting. Run: docker-compose run --rm php php tools/php-cs-fixer fix --config=.php-cs-fixer.dist.php\"}}"
fi

exit 0
