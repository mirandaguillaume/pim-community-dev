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
APP_ENV=test docker-compose run -T php ./vendor/bin/phpunit \
  -c "$CONFIG_DIRECTORY" \
  --log-junit "var/tests/phpunit/phpunit_shard_${PHPUNIT_SHARD:-0}.xml" \
  $TEST_FILES
