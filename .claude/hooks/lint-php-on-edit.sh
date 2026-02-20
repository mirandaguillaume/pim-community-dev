#!/usr/bin/env bash
# Hook: PostToolUse on Edit|Write
# Runs php -l syntax check on edited PHP files.
# Fast check (~1s) to catch syntax errors immediately.

set -euo pipefail

INPUT=$(cat)
FILE_PATH=$(echo "$INPUT" | jq -r '.tool_input.file_path // .tool_input.filePath // empty')

if [ -z "$FILE_PATH" ]; then
    exit 0
fi

# Only check PHP files
if [[ "$FILE_PATH" != *.php ]]; then
    exit 0
fi

# Only check files that exist
if [ ! -f "$FILE_PATH" ]; then
    exit 0
fi

# Run syntax check
if ! php -l "$FILE_PATH" > /dev/null 2>&1; then
    ERROR=$(php -l "$FILE_PATH" 2>&1)
    echo "$ERROR" >&2
    exit 2
fi

exit 0
