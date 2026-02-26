#!/usr/bin/env bash
# Hook: PreToolUse on Write
# Warns when writing files larger than 50KB.
set -euo pipefail

INPUT=$(cat)
CONTENT=$(echo "$INPUT" | jq -r '.tool_input.content // empty' 2>/dev/null)
FILE=$(echo "$INPUT" | jq -r '.tool_input.file_path // .tool_input.filePath // empty' 2>/dev/null)

[ -z "$CONTENT" ] || [ -z "$FILE" ] && exit 0

SIZE=${#CONTENT}
if [ "$SIZE" -gt 50000 ]; then
    BASENAME=$(basename "$FILE")
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"LARGE FILE WARNING: About to write ${SIZE} chars to $BASENAME. Verify this is intentional and not a generated/binary file.\"}}"
fi
exit 0
