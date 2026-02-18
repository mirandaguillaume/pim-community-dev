<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetAllResourceNamesQuery
{
    public function __construct(private readonly Connection $dbConnection)
    {
    }

    public function execute(): array
    {
        $query = <<<SQL
SELECT DISTINCT resource_name FROM pim_versioning_version;
SQL;

        return $this->dbConnection->executeQuery($query)->fetchFirstColumn();
    }
}
