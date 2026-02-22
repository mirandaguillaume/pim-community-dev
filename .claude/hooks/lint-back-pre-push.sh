#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git push)
# Runs bounded-context lint-back targets for changed files before pushing.
# Uses each worktree's own vendor if available, otherwise skips.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+push' || exit 0

# Determine the working directory from the command
WORK_DIR=""
if echo "$COMMAND" | grep -qE '^\s*cd\s+'; then
    WORK_DIR=$(echo "$COMMAND" | sed -n 's/^\s*cd\s\+\([^ &;]*\).*/\1/p')
fi

if [ -n "$WORK_DIR" ] && [ -d "$WORK_DIR" ]; then
    GIT_DIR="$WORK_DIR"
else
    GIT_DIR="."
fi

# Resolve to absolute path
GIT_DIR=$(cd "$GIT_DIR" && pwd)

# Skip if not a git repo
if ! git -C "$GIT_DIR" rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    exit 0
fi

# Skip if on master/main
BRANCH=$(git -C "$GIT_DIR" branch --show-current 2>/dev/null || true)
if [ "$BRANCH" = "master" ] || [ "$BRANCH" = "main" ]; then
    exit 0
fi

# Skip if docker-compose is not available
if ! command -v docker-compose >/dev/null 2>&1 && ! docker compose version >/dev/null 2>&1; then
    exit 0
fi

# Find docker-compose.yml (main repo)
MAIN_REPO="${CLAUDE_PROJECT_DIR:-}"
if [ -z "$MAIN_REPO" ] || [ ! -f "$MAIN_REPO/docker-compose.yml" ]; then
    MAIN_REPO="/home/gumiranda/pim-community-dev"
fi
if [ ! -f "$MAIN_REPO/docker-compose.yml" ]; then
    exit 0
fi

# Install vendors if missing
if [ ! -f "$GIT_DIR/vendor/bin/phpstan" ]; then
    docker-compose -f "$MAIN_REPO/docker-compose.yml" run --rm -T \
        -v "$GIT_DIR:/srv/pim" -w /srv/pim php \
        composer install --no-interaction --no-scripts >/dev/null 2>&1 || exit 0
    # Still missing after install? Skip.
    if [ ! -f "$GIT_DIR/vendor/bin/phpstan" ]; then
        exit 0
    fi
fi

BASE="origin/master"
if ! git -C "$GIT_DIR" rev-parse "$BASE" >/dev/null 2>&1; then
    exit 0
fi

# Map directory prefixes to PHPStan commands (matching CI make targets exactly)
# Format: "config_neon|paths" — level comes from neon config, NOT overridden here
# For _inline_ (no neon): "level|paths"
declare -A CONTEXT_PHPSTAN=(
    ["src/Akeneo/Category/"]="src/Akeneo/Category/back/tests/phpstan.neon.dist"
    ["src/Akeneo/Channel/"]="src/Akeneo/Channel/back/tests/phpstan.neon.dist"
    ["src/Akeneo/Connectivity/Connection/"]="src/Akeneo/Connectivity/Connection/back/tests/phpstan.neon"
    ["src/Akeneo/Pim/Enrichment/Product/"]="src/Akeneo/Pim/Enrichment/Product/back/Test/phpstan.neon"
    # identifier-generator: CI runs 3 separate analyses (Infrastructure@max, Domain+App@max, tests@0)
    # Cannot reproduce here; skip (CI will catch issues)
    #["components/identifier-generator/"]="components/identifier-generator/back/tests/phpstan.neon"
    ["src/Akeneo/Platform/Bundle/ImportExportBundle/"]="_inline_|5|src/Akeneo/Platform/Bundle/ImportExportBundle"
    ["src/Akeneo/Platform/Installer/"]="src/Akeneo/Platform/Installer/back/tests/phpstan.neon"
    ["src/Akeneo/Platform/Job/"]="src/Akeneo/Platform/Job/back/tests/phpstan.neon.dist"
    # Migration files have their own PHPStan config (CI: migration-lint-back)
    ["upgrades/"]="upgrades/phpstan.neon"
    # Main src/Akeneo/Pim PHPStan (CI: lint-back, level 2)
    ["src/Akeneo/Pim/"]="_inline_|2|src/Akeneo/Pim"
)

# Get changed files compared to base
CHANGED=$(git -C "$GIT_DIR" diff --name-only "$BASE"...HEAD 2>/dev/null || true)
[ -z "$CHANGED" ] && exit 0

# Determine which contexts are affected
AFFECTED_PREFIXES=()
for prefix in "${!CONTEXT_PHPSTAN[@]}"; do
    if echo "$CHANGED" | grep -q "^${prefix}"; then
        AFFECTED_PREFIXES+=("$prefix")
    fi
done

[ ${#AFFECTED_PREFIXES[@]} -eq 0 ] && exit 0

# Docker run: always mount the worktree (GIT_DIR) as /srv/pim with its own vendor
DOCKER_RUN="docker-compose -f $MAIN_REPO/docker-compose.yml run --rm -T -v $GIT_DIR:/srv/pim php"

ERRORS=""
for prefix in "${AFFECTED_PREFIXES[@]}"; do
    ENTRY="${CONTEXT_PHPSTAN[$prefix]}"

    if [[ "$ENTRY" == _inline_* ]]; then
        # Format: _inline_|level|paths
        IFS='|' read -r _ level paths <<< "$ENTRY"
        PHPSTAN_CMD="php -d memory_limit=1G vendor/bin/phpstan analyse --level $level --no-progress $paths"
    else
        # Format: config_neon (level defined in neon, paths defined in neon)
        PHPSTAN_CMD="php -d memory_limit=1G vendor/bin/phpstan analyse --configuration $ENTRY --no-progress"
    fi

    RESULT=$($DOCKER_RUN $PHPSTAN_CMD 2>&1 || true)

    if echo "$RESULT" | grep -qE '\[ERROR\]|Found [0-9]+ error'; then
        CONTEXT_NAME=$(echo "$prefix" | sed 's|.*/\([^/]*\)/$|\1|; s|components/||')
        ERROR_SUMMARY=$(echo "$RESULT" | grep -E 'Found [0-9]+ error' | head -1 || echo "errors found")
        ERRORS="$ERRORS\n- $CONTEXT_NAME: $ERROR_SUMMARY"
    fi
done

# Run phpspec if any PHP files changed
if echo "$CHANGED" | grep -q '\.php$'; then
    PHPSPEC_RESULT=$($DOCKER_RUN php vendor/bin/phpspec run --no-interaction 2>&1 || true)
    # Extract summary line (e.g. "9906 examples (9903 passed, 1 skipped, 2 broken)")
    PHPSPEC_SUMMARY=$(echo "$PHPSPEC_RESULT" | grep -E '^[0-9]+ examples' | tail -1 || true)
    if echo "$PHPSPEC_SUMMARY" | grep -qE 'failed'; then
        FAILED_COUNT=$(echo "$PHPSPEC_SUMMARY" | grep -oP '\d+ failed' || echo "failures")
        ERRORS="$ERRORS\n- phpspec: $FAILED_COUNT"
    fi
fi

if [ -n "$ERRORS" ]; then
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"LINT-BACK ERRORS before push:$ERRORS\nFix these before pushing to avoid CI failures.\"}}"
fi

exit 0
