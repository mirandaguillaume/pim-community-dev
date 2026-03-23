<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Infrastructure\Repository;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Domain\Model\IndexMigration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Domain\Query\IndexMigrationRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class IndexMigrationRepository implements IndexMigrationRepositoryInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly SqlPlatformHelperInterface $platformHelper,
    ) {
    }

    public function save(IndexMigration $indexMigration): void
    {
        $upsert = $this->platformHelper->upsertClause(
            ['index_alias', 'hash'],
            ['`values` = :values']
        );
        $sql = <<<SQL
                INSERT INTO pim_index_migration (`index_alias`, `hash`, `values`)
                VALUES (:index_alias, :hash, :values)
                {$upsert};
            SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'index_alias' => $indexMigration->getIndexAlias(),
                'hash' => $indexMigration->getIndexConfigurationHash(),
                'values' => $indexMigration->normalize(),
            ],
            ['values' => Types::JSON]
        );
    }
}
