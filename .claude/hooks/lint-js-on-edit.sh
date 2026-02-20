#!/usr/bin/env bash
# Hook: PostToolUse on Edit|Write
# Runs ESLint + Prettier check on edited JS/TS/TSX files.
# Catches front-lint CI failures immediately.

set -euo pipefail

INPUT=$(cat)
FILE_PATH=$(echo "$INPUT" | jq -r '.tool_input.file_path // .tool_input.filePath // empty')

if [ -z "$FILE_PATH" ]; then
    exit 0
fi

# Only check JS/TS/TSX/JSX files
case "$FILE_PATH" in
    *.js|*.jsx|*.ts|*.tsx) ;;
    *) exit 0 ;;
esac

# Only check files that exist
if [ ! -f "$FILE_PATH" ]; then
    exit 0
fi

# Run ESLint on the file (using npx to avoid global install issues)
RESULT=$(npx eslint "$FILE_PATH" --no-error-on-unmatched-pattern 2>&1 || true)

if echo "$RESULT" | grep -qE '[0-9]+ error'; then
    ERROR_COUNT=$(echo "$RESULT" | grep -oE '[0-9]+ error' | head -1)
    echo "ESLint: $ERROR_COUNT(s) in $FILE_PATH" >&2
    echo "$RESULT" | grep -E '^\s+[0-9]+:[0-9]+' | head -5 >&2
    exit 2
fi

exit 0
