#!/usr/bin/env bash
# Hook: PostToolUse on Edit|Write|Serena edit tools
# Auto-formats JS/TS files with Prettier after edit.
# Uses Docker yarn matching castor's yarnRun helper for CI parity.
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

# Convert absolute host path to relative (Docker mounts ./ at /srv/pim with working_dir=/srv/pim)
REL_FILE="${FILE#${CLAUDE_PROJECT_DIR}/}"

case "$FILE" in
    *.ts|*.tsx|*.js|*.jsx)
        docker-compose run --rm -T -u node \
            -e YARN_REGISTRY \
            -e PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=1 \
            -e PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome \
            node yarn prettier --write "$REL_FILE" 2>/dev/null || true
        ;;
esac
exit 0
