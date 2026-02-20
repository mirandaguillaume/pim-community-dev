<?php

declare(strict_types = 1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\QuantifiedAssociation\GetIdMappingFromProductIdsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindQuantifiedAssociationTypeCodesInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

final readonly class GetProductQuantifiedAssociationsByProductModelCodes
{
    public function __construct(private Connection $connection, private GetIdMappingFromProductIdsQuery $getIdMappingFromProductIdsQuery, private FindQuantifiedAssociationTypeCodesInterface $findQuantifiedAssociationTypeCodes)
    {
    }

    /**
     * Executes SQL query to get product quantified associations from a set of product model codes.
     * Returns an array like:
     * [
     *      'productModelA' => [
     *          'PACK' => [
     *              'products' => [
     *                  ['identified' => 'productA','quantity' => 5]
     *              ]
     *          ]
     *      ]
     * ]
     */
    public function fromProductModelCodes(array $productModelCodes): array
    {
        if (empty($productModelCodes)) {
            return [];
        }

        $rows = $this->fetchQuantifiedAssociations($productModelCodes);

        return $this->hydrateQuantifiedAssociations($rows);
    }

    private function fetchQuantifiedAssociations(array $productModelCodes): array
    {
        $query = <<<SQL
SELECT
    product_model.code,
    JSON_MERGE_PRESERVE(COALESCE(parent_product_model.quantified_associations, '{}'), COALESCE(product_model.quantified_associations, '{}')) AS all_quantified_associations
FROM pim_catalog_product_model as product_model
LEFT JOIN pim_catalog_product_model parent_product_model ON parent_product_model.id = product_model.parent_id
WHERE product_model.code IN (:productModelCodes)
;
SQL;

        $rows = $this->connection->executeQuery(
            $query,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => ArrayParameterType::STRING]
        )->fetchAllAssociative();

        return $rows;
    }

    private function hydrateQuantifiedAssociations($rows): array
    {
        $validQuantifiedAssociationTypeCodes = $this->findQuantifiedAssociationTypeCodes->execute();

        $results = [];
        foreach ($rows as $row) {
            if (null === $row['all_quantified_associations']) {
                continue;
            }
            $allQuantifiedAssociationsWithProductId = json_decode((string) $row['all_quantified_associations'], true, 512, JSON_THROW_ON_ERROR);
            $associationWithIdentifiers = $this->associationsWithIdentifiers(
                $allQuantifiedAssociationsWithProductId,
                $validQuantifiedAssociationTypeCodes
            );
            if (!empty($associationWithIdentifiers)) {
                $productIdentifier = $row['code'];
                $results[$productIdentifier] = $associationWithIdentifiers;
            }
        }

        return $results;
    }

    private function associationsWithIdentifiers(
        array $allQuantifiedAssociationsWithProductIds,
        array $validQuantifiedAssociationTypeCodes
    ) {
        $productIdMapping = $this->fetchIdMapping($allQuantifiedAssociationsWithProductIds);

        $result = [];
        foreach ($allQuantifiedAssociationsWithProductIds as $associationTypeCode => $associationWithIds) {
            if (empty($associationWithIds) || !is_string($associationTypeCode)) {
                continue;
            }

            if (!in_array($associationTypeCode, $validQuantifiedAssociationTypeCodes)) {
                continue;
            }

            $uniqueQuantifiedAssociations = [];
            foreach ($associationWithIds['products'] as $associationWithProductId) {
                try {
                    $identifier = $productIdMapping->getIdentifier($associationWithProductId['id']);
                } catch (\Exception) {
                    continue;
                }
                $uniqueQuantifiedAssociations[$identifier] = [
                    'identifier' => $identifier,
                    'quantity'   => (int) $associationWithProductId['quantity']
                ];
            }
            if (!empty($uniqueQuantifiedAssociations)) {
                $result[$associationTypeCode]['products'] = array_values($uniqueQuantifiedAssociations);
            }
        }

        return $result;
    }

    private function productModelCodes(array $quantifiedAssociationWithProductModelId): array
    {
        return array_map(
            fn (array $quantifiedAssociations) => $quantifiedAssociations['id'],
            $quantifiedAssociationWithProductModelId['products'] ?? []
        );
    }

    private function fetchIdMapping(array $allQuantifiedAssociationsWithProductModelIds): IdMapping
    {
        $productModelCodes = [];
        foreach ($allQuantifiedAssociationsWithProductModelIds as $quantifiedAssociationWithId) {
            if (empty($quantifiedAssociationWithId)) {
                continue;
            }
            $productModelCodes = array_merge(
                $productModelCodes,
                $this->productModelCodes($quantifiedAssociationWithId)
            );
        }

        return $this->getIdMappingFromProductIdsQuery->execute($productModelCodes);
    }
}
