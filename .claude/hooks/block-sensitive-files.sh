#!/usr/bin/env bash
# Hook: PreToolUse on Edit|Write|Serena edit tools
# Blocks edits to .env files and lock files.
set -euo pipefail

INPUT=$(cat)

FILE=$(echo "$INPUT" | jq -r '.tool_input.file_path // .tool_input.filePath // empty' 2>/dev/null)
if [ -z "$FILE" ]; then
    REL=$(echo "$INPUT" | jq -r '.tool_input.relative_path // empty' 2>/dev/null)
    if [ -n "$REL" ]; then
        FILE="${CLAUDE_PROJECT_DIR}/${REL}"
    fi
fi

[ -z "$FILE" ] && exit 0

BASENAME=$(basename "$FILE")
case "$BASENAME" in
    .env|.env.*)
        echo '{"decision":"block","reason":"Protected file: '"$BASENAME"'. Edit .env files manually."}'
        ;;
    composer.lock|yarn.lock)
        echo '{"decision":"block","reason":"Protected file: '"$BASENAME"'. Use composer/yarn to update lock files."}'
        ;;
esac
exit 0
