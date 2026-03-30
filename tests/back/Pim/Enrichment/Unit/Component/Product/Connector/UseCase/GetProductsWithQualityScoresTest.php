<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScores;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class GetProductsWithQualityScoresTest extends TestCase
{
    private GetProductsWithQualityScores $sut;

    protected function setUp(): void
    {
        $this->sut = new GetProductsWithQualityScores();
    }

    private function buildConnectorProduct(
        string $identifier,
        $qualityScore,
        $uuid = null
    ): ConnectorProduct
    {
            return new ConnectorProduct(
                $uuid ?? Uuid::uuid4(),
                $identifier,
                new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
                new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
                true,
                'family_code',
                ['category_code_1', 'category_code_2'],
                ['group_code_1', 'group_code_2'],
                'parent_product_model_code',
                [],
                [],
                [],
                new ReadValueCollection(),
                $qualityScore,
                null
            );
        }
}
