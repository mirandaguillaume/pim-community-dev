#!/usr/bin/env bash
# Hook: PostToolUse on Edit|Write|Serena edit tools
# Auto-formats JS/TS files with Prettier after edit.
set -euo pipefail

INPUT=$(cat)

# Extract file path: native tools use file_path, Serena uses relative_path
FILE=$(echo "$INPUT" | jq -r '.tool_input.file_path // .tool_input.filePath // empty' 2>/dev/null)
if [ -z "$FILE" ]; then
    REL=$(echo "$INPUT" | jq -r '.tool_input.relative_path // empty' 2>/dev/null)
    if [ -n "$REL" ]; then
        FILE="${CLAUDE_PROJECT_DIR}/${REL}"
    fi
fi

[ -z "$FILE" ] && exit 0
[ -f "$FILE" ] || exit 0

case "$FILE" in
    *.ts|*.tsx|*.js|*.jsx)
        npx prettier --config "${CLAUDE_PROJECT_DIR}/.prettierrc.json" --write "$FILE" 2>/dev/null || true
        ;;
esac
exit 0
