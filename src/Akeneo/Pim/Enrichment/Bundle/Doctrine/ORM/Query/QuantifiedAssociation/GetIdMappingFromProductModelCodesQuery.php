<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductModelCodesQueryInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetIdMappingFromProductModelCodesQuery implements GetIdMappingFromProductModelCodesQueryInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function execute(array $productModelCodes): IdMapping
    {
        if (empty($productModelCodes)) {
            return IdMapping::createFromMapping([]);
        }

        $query = <<<SQL
        SELECT id, code from pim_catalog_product_model WHERE code IN (:product_codes)
SQL;

        $mapping = array_column($this->connection->executeQuery(
            $query,
            ['product_codes' => $productModelCodes],
            ['product_codes' => ArrayParameterType::STRING]
        )->fetchAllAssociative(), 'code', 'id');

        return IdMapping::createFromMapping($mapping);
    }
}
