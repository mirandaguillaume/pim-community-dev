<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AverageMaxProductValues implements AverageMaxQuery
{
    private const string VOLUME_NAME = 'average_max_product_values';

    public function __construct(
        private readonly Connection $connection,
        private readonly SqlPlatformHelperInterface $platformHelper,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(): AverageMaxVolumes
    {
        $allValues = $this->platformHelper->jsonPathQuery('raw_values', '$.*.*.*');
        $valueCount = $this->platformHelper->jsonLength($allValues);

        $sql = <<<SQL
                        SELECT
                          MAX({$valueCount}) AS max,
                          CEIL(AVG({$valueCount})) AS average
                        FROM pim_catalog_product;
            SQL;
        $result = $this->connection->executeQuery($sql)->fetchAssociative();

        return new AverageMaxVolumes((int) $result['max'], (int) $result['average'], self::VOLUME_NAME);
    }
}
