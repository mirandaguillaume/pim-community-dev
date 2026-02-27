<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountVariantProductsInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class CountVariantProducts implements CountVariantProductsInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function forProductModelCodes(array $productModelCodes): int
    {
        if (0 === count($productModelCodes)) {
            return 0;
        }

        $sql = <<<'SQL'
                        select count(variant.id) 
                        from (
                            select product.id
                            from pim_catalog_product product
                            inner join pim_catalog_product_model product_model on product_model.id = product.product_model_id
                            where product_model.code in (:productModelCodes)
                            UNION 
                            select product.id
                            from pim_catalog_product product
                            inner join pim_catalog_product_model sub on sub.id = product.product_model_id
                            inner join pim_catalog_product_model root on root.id = sub.parent_id
                            where root.code in (:productModelCodes)
                        ) as variant
            SQL;

        $stmt = $this->connection->executeQuery(
            $sql,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => ArrayParameterType::STRING]
        );

        return (int) $stmt->fetchOne();
    }
}
