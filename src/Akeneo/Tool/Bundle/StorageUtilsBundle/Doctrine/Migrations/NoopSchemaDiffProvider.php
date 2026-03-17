<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Provider\SchemaDiffProvider;

/**
 * Schema diff provider that skips ALTER SQL generation for Doctrine Migrations.
 *
 * All Akeneo migrations use addSql() exclusively and never manipulate the Schema
 * object directly. DBAL 4's stricter column validation (ColumnLengthRequired for
 * VARCHAR) causes the default diff provider to fail when comparing introspected
 * columns whose custom types (e.g. PhpSerializedArrayType) don't map back cleanly.
 *
 * This provider delegates schema introspection to the real DBAL connection (so
 * migrations can still call $schema->getTable() / $schema->hasTable()), but
 * returns an empty diff — which is safe because no migration relies on it.
 */
final class NoopSchemaDiffProvider implements SchemaDiffProvider
{
    public function __construct(
        private readonly Connection $connection,
    ) {
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
