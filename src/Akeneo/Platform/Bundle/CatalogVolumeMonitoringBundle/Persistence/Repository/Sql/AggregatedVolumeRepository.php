<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Repository\Sql;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Model\AggregatedVolume;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Repository\AggregatedVolumeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AggregatedVolumeRepository implements AggregatedVolumeRepositoryInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly SqlPlatformHelperInterface $platformHelper,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function add(AggregatedVolume $aggregatedVolume): void
    {
        $upsert = $this->platformHelper->upsertClause(['volume_name'], ['volume = :volume', 'aggregated_at = :aggregatedAt']);
        $sql = <<<SQL
            INSERT INTO pim_aggregated_volume (volume_name, volume, aggregated_at)
            VALUES (:volumeName, :volume, :aggregatedAt) {$upsert}
            SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('volumeName', $aggregatedVolume->getVolumeName(), Types::STRING);
        $stmt->bindValue('volume', $aggregatedVolume->getVolume(), Types::JSON);
        $stmt->bindValue('aggregatedAt', $aggregatedVolume->aggregatedAt(), Types::DATETIME_MUTABLE);

        $stmt->executeStatement();
    }
}
