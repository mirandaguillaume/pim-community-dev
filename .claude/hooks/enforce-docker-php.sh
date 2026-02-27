#!/usr/bin/env bash
# Hook: PreToolUse on Bash
# Reminds to use Docker for PHP/composer commands. The host has PHP 8.1, project needs 8.2+.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0

# Allow commands that already use docker-compose or docker
echo "$COMMAND" | grep -qE '^\s*(docker-compose|docker)\s' && exit 0

# Allow make commands (they use docker internally)
echo "$COMMAND" | grep -qE '^\s*(make|APP_ENV=\S+\s+make|PIM_CONTEXT=\S+\s+make)\s' && exit 0

# Block direct PHP execution that should use Docker
if echo "$COMMAND" | grep -qE '^\s*(php |vendor/bin/phpstan|vendor/bin/phpspec|vendor/bin/phpunit|tools/php-cs-fixer)\b'; then
    echo '{"hookSpecificOutput":{"hookEventName":"PreToolUse","additionalContext":"REMINDER: Use docker-compose run --rm php php <command> instead of running PHP directly. Host has PHP 8.1, project needs 8.2+."}}'
    exit 0
fi

# Block ALL direct composer commands — must go through Docker
if echo "$COMMAND" | grep -qE '^\s*composer\b'; then
    echo '{"hookSpecificOutput":{"hookEventName":"PreToolUse","additionalContext":"REMINDER: Use docker-compose run --rm php composer <command> instead of running composer directly. Host has PHP 8.1, project needs 8.2+."}}'
    exit 0
fi

exit 0
