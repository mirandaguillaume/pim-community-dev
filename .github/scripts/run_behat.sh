#!/bin/bash

set -eo pipefail

TEST_SUITE=$1

ALL_SCENARIOS=$(docker-compose run --rm -T php vendor/bin/behat --list-scenarios -p legacy -s $TEST_SUITE)
if command -v circleci >/dev/null 2>&1; then
    TEST_FILES=$(echo "$ALL_SCENARIOS" | circleci tests split --split-by=timings)
elif [[ -n "$BEHAT_SPLIT" ]]; then
    # GitHub Actions sharding: BEHAT_SPLIT="N/TOTAL" (e.g. "3/15")
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
fi
echo "TEST FILES ON THIS CONTAINER: $TEST_FILES"

fail=0
counter=1
total=$(echo $TEST_FILES | tr ' ' "\n" | wc -l)

for TEST_FILE in $TEST_FILES; do
    echo -e "\nLAUNCHING $TEST_FILE ($counter/$total):"
    output=$(basename $TEST_FILE)_$(uuidgen)

    set +e
    docker-compose exec -u www-data -T httpd ./vendor/bin/behat --strict --format pim --out var/tests/behat/${output} --format pretty --out std --colors -p legacy -s $TEST_SUITE $TEST_FILE ||
    (
      echo Retrying $TEST_FILE &&
      docker-compose exec -u www-data -T httpd /bin/bash -c "echo $TEST_FILE >> var/tests/behat/behats_retried.txt" &&
      docker-compose exec -u www-data -T httpd ./vendor/bin/behat --strict --format pim --out var/tests/behat/${output} --format pretty --out std --colors -p legacy -s $TEST_SUITE $TEST_FILE
    )

    fail=$(($fail + $?))
    counter=$(($counter + 1))
    set -eo pipefail
done

exit $fail
