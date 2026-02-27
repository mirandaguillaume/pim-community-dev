<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AnalyticsBundle\Query\Sql;

use Akeneo\Tool\Component\Analytics\GetConnectedAppsIdentifiersQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetConnectedAppsIdentifiersQuery implements GetConnectedAppsIdentifiersQueryInterface
{
    public function __construct(private readonly Connection $connection) {}

    public function execute(): array
    {
        $query = <<<SQL
            SELECT id
            FROM akeneo_connectivity_connected_app
            SQL;

        return $this->connection->fetchFirstColumn($query);
    }
}
