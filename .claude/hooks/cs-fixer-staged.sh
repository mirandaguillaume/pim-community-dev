#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git commit)
# Runs php-cs-fixer dry-run on staged PHP files before committing.
# Catches code style violations that would fail lint-back in CI.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty')

if [ -z "$COMMAND" ]; then
    exit 0
fi

# Only trigger on git commit commands
if ! echo "$COMMAND" | grep -qE 'git\s+commit'; then
    exit 0
fi

# Get staged PHP files (exclude Spec.php and Integration.php as they're excluded from cs-fixer)
STAGED_PHP=$(git diff --cached --name-only --diff-filter=ACMR -- '*.php' 2>/dev/null \
    | grep -v 'Spec\.php$' \
    | grep -v 'Integration\.php$' \
    || true)

if [ -z "$STAGED_PHP" ]; then
    exit 0
fi

# Run cs-fixer dry-run on staged files
VIOLATIONS=""
for file in $STAGED_PHP; do
    if [ -f "$file" ]; then
        RESULT=$(docker-compose run --rm -T php php vendor/bin/php-cs-fixer fix --dry-run --diff --config=.php_cs.php "$file" 2>&1 || true)
        if echo "$RESULT" | grep -q 'diff'; then
            VIOLATIONS="${VIOLATIONS}\n${file}"
        fi
    fi
done

if [ -n "$VIOLATIONS" ]; then
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"CS-FIXER VIOLATIONS detected in staged files:${VIOLATIONS}\nRun: docker-compose run --rm php php vendor/bin/php-cs-fixer fix --config=.php_cs.php on these files before committing.\"}}"
fi

exit 0
