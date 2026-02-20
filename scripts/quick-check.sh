#!/usr/bin/env bash
# Quick local validation before pushing — catches common CI failures early.
# Usage: ./scripts/quick-check.sh [--all]
#
# Default: runs fast checks only (< 30s)
# --all: also runs phpspec and deprecation checks (slower)

set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

ERRORS=0

check() {
    local label="$1"
    shift
    printf "%-45s" "$label..."
    if "$@" > /dev/null 2>&1; then
        echo -e "${GREEN}OK${NC}"
    else
        echo -e "${RED}FAIL${NC}"
        ERRORS=$((ERRORS + 1))
    fi
}

echo "=== Quick Pre-Push Checks ==="
echo ""

# 1. Composer validate
check "Composer schema valid" docker-compose run --rm php php /usr/local/bin/composer validate --no-check-all

# 2. Composer platform compatibility (catches PHP version mismatches)
check "Platform compatibility" docker-compose run --rm php php /usr/local/bin/composer check-platform-reqs --no-dev

# 3. PHP syntax check on changed files
CHANGED_PHP=$(git diff --name-only --diff-filter=ACMR HEAD~1 -- '*.php' 2>/dev/null || true)
if [ -n "$CHANGED_PHP" ]; then
    SYNTAX_OK=true
    for f in $CHANGED_PHP; do
        if [ -f "$f" ]; then
            if ! docker-compose run --rm php php -l "$f" > /dev/null 2>&1; then
                SYNTAX_OK=false
            fi
        fi
    done
    if $SYNTAX_OK; then
        printf "%-45s" "PHP syntax (changed files)..."
        echo -e "${GREEN}OK${NC}"
    else
        printf "%-45s" "PHP syntax (changed files)..."
        echo -e "${RED}FAIL${NC}"
        ERRORS=$((ERRORS + 1))
    fi
fi

# 4. CS-Fixer dry run on changed files
if [ -n "$CHANGED_PHP" ]; then
    check "Code style (php-cs-fixer)" docker-compose run --rm php php vendor/bin/php-cs-fixer fix --dry-run --diff --config=.php_cs.php
fi

# 5. PHPStan level 2 on src/Akeneo/Pim
check "PHPStan (level 2, Pim)" docker-compose run --rm php php -d memory_limit=1G vendor/bin/phpstan analyse src/Akeneo/Pim --level 2

if [ "${1:-}" = "--all" ]; then
    echo ""
    echo "=== Extended Checks ==="
    echo ""

    # 6. PHPSpec
    check "PHPSpec unit tests" docker-compose run --rm php php vendor/bin/phpspec run --no-interaction

    # 7. Deprecation analysis
    check "PHPStan deprecations" docker-compose run --rm php php -d memory_limit=2G vendor/bin/phpstan analyse -c phpstan-deprecations.neon --level 1

    # 8. Static checks (container lint)
    check "Static checks" make static-back
fi

echo ""
if [ $ERRORS -gt 0 ]; then
    echo -e "${RED}$ERRORS check(s) failed. Fix before pushing.${NC}"
    exit 1
else
    echo -e "${GREEN}All checks passed!${NC}"
fi
