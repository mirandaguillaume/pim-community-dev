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

mkdir -p var/tests/behat

# First pass: run all scenarios in one batch.
# Uses "progress" formatter for lightweight stdout and "pim" for structured results.
# Behat automatically writes failed scenarios to its rerun cache (/tmp/behat_rerun_cache/).
set +e
docker-compose exec -u www-data -T httpd ./vendor/bin/behat \
  --strict \
  --format pim --out var/tests/behat/batch_results \
  --format progress --out std \
  --colors \
  -p legacy -s $TEST_SUITE \
  $TEST_FILES
BATCH_RESULT=$?
set -eo pipefail

if [ $BATCH_RESULT -eq 0 ]; then
    echo ""
    echo "All scenarios passed on first run."
    exit 0
fi

echo ""
echo "=== Some scenarios failed. Retrying with --rerun... ==="

# Second pass: use Behat's built-in --rerun to retry only failed scenarios.
# Must pass the same paths ($TEST_FILES) so the rerun cache key matches the first pass.
set +e
docker-compose exec -u www-data -T httpd ./vendor/bin/behat \
  --strict \
  --rerun \
  --format pim --out var/tests/behat/batch_results_retry \
  --format progress --out std \
  --colors \
  -p legacy -s $TEST_SUITE \
  $TEST_FILES
RETRY_RESULT=$?
set -eo pipefail

if [ $RETRY_RESULT -eq 0 ]; then
    echo ""
    echo "All previously failed scenarios passed on retry."
    exit 0
fi

echo ""
echo "Some scenarios still failed after retry."
exit 1
