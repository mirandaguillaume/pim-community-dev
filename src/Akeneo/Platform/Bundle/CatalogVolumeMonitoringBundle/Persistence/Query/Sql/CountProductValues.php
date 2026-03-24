<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountProductValues implements CountQuery
{
    private const string VOLUME_NAME = 'count_product_values';

    public function __construct(
        private readonly Connection $connection,
        private readonly SqlPlatformHelperInterface $platformHelper,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(): CountVolume
    {
        $allValues = $this->platformHelper->jsonPathQuery('raw_values', '$.*.*.*');
        $valueCount = $this->platformHelper->jsonLength($allValues);

        $sql = <<<SQL
                       SELECT SUM({$valueCount}) as sum_product_values
                       FROM pim_catalog_product
            SQL;
        $result = $this->connection->executeQuery($sql)->fetchAssociative();

        return new CountVolume((int) $result['sum_product_values'], self::VOLUME_NAME);
    }
}
