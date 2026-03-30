<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Processor\Normalization;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization\GetNormalizedProductQualityScores;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class GetNormalizedProductQualityScoresTest extends TestCase
{
    private GetProductScoresQueryInterface|MockObject $getProductScoresQuery;
    private FeatureFlag|MockObject $dataQualityInsightsFeature;
    private GetNormalizedProductQualityScores $sut;

    protected function setUp(): void
    {
        $this->getProductScoresQuery = $this->createMock(GetProductScoresQueryInterface::class);
        $this->dataQualityInsightsFeature = $this->createMock(FeatureFlag::class);
        $this->sut = new GetNormalizedProductQualityScores($this->getProductScoresQuery, $this->dataQualityInsightsFeature);
    }

    public function test_it_returns_an_empty_array_when_the_feature_dqi_is_disabled(): void
    {
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(false);
        $this->getProductScoresQuery->expects($this->never())->method('byProductUuid')->with($this->anything());
        $this->assertSame([], $this->sut->__invoke(Uuid::uuid4()));
    }

    public function test_it_gets_normalized_quality_scores_without_filters_for_a_product(): void
    {
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(true);
        $uuid = Uuid::uuid4();
        $this->getProductScoresQuery->method('byProductUuid')->with($uuid)->willReturn(new QualityScoreCollection([
                    'ecommerce' => [
                        'en_US' => new QualityScore('A', 98),
                        'fr_FR' => new QualityScore('B', 87),
                    ]
                ]));
        $this->assertEquals([
                    'ecommerce' => [
                        'en_US' => 'A',
                        'fr_FR' => 'B',
                    ]
                ], $this->sut->__invoke($uuid));
    }

    public function test_it_gets_normalized_quality_scores_with_filters_on_channel_and_locales_for_a_product(): void
    {
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(true);
        $uuid = Uuid::uuid4();
        $this->getProductScoresQuery->method('byProductUuid')->with($uuid)->willReturn(new QualityScoreCollection([
                    'ecommerce' => [
                        'en_US' => new QualityScore('A', 98),
                        'fr_FR' => new QualityScore('B', 87),
                        'de_DE' => new QualityScore('B', 89),
                    ],
                    'mobile' => [
                        'en_US' => new QualityScore('C', 78),
                    ]
                ]));
        $this->assertEquals([
                    'ecommerce' => [
                        'en_US' => 'A',
                        'fr_FR' => 'B',
                    ]
                ], $this->sut->__invoke($uuid, 'ecommerce', ['en_US', 'fr_FR']));
    }
}
