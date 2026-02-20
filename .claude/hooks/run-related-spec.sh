#!/usr/bin/env bash
# Hook: PostToolUse on Edit|Write
# When a PHP source file is edited, finds and runs the related PHPSpec spec.
# Catches spec failures immediately instead of waiting for CI phpspec job.

set -euo pipefail

INPUT=$(cat)
FILE_PATH=$(echo "$INPUT" | jq -r '.tool_input.file_path // .tool_input.filePath // empty')

if [ -z "$FILE_PATH" ]; then
    exit 0
fi

# Only check PHP source files (not specs, tests, or configs)
if [[ "$FILE_PATH" != *.php ]]; then
    exit 0
fi
if [[ "$FILE_PATH" == *Spec.php ]] || [[ "$FILE_PATH" == *Test.php ]] || [[ "$FILE_PATH" == *Integration.php ]]; then
    exit 0
fi
if [[ "$FILE_PATH" == *tests/* ]] || [[ "$FILE_PATH" == *Test/* ]]; then
    exit 0
fi

# Derive spec path from source path
# src/Akeneo/Foo/Bar.php -> src/Akeneo/Foo/spec/Bar/BarSpec.php (not reliable)
# Better approach: search for the class name in spec/ directories
CLASSNAME=$(basename "$FILE_PATH" .php)
SPEC_FILE=""

# Try common spec locations
DIR=$(dirname "$FILE_PATH")
# Look for spec/ relative to the file
for candidate in \
    "${DIR}/spec/${CLASSNAME}Spec.php" \
    "${DIR}/../spec/$(basename "$DIR")/${CLASSNAME}Spec.php" \
    "$(echo "$FILE_PATH" | sed 's|/src/|/tests/Unit/spec/|;s|\.php$|Spec.php|')" \
    "$(echo "$FILE_PATH" | sed 's|src/|spec/|;s|\.php$|Spec.php|')" \
    ; do
    if [ -f "$candidate" ]; then
        SPEC_FILE="$candidate"
        break
    fi
done

# If no spec found, try a broader search
if [ -z "$SPEC_FILE" ]; then
    SPEC_FILE=$(find "$(git rev-parse --show-toplevel)" -name "${CLASSNAME}Spec.php" -path "*/spec/*" 2>/dev/null | head -1 || true)
fi

if [ -z "$SPEC_FILE" ] || [ ! -f "$SPEC_FILE" ]; then
    exit 0
fi

# Run the spec
RESULT=$(docker-compose run --rm -T php php vendor/bin/phpspec run "$SPEC_FILE" --no-interaction 2>&1 || true)

if echo "$RESULT" | grep -qE 'broken|failed|FAILED'; then
    FAILURE=$(echo "$RESULT" | grep -E 'broken|failed|examples' | tail -3)
    echo "Related spec FAILED: $SPEC_FILE" >&2
    echo "$FAILURE" >&2
    exit 2
fi

exit 0
