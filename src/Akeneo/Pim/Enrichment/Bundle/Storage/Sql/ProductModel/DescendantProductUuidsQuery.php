<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\DescendantProductUuidsQueryInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class DescendantProductUuidsQuery implements DescendantProductUuidsQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function fetchFromProductModelIds(array $productModelIds): array
    {
        if (empty($productModelIds)) {
            return [];
        }

        $sql = <<<SQL
            SELECT BIN_TO_UUID(uuid) AS uuid FROM pim_catalog_product WHERE product_model_id IN (:productModelIds)
            SQL;

        $resultRows = $this->connection->executeQuery(
            $sql,
            ['productModelIds' => $productModelIds],
            ['productModelIds' => ArrayParameterType::INTEGER]
        )->fetchAllAssociative();

        return array_map(fn ($rowData) => Uuid::fromString($rowData['uuid']), $resultRows);
    }
}
