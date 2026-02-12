<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAllBlacklistedAttributeCodesInterface;
use Doctrine\DBAL\Connection;

final readonly class GetAllBlacklistedAttributeCodes implements GetAllBlacklistedAttributeCodesInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(): array
    {
        $sql = <<<SQL
SELECT attribute_code
FROM `pim_catalog_attribute_blacklist`
SQL;

        return $this->connection
            ->executeQuery($sql)
            ->fetchFirstColumn();
    }
}
