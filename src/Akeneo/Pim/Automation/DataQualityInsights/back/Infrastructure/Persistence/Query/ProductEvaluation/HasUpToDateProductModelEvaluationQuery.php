<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductModelIdFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class HasUpToDateProductModelEvaluationQuery implements HasUpToDateEvaluationQueryInterface
{
    public function __construct(
        private Connection $dbConnection,
        private ProductModelIdFactory $factory,
        private SqlPlatformHelperInterface $platformHelper,
    ) {
    }

    public function forEntityId(ProductEntityIdInterface $productId): bool
    {
        $productModelIdCollection = $this->factory->createCollection([(string) $productId]);
        $upToDateProducts = $this->forEntityIdCollection($productModelIdCollection);
        return !is_null($upToDateProducts);
    }

    public function forEntityIdCollection(ProductEntityIdCollection $productIdCollection): ?ProductModelIdCollection
    {
        if ($productIdCollection->isEmpty()) {
            return null;
        }

        $latestUpdate = $this->platformHelper->conditional('parent.updated > product_model.updated', 'parent.updated', 'product_model.updated');

        $query = <<<SQL
            SELECT product_model.id
            FROM pim_catalog_product_model AS product_model
                     LEFT JOIN pim_catalog_product_model AS parent ON parent.id = product_model.parent_id
            WHERE product_model.id IN (:product_ids)
              AND EXISTS(
                    SELECT 1 FROM pim_data_quality_insights_product_model_criteria_evaluation AS evaluation
                    WHERE evaluation.product_id = product_model.id
                      AND evaluation.evaluated_at >=
                          {$latestUpdate}
                )
            SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['product_ids' => $productIdCollection->toArrayString()],
            ['product_ids' => ArrayParameterType::INTEGER]
        );

        $result = $stmt->fetchAllAssociative();

        if (!is_array($result)) {
            return null;
        }

        $ids = array_map(fn ($resultRow) => $resultRow['id'], $result);

        if (empty($ids)) {
            return null;
        }

        return $this->factory->createCollection($ids);
    }
}
