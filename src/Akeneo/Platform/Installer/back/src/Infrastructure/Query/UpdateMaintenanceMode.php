<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Query;

use Akeneo\Platform\Installer\Domain\Query\UpdateMaintenanceModeInterface;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class UpdateMaintenanceMode implements UpdateMaintenanceModeInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly SqlPlatformHelperInterface $platformHelper,
    ) {
    }

    public function execute(bool $enabled): void
    {
        $upsert = $this->platformHelper->upsertClause(
            ['code'],
            ['`values` = :values']
        );
        $query = <<<SQL
                INSERT INTO pim_configuration (`code`,`values`)
                VALUES (:code, :values)
                {$upsert}
            SQL;

        $this->connection->executeStatement($query, [
            'code' => 'maintenance_mode',
            'values' => ['enabled' => $enabled],
        ], [
            'code' => Types::STRING,
            'values' => Types::JSON,
        ]);
    }
}
