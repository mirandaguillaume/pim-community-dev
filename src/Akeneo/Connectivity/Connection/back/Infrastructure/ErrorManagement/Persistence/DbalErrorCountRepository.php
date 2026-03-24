<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Persistence;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\ErrorCountRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalErrorCountRepository implements ErrorCountRepositoryInterface
{
    public function __construct(
        private readonly DbalConnection $dbalConnection,
        private readonly SqlPlatformHelperInterface $platformHelper,
    ) {
    }

    public function upsert(HourlyErrorCount $hourlyErrorCount): void
    {
        $upsert = $this->platformHelper->upsertClause(
            ['connection_code', 'error_datetime', 'error_type'],
            ['error_count = error_count + :error_count']
        );

        $upsertQuery = <<<SQL
            INSERT INTO akeneo_connectivity_connection_audit_error (connection_code, error_datetime, error_count, error_type)
            VALUES(:connection_code, :error_datetime, :error_count, :error_type)
            $upsert
            SQL;

        $this->dbalConnection->executeStatement(
            $upsertQuery,
            [
                'connection_code' => (string) $hourlyErrorCount->connectionCode(),
                'error_datetime' => $hourlyErrorCount->hourlyInterval()->fromDateTime(),
                'error_count' => $hourlyErrorCount->errorCount(),
                'error_type' => (string) $hourlyErrorCount->errorType(),
            ],
            [
                'error_datetime' => Types::DATETIME_IMMUTABLE,
                'error_count' => Types::INTEGER,
            ]
        );
    }
}
