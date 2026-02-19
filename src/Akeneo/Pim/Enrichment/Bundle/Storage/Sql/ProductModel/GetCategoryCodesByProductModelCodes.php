<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * Query to fetch category codes by a given list of product model codes.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class GetCategoryCodesByProductModelCodes
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return array ['code1' => ['category1'], 'code2' => []]
     */
    public function fromProductModelCodes(array $productModelCodes): array
    {
        $productModelCodes = (fn(string ...$productModelCodes) => $productModelCodes)(... $productModelCodes);

        $results = [];

        foreach ($productModelCodes as $productModelCode) {
            $results[$productModelCode] = [];
        }

        $query = <<<SQL
SELECT product_model_code, JSON_ARRAYAGG(category_codes) as category_codes
FROM (
         SELECT model.code as product_model_code, category.code as category_codes
         FROM pim_catalog_product_model model
            INNER JOIN pim_catalog_category_product_model category_model ON model.id = category_model.product_model_id
            INNER JOIN pim_catalog_category category ON category_model.category_id = category.id
         WHERE model.code IN (:productModelCodes)
       UNION ALL
         SELECT model.code as product_model_code, category.code as category_codes
         FROM pim_catalog_product_model model
           INNER JOIN pim_catalog_product_model parent ON parent.id = model.parent_id
           INNER JOIN pim_catalog_category_product_model category_model ON parent.id = category_model.product_model_id
           INNER JOIN pim_catalog_category category ON category_model.category_id = category.id
         WHERE model.code IN (:productModelCodes)
) all_results
GROUP BY product_model_code
SQL;

        $queryResults = $this->connection->executeQuery(
            $query,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => ArrayParameterType::STRING]
        )->fetchAllAssociative();

        foreach ($queryResults as $queryResult) {
            $categoryCodes = json_decode((string) $queryResult['category_codes'], null, 512, JSON_THROW_ON_ERROR);
            sort($categoryCodes);
            $results[$queryResult['product_model_code']] = $categoryCodes;
        }

        return $results;
    }
}
