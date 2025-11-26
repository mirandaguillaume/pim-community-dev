#!/bin/bash
#
# Usage:
#   run_phpunit.sh path/to/phpunit.xml .circleci/find_phpunit.php PIM_Integration_Test
#

set -eo pipefail

CONFIG_DIRECTORY="$1"
FIND_PHPUNIT_SCRIPT="$2"
TEST_SUITES="$3"

if command -v docker-compose >/dev/null 2>&1; then
    DOCKER_COMPOSE="docker-compose"
else
    DOCKER_COMPOSE="docker compose"
fi

TEST_DISCOVERY=$($DOCKER_COMPOSE run --rm -T php php "$FIND_PHPUNIT_SCRIPT" -c "$CONFIG_DIRECTORY" --testsuite "$TEST_SUITES")

SHARD_TOTAL=${TEST_SHARD_TOTAL:-1}
SHARD_INDEX=${TEST_SHARD_INDEX:-0}

if [ "$SHARD_TOTAL" -gt 1 ]; then
    echo "Sharding tests with TEST_SHARD_INDEX=$SHARD_INDEX / TEST_SHARD_TOTAL=$SHARD_TOTAL" >&2
    TEST_FILES=""
    i=0
    while read -r TEST_FILE; do
        if [ $((i % SHARD_TOTAL)) -eq "$SHARD_INDEX" ]; then
            TEST_FILES="${TEST_FILES}${TEST_FILE}\n"
        fi
        i=$((i + 1))
    done <<EOF
$TEST_DISCOVERY
EOF
    # Trim trailing newline
    TEST_FILES=$(printf "%b" "$TEST_FILES")
else
    TEST_FILES=$TEST_DISCOVERY
fi

fail=0
for TEST_FILE in $TEST_FILES; do
    echo $TEST_FILE

    set +e
    APP_ENV=test $DOCKER_COMPOSE run -T php ./vendor/bin/phpunit -c "$CONFIG_DIRECTORY" --log-junit var/tests/phpunit/phpunit_$(uuidgen).xml "$TEST_FILE"
    TEST_RESULT=$?
    if [ $TEST_RESULT -ne 0 ]; then
        echo "Test has failed (with code $TEST_RESULT): $TEST_FILE"
    fi
    fail=$(($fail + $TEST_RESULT))
    set -eo pipefail
done

exit $fail
