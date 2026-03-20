#!/usr/bin/env bash
# Hook: PostToolUse on Edit|Write|Serena edit tools
# Runs PHPSpec for the related spec when a PHP source file is edited.
# Advisory only — does not block on failure.
set -euo pipefail

# ── Anti-parallel guard: skip if another phpspec-on-edit is already running ──
LOCKFILE="/tmp/phpspec-on-edit.lock"
exec 9>"$LOCKFILE"
flock -n 9 || exit 0

# ── Debounce: skip if ran less than 10s ago ──
STAMP="/tmp/phpspec-on-edit.stamp"
if [ -f "$STAMP" ]; then
    LAST=$(stat -c %Y "$STAMP" 2>/dev/null || echo 0)
    NOW=$(date +%s)
    if [ $((NOW - LAST)) -lt 10 ]; then
        exit 0
    fi
fi
touch "$STAMP"

INPUT=$(cat)

# Extract file path: supports native tools (file_path) and Serena (relative_path)
FILE=$(echo "$INPUT" | jq -r '.tool_input.file_path // .tool_input.filePath // empty' 2>/dev/null)
if [ -z "$FILE" ]; then
    REL=$(echo "$INPUT" | jq -r '.tool_input.relative_path // empty' 2>/dev/null)
    if [ -n "$REL" ]; then
        FILE="${CLAUDE_PROJECT_DIR}/${REL}"
    fi
fi

[ -z "$FILE" ] && exit 0
[ -f "$FILE" ] || exit 0

# Only PHP source files in src/ or components/
case "$FILE" in
    */src/*.php|*/components/*.php) ;;
    *) exit 0 ;;
esac

# Skip spec files, test files, and fixtures
case "$FILE" in
    *Spec.php|*Test.php|*Integration.php|*EndToEnd.php) exit 0 ;;
    */tests/*|*/Test/*|*/spec/*|*/fixtures/*|*/DataFixtures/*|*/Specification/*) exit 0 ;;
esac

# Extract class name and find matching spec
BASENAME=$(basename "$FILE" .php)
SPEC=""

# Search in all spec/test directories: src/, tests/back/, components/
for SEARCH_DIR in "${CLAUDE_PROJECT_DIR}/src" "${CLAUDE_PROJECT_DIR}/tests/back" "${CLAUDE_PROJECT_DIR}/components"; do
    [ -d "$SEARCH_DIR" ] || continue
    SPEC=$(find "$SEARCH_DIR" \( -path "*/tests/*" -o -path "*/spec/*" -o -path "*/Specification/*" \) -name "${BASENAME}Spec.php" -print -quit 2>/dev/null || true)
    [ -n "$SPEC" ] && break
done

[ -z "$SPEC" ] && exit 0

# Make spec path relative to project root
SPEC_REL="${SPEC#${CLAUDE_PROJECT_DIR}/}"

# Run PHPSpec via Docker (advisory, non-blocking)
RESULT=$(docker-compose run --rm -T php php vendor/bin/phpspec run "$SPEC_REL" --no-interaction 2>&1 || true)

if echo "$RESULT" | grep -qE 'failed|broken'; then
    FAILURES=$(echo "$RESULT" | grep -cE '✘|failed|broken' || echo "?")
    echo "PHPSpec: ${FAILURES} failure(s) in ${SPEC_REL}"
fi

exit 0
