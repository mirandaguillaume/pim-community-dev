<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingProductModelCodesQueryInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

class FindNonExistingProductModelCodesQuery implements FindNonExistingProductModelCodesQueryInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function execute(array $productModelCodes): array
    {
        if (empty($productModelCodes)) {
            return [];
        }

        $query = <<<SQL
                    SELECT code FROM pim_catalog_product_model WHERE code IN (:product_model_codes)
            SQL;

        $results = $this->connection->executeQuery(
            $query,
            ['product_model_codes' => $productModelCodes],
            ['product_model_codes' => ArrayParameterType::STRING]
        )->fetchFirstColumn();

        $nonExistingProductModelCodes = array_values(array_diff($productModelCodes, $results));

        return $nonExistingProductModelCodes;
    }
}
