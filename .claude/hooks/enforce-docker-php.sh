#!/usr/bin/env bash
# Hook: PreToolUse on Bash
# Blocks direct PHP/composer/phpstan/phpspec commands that should go through Docker.
# The host has PHP 8.1 but the project requires PHP 8.2+.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty')

if [ -z "$COMMAND" ]; then
    exit 0
fi

# Allow commands that already use docker-compose or docker
if echo "$COMMAND" | grep -qE '^\s*(docker-compose|docker)\s'; then
    exit 0
fi

# Allow make commands (they use docker internally)
if echo "$COMMAND" | grep -qE '^\s*(make|APP_ENV=\S+ make|PIM_CONTEXT=\S+ make)\s'; then
    exit 0
fi

# Block direct PHP execution that should use Docker
if echo "$COMMAND" | grep -qE '^\s*(php |vendor/bin/phpstan|vendor/bin/phpspec|vendor/bin/phpunit|vendor/bin/php-cs-fixer|/usr/local/bin/composer)\s'; then
    echo '{"hookSpecificOutput":{"hookEventName":"PreToolUse","additionalContext":"REMINDER: Use docker-compose run --rm php php <command> instead of running PHP directly. The host has PHP 8.1 but the project requires PHP 8.2+."}}'
    exit 0
fi

# Block composer commands that aren't safe without Docker
# (composer update --no-install with --ignore-platform-reqs is OK for lock resolution)
if echo "$COMMAND" | grep -qE '^\s*composer\s+(install|require|remove)\b'; then
    echo '{"hookSpecificOutput":{"hookEventName":"PreToolUse","additionalContext":"REMINDER: Use docker-compose run --rm php php /usr/local/bin/composer instead of running composer directly. Or use composer update --no-install --ignore-platform-reqs for lock-only resolution."}}'
    exit 0
fi

exit 0
