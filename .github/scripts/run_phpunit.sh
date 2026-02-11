#!/bin/bash
#
# Usage:
#   run_phpunit.sh path/to/phpunit.xml .github/scripts/find_phpunit.php PIM_Integration_Test
#
# Environment variables:
#   PHPUNIT_SHARD        - Current shard number (1-based)
#   PHPUNIT_TOTAL_SHARDS - Total number of shards
#

set -eo pipefail

CONFIG_DIRECTORY=$1
FIND_PHPUNIT_SCRIPT=$2
TEST_SUITES=$3

# CircleCI workers keep historical timing data, so we split per-file there.
if command -v circleci >/dev/null 2>&1; then
    TEST_FILES=$(docker-compose run --rm -T php php $FIND_PHPUNIT_SCRIPT -c $CONFIG_DIRECTORY --testsuite $TEST_SUITES)
    TEST_FILES=$(echo "$TEST_FILES" | circleci tests split --split-by=timings)

    fail=0
    for TEST_FILE in $TEST_FILES; do
        echo $TEST_FILE

        set +e
        APP_ENV=test docker-compose run -T php ./vendor/bin/phpunit -c $CONFIG_DIRECTORY --log-junit var/tests/phpunit/phpunit_$(uuidgen).xml $TEST_FILE
        TEST_RESULT=$?
        if [ $TEST_RESULT -ne 0 ]; then
            echo "Test has failed (with code $TEST_RESULT): $TEST_FILE"
        fi
        fail=$(($fail + $TEST_RESULT))
        set -eo pipefail
    done

    exit $fail
fi

# On GitHub Actions, use sharding if PHPUNIT_SHARD and PHPUNIT_TOTAL_SHARDS are set.
if [[ -n "$PHPUNIT_SHARD" && -n "$PHPUNIT_TOTAL_SHARDS" ]]; then
    echo "Running shard $PHPUNIT_SHARD of $PHPUNIT_TOTAL_SHARDS for testsuite $TEST_SUITES"

    # Get all test files for this testsuite
    TEST_FILES=$(docker-compose run --rm -T php php $FIND_PHPUNIT_SCRIPT -c $CONFIG_DIRECTORY --testsuite $TEST_SUITES)

    # Filter to only this shard using hash-based distribution
    SHARD_FILES=""
    while IFS= read -r file; do
        if [[ -n "$file" ]]; then
            # Use cksum for hash-based assignment (portable across systems)
            HASH=$(echo -n "$file" | cksum | cut -d' ' -f1)
            ASSIGNED_SHARD=$(( (HASH % PHPUNIT_TOTAL_SHARDS) + 1 ))
            if [[ "$ASSIGNED_SHARD" -eq "$PHPUNIT_SHARD" ]]; then
                SHARD_FILES="$SHARD_FILES $file"
            fi
        fi
    done <<< "$TEST_FILES"

    # Count files
    FILE_COUNT=$(echo $SHARD_FILES | wc -w)
    echo "Shard $PHPUNIT_SHARD has $FILE_COUNT test files"

    if [[ -z "$SHARD_FILES" || "$FILE_COUNT" -eq 0 ]]; then
        echo "No test files for this shard, skipping."
        exit 0
    fi

    # Run tests for this shard
    fail=0
    for TEST_FILE in $SHARD_FILES; do
        echo "Running: $TEST_FILE"

        set +e
        APP_ENV=test docker-compose run -T php ./vendor/bin/phpunit \
          -c "$CONFIG_DIRECTORY" \
          --log-junit "var/tests/phpunit/phpunit_$(uuidgen).xml" \
          "$TEST_FILE"
        TEST_RESULT=$?
        if [ $TEST_RESULT -ne 0 ]; then
            echo "Test has failed (with code $TEST_RESULT): $TEST_FILE"
        fi
        fail=$(($fail + $TEST_RESULT))
        set -eo pipefail
    done

    exit $fail
fi

# Fallback: single phpunit run (no sharding)
APP_ENV=test docker-compose run -T php ./vendor/bin/phpunit \
  -c "$CONFIG_DIRECTORY" \
  --log-junit "var/tests/phpunit/phpunit_$(uuidgen).xml" \
  --testsuite "$TEST_SUITES"
