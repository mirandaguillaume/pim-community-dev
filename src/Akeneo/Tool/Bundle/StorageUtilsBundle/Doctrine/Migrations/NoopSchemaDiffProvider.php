<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Provider\SchemaDiffProvider;

/**
 * Schema diff provider that performs real schema introspection but skips diff generation.
 *
 * All Akeneo migrations use addSql() exclusively and never generate SQL from schema diffs,
 * so getSqlDiffToMigrate() always returns []. However, migrations may still read the $schema
 * object passed to up() (e.g. to check hasTable() / hasColumn()), so createFromSchema() must
 * return the actual database schema.
 *
 * The pim_catalog_product_identifiers table is excluded via doctrine.yml schema_filter to
 * avoid DBAL 4's introspection bug with MySQL 8 expression indexes (NULL COLUMN_NAME in
 * INFORMATION_SCHEMA.STATISTICS causes a TypeError in Index::_addColumn(string)).
 */
final class NoopSchemaDiffProvider implements SchemaDiffProvider
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function createFromSchema(): Schema
    {
        return $this->connection->createSchemaManager()->introspectSchema();
    }

    public function createToSchema(Schema $fromSchema): Schema
    {
        return clone $fromSchema;
    }

    /** @return string[] */
    public function getSqlDiffToMigrate(Schema $fromSchema, Schema $toSchema): array
    {
        return [];
    }
}
