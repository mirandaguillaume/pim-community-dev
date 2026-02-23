#!/bin/bash
#
# Environment variables:
#   BEHAT_SPLIT        - Shard spec "N/TOTAL" (e.g. "3/10")
#   BEHAT_TIMING_FILE  - Optional JSON file with scenario durations for smart sharding
#

set -eo pipefail

TEST_SUITE=$1
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

ALL_SCENARIOS=$(docker-compose run --rm -T php vendor/bin/behat --list-scenarios -p legacy -s $TEST_SUITE)

if [[ -n "$BEHAT_SPLIT" ]]; then
    SHARD_NUM="${BEHAT_SPLIT%%/*}"
    SHARD_TOTAL="${BEHAT_SPLIT##*/}"
    echo "Running Behat shard $SHARD_NUM of $SHARD_TOTAL for suite $TEST_SUITE"

    TEST_FILES=$(echo "$ALL_SCENARIOS" | "$SCRIPT_DIR/shard-by-timing.sh" \
      "${BEHAT_TIMING_FILE:-}" "$SHARD_NUM" "$SHARD_TOTAL" 60)

    FILE_COUNT=$(echo "$TEST_FILES" | grep -c '.' || true)
    echo "Shard $SHARD_NUM has $FILE_COUNT scenarios"

    if [[ -z "$TEST_FILES" || "$FILE_COUNT" -eq 0 ]]; then
        echo "No scenarios for this shard, skipping."
        exit 0
    fi
else
    TEST_FILES="$ALL_SCENARIOS"
    FILE_COUNT=$(echo "$TEST_FILES" | grep -c '.' || true)
fi

echo "Running $FILE_COUNT scenarios in a single Behat invocation"

BATCH_OUTPUT_FILE="var/tests/behat/batch_output.txt"
mkdir -p var/tests/behat

# First pass: run all scenarios in one batch.
# Pipe through tee to capture output for failed scenario extraction.
set +e
docker-compose exec -u www-data -T httpd ./vendor/bin/behat \
  --strict \
  --format pim --out var/tests/behat/batch_results \
  --format pretty --out std \
  --colors \
  -p legacy -s $TEST_SUITE \
  $TEST_FILES 2>&1 | tee "$BATCH_OUTPUT_FILE"
BATCH_RESULT=${PIPESTATUS[0]}
set -eo pipefail

if [ $BATCH_RESULT -eq 0 ]; then
    echo "All scenarios passed on first run."
    exit 0
fi

# Extract failed scenarios from captured output.
# Strip ANSI escape codes, then find the "Failed scenarios:" block.
FAILED_SCENARIOS=$(sed 's/\x1b\[[0-9;]*[a-zA-Z]//g' "$BATCH_OUTPUT_FILE" \
  | awk '/^Failed scenarios:$/,/^[0-9]/' \
  | grep -E '^\s+' | sed 's/^\s*//' || true)

if [ -z "$FAILED_SCENARIOS" ]; then
    echo "Batch failed but could not extract failed scenarios. Exiting with failure."
    exit 1
fi

FAILED_COUNT=$(echo "$FAILED_SCENARIOS" | wc -w)
echo ""
echo "=== $FAILED_COUNT scenario(s) failed. Retrying individually... ==="

fail=0
for SCENARIO in $FAILED_SCENARIOS; do
    echo -e "\nRetrying: $SCENARIO"
    output=$(basename $SCENARIO)_retry_$(uuidgen)

    set +e
    docker-compose exec -u www-data -T httpd ./vendor/bin/behat \
      --strict \
      --format pim --out "var/tests/behat/${output}" \
      --format pretty --out std \
      --colors \
      -p legacy -s $TEST_SUITE \
      $SCENARIO
    RETRY_RESULT=$?
    set -eo pipefail

    if [ $RETRY_RESULT -ne 0 ]; then
        echo "FAILED (after retry): $SCENARIO"
        docker-compose exec -u www-data -T httpd /bin/bash -c "echo $SCENARIO >> var/tests/behat/behats_retried.txt"
        fail=$((fail + 1))
    else
        echo "PASSED on retry: $SCENARIO"
    fi
done

if [ $fail -gt 0 ]; then
    echo "$fail scenario(s) still failed after retry."
    exit 1
fi

echo "All previously failed scenarios passed on retry."
exit 0
