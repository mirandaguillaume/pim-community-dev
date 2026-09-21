#!/bin/bash
#
# Usage:
#   run_phpunit.sh path/to/phpunit.xml .github/scripts/find_phpunit.php PIM_Integration_Test
#
# Environment variables:
#   PHPUNIT_SHARD        - Current shard number (1-based)
#   PHPUNIT_TOTAL_SHARDS - Total number of shards
#   PHPUNIT_TIMING_FILE  - Optional JSON file with test durations for smart sharding
#

set -eo pipefail

CONFIG_DIRECTORY=$1
FIND_PHPUNIT_SCRIPT=$2
TEST_SUITES=$3

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

# Get all test files for this testsuite
TEST_FILES=$(docker-compose run --rm -T php php $FIND_PHPUNIT_SCRIPT -c $CONFIG_DIRECTORY --testsuite $TEST_SUITES)

# On GitHub Actions, use sharding if PHPUNIT_SHARD and PHPUNIT_TOTAL_SHARDS are set.
if [[ -n "$PHPUNIT_SHARD" && -n "$PHPUNIT_TOTAL_SHARDS" ]]; then
    echo "Running shard $PHPUNIT_SHARD of $PHPUNIT_TOTAL_SHARDS for testsuite $TEST_SUITES"

    SHARD_FILES=$(echo "$TEST_FILES" | "$SCRIPT_DIR/shard-by-timing.sh" \
      "${PHPUNIT_TIMING_FILE:-}" "$PHPUNIT_SHARD" "$PHPUNIT_TOTAL_SHARDS" 30)

    FILE_COUNT=$(echo "$SHARD_FILES" | grep -c '.' || true)
    echo "Shard $PHPUNIT_SHARD has $FILE_COUNT test files"

    if [[ -z "$SHARD_FILES" || "$FILE_COUNT" -eq 0 ]]; then
        echo "No test files for this shard, skipping."
        exit 0
    fi

    TEST_FILES="$SHARD_FILES"
else
    FILE_COUNT=$(echo "$TEST_FILES" | grep -c '.' || true)
fi

# Run all test files in a single PHPUnit process (one container, one bootstrap).
echo "Running $FILE_COUNT test files in a single PHPUnit invocation"
# Coverage is only ever requested nightly / on demand (PHPUNIT_COVERAGE). PHPUnit 10 raises
# the test runner warning "No filter is configured, code coverage will not be processed" when
# --coverage-clover is asked of a configuration that declares no <source> scope, and
# failOnPhpunitWarning defaults to true — so a single unscoped configuration exits 1 with every
# test green. Skip coverage loudly for such a configuration instead of reddening the nightly.
COVERAGE_ARGS=()
if [[ -n "$PHPUNIT_COVERAGE" ]]; then
    if [[ -f "$CONFIG_DIRECTORY" ]]; then
        CONFIG_FILE="$CONFIG_DIRECTORY"
    else
        CONFIG_FILE=$(ls "$CONFIG_DIRECTORY"/phpunit.xml "$CONFIG_DIRECTORY"/phpunit.xml.dist 2>/dev/null | head -1)
    fi

    if [[ -n "$CONFIG_FILE" ]] && grep -q "<source" "$CONFIG_FILE"; then
        COVERAGE_ARGS=(--coverage-clover "var/tests/phpunit/coverage-shard-${PHPUNIT_SHARD:-0}.xml")
    else
        echo "::warning::${CONFIG_FILE:-$CONFIG_DIRECTORY} declares no <source> scope; skipping coverage for $TEST_SUITES"
    fi
fi

if [[ ${#COVERAGE_ARGS[@]} -gt 0 ]]; then
    # Load Xdebug in coverage mode (the image ships XDEBUG_MODE=off as an env var, which
    # overrides -d xdebug.mode, so set it via -e) and emit a per-shard clover for Codecov.
    # The per-PR path below stays coverage-free so PR CI is not slowed by Xdebug.
    APP_ENV=test docker-compose run -T -e XDEBUG_MODE=coverage php \
      php -d zend_extension=xdebug ./vendor/bin/phpunit \
      -c "$CONFIG_DIRECTORY" \
      --log-junit "var/tests/phpunit/phpunit_shard_${PHPUNIT_SHARD:-0}.xml" \
      "${COVERAGE_ARGS[@]}" \
      $TEST_FILES
else
    APP_ENV=test docker-compose run -T php ./vendor/bin/phpunit \
      -c "$CONFIG_DIRECTORY" \
      --log-junit "var/tests/phpunit/phpunit_shard_${PHPUNIT_SHARD:-0}.xml" \
      $TEST_FILES
fi
