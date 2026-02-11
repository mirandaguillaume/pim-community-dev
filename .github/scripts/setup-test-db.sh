#!/usr/bin/env bash
# Setup test database from seed dump
# Usage: setup-test-db.sh <db-name> <seed-file> [extra-tables-sql]

set -euo pipefail

DB_NAME="${1:-akeneo_pim_test}"
SEED_FILE="${2:-seed/db-seed.sql}"
EXTRA_TABLES_SQL="${3:-.github/scripts/behat-extra-tables.sql}"

echo "Setting up database: ${DB_NAME}"

# Create database if not exists
docker-compose exec -T mysql mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME}"

# Import seed dump
if [ -f "${SEED_FILE}" ]; then
    echo "Importing seed from ${SEED_FILE}..."
    docker-compose exec -T mysql sh -c "cat > /tmp/db-seed.sql && mysql -uroot -proot ${DB_NAME} < /tmp/db-seed.sql" < "${SEED_FILE}"
    echo "Seed imported successfully"
else
    echo "Warning: Seed file ${SEED_FILE} not found, skipping import"
fi

# Create extra tables if SQL file exists
if [ -f "${EXTRA_TABLES_SQL}" ]; then
    echo "Creating extra tables from ${EXTRA_TABLES_SQL}..."
    docker-compose exec -T mysql sh -c "cat > /tmp/extra-tables.sql && mysql -uroot -proot ${DB_NAME} < /tmp/extra-tables.sql" < "${EXTRA_TABLES_SQL}"
    echo "Extra tables created successfully"
fi

echo "Database ${DB_NAME} setup complete"
