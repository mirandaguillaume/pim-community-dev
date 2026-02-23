#!/bin/bash

set -eo pipefail

TEST_SUITE=$1

ALL_SCENARIOS=$(docker-compose run --rm -T php vendor/bin/behat --list-scenarios -p legacy -s $TEST_SUITE)

if [[ -n "$BEHAT_SPLIT" ]]; then
    # GitHub Actions sharding: BEHAT_SPLIT="N/TOTAL" (e.g. "3/10")
    SHARD_NUM="${BEHAT_SPLIT%%/*}"
    SHARD_TOTAL="${BEHAT_SPLIT##*/}"
    echo "Running Behat shard $SHARD_NUM of $SHARD_TOTAL for suite $TEST_SUITE"

    TEST_FILES=""
    while IFS= read -r scenario; do
        if [[ -n "$scenario" ]]; then
            HASH=$(echo -n "$scenario" | cksum | cut -d' ' -f1)
            ASSIGNED=$(( (HASH % SHARD_TOTAL) + 1 ))
            if [[ "$ASSIGNED" -eq "$SHARD_NUM" ]]; then
                TEST_FILES="$TEST_FILES $scenario"
            fi
        fi
    done <<< "$ALL_SCENARIOS"

    FILE_COUNT=$(echo $TEST_FILES | wc -w)
    echo "Shard $SHARD_NUM has $FILE_COUNT scenarios"

    if [[ -z "$TEST_FILES" || "$FILE_COUNT" -eq 0 ]]; then
        echo "No scenarios for this shard, skipping."
        exit 0
    fi
else
    TEST_FILES="$ALL_SCENARIOS"
    FILE_COUNT=$(echo $TEST_FILES | wc -w)
fi

echo "Running $FILE_COUNT scenarios in a single Behat invocation"

RERUN_FILE="var/tests/behat/rerun.txt"
mkdir -p var/tests/behat

# First pass: run all scenarios in one batch.
# Use rerun format to capture failed scenarios for targeted retry.
set +e
docker-compose exec -u www-data -T httpd ./vendor/bin/behat \
  --strict \
  --format pim --out var/tests/behat/batch_results \
  --format pretty --out std \
  --format rerun --out "$RERUN_FILE" \
  --colors \
  -p legacy -s $TEST_SUITE \
  $TEST_FILES
BATCH_RESULT=$?
set -eo pipefail

if [ $BATCH_RESULT -eq 0 ]; then
    echo "All scenarios passed on first run."
    exit 0
fi

# Check if rerun file has failed scenarios
if [ ! -s "$RERUN_FILE" ]; then
    echo "Batch failed but no rerun file found. Exiting with failure."
    exit 1
fi

FAILED_SCENARIOS=$(cat "$RERUN_FILE" | tr '\n' ' ')
FAILED_COUNT=$(echo $FAILED_SCENARIOS | wc -w)
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
