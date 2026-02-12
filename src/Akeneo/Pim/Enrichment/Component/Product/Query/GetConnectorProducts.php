<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetConnectorProducts
{
    /**
     * Ideally, we should not pass the PQB but a Product Query agnostic of the storage.
     * It would be much easier fake it.
     *
     * @return ConnectorProductList
     */
    public function fromProductQueryBuilder(
        ProductQueryBuilderInterface $productQueryBuilder,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductList;

    /**
     * @return ConnectorProduct
     * @throws ObjectNotFoundException
     */
    public function fromProductUuid(UuidInterface $productUuid, int $userId): ConnectorProduct;

    /**
     * @param UuidInterface[] $productUuids
     * @param string[]|null $attributesToFilterOn
     * @param string[]|null $localesToFilterOn
     *
     * @return ConnectorProductList
     */
    public function fromProductUuids(
        array $productUuids,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductList;
}
