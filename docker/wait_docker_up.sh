#!/usr/bin/env bash

set -e

DOCKER_COMPOSE_CMD=${DOCKER_COMPOSE:-$(command -v docker-compose >/dev/null 2>&1 && echo docker-compose || echo "docker compose")}
MAX_COUNTER=${WAIT_FOR_MAX_COUNTER:-120}

wait_for() {
    local service=$1
    local check_command=$2
    local counter=1

    echo "Waiting for ${service} server…"
    while ! ${DOCKER_COMPOSE_CMD} exec ${service} sh -c "${check_command}" > /dev/null 2>&1; do
        counter=$((counter + 1))
        if [ ${counter} -gt ${MAX_COUNTER} ]; then
            echo "We have been waiting for ${service} too long already; failing." >&2
            exit 1
        fi;
        sleep 1
    done
    echo "${service^} server is running!"
}

wait_for mysql "mysql --protocol TCP -uroot -proot -e 'show databases;'"
wait_for elasticsearch "curl -s -k --fail 'http://elasticsearch:9200/_cluster/health?wait_for_status=yellow&timeout=1s'"
