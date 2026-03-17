<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Provider\SchemaDiffProvider;

/**
 * Schema diff provider that skips all schema introspection and diff generation.
 *
 * All Akeneo migrations use addSql() exclusively and never manipulate the Schema
 * object directly, so returning an empty Schema from createFromSchema() is safe.
 *
 * This also avoids DBAL 4's schema introspection bug with MySQL 8 expression indexes
 * (NULL column names in INFORMATION_SCHEMA.STATISTICS cause a TypeError in
 * Index::_addColumn(string)), which would otherwise crash doctrine:migrations:execute
 * Pre-Checks when the pim_catalog_product_identifiers table with its functional index exists.
 */
final class NoopSchemaDiffProvider implements SchemaDiffProvider
{
    public function createFromSchema(): Schema
    {
        return new Schema();
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
