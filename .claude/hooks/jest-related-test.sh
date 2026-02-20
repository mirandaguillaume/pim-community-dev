#!/usr/bin/env bash
# Hook: PostToolUse on Edit|Write
# When a JS/TS source file is edited, finds and runs the related Jest test.
# Catches front-unit CI failures immediately.

set -euo pipefail

INPUT=$(cat)
FILE_PATH=$(echo "$INPUT" | jq -r '.tool_input.file_path // .tool_input.filePath // empty')

if [ -z "$FILE_PATH" ]; then
    exit 0
fi

# Only check JS/TS/TSX files (not test files themselves)
case "$FILE_PATH" in
    *.js|*.jsx|*.ts|*.tsx) ;;
    *) exit 0 ;;
esac

# Skip test files
if [[ "$FILE_PATH" == *.test.* ]] || [[ "$FILE_PATH" == *.spec.* ]] || [[ "$FILE_PATH" == *__tests__* ]] || [[ "$FILE_PATH" == *tests/* ]]; then
    exit 0
fi

# Skip non-source files (configs, etc.)
if [[ "$FILE_PATH" == *.config.* ]] || [[ "$FILE_PATH" == *webpack* ]]; then
    exit 0
fi

# Derive test file path - try common patterns
BASENAME=$(basename "$FILE_PATH")
NAMENOEXT="${BASENAME%.*}"
EXT="${BASENAME##*.}"
DIR=$(dirname "$FILE_PATH")

TEST_FILE=""
for candidate in \
    "${DIR}/${NAMENOEXT}.test.${EXT}" \
    "${DIR}/${NAMENOEXT}.spec.${EXT}" \
    "${DIR}/__tests__/${NAMENOEXT}.test.${EXT}" \
    "${DIR}/../tests/${NAMENOEXT}.test.${EXT}" \
    ; do
    if [ -f "$candidate" ]; then
        TEST_FILE="$candidate"
        break
    fi
done

# If no related test found, skip
if [ -z "$TEST_FILE" ]; then
    exit 0
fi

# Run the related test
RESULT=$(npx jest "$TEST_FILE" --no-coverage --passWithNoTests 2>&1 || true)

if echo "$RESULT" | grep -qE 'FAIL|failed'; then
    FAILURE=$(echo "$RESULT" | grep -E 'FAIL|Tests:.*failed' | head -3)
    echo "Related Jest test FAILED: $TEST_FILE" >&2
    echo "$FAILURE" >&2
    exit 2
fi

exit 0
