<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Processor\Normalization;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization\GetNormalizedProductModelQualityScores;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetNormalizedProductModelQualityScoresTest extends TestCase
{
    private GetProductModelScoresQueryInterface|MockObject $getProductModelScoresQuery;
    private FeatureFlag|MockObject $dataQualityInsightsFeature;
    private GetNormalizedProductModelQualityScores $sut;

    protected function setUp(): void
    {
        $this->getProductModelScoresQuery = $this->createMock(GetProductModelScoresQueryInterface::class);
        $this->dataQualityInsightsFeature = $this->createMock(FeatureFlag::class);
        $this->sut = new GetNormalizedProductModelQualityScores($this->getProductModelScoresQuery, $this->dataQualityInsightsFeature);
    }

    public function test_it_returns_an_empty_array_when_the_feature_dqi_is_disabled(): void
    {
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(false);
        $this->assertSame([], $this->sut->__invoke('a_product_model'));
    }

    public function test_it_gets_normalized_quality_scores_without_filters_for_a_product_model(): void
    {
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(true);
        $this->getProductModelScoresQuery->method('byProductModelCode')->with('a_product_model')->willReturn(new QualityScoreCollection([
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
                ], $this->sut->__invoke('a_product_model'));
    }

    public function test_it_gets_normalized_quality_scores_with_filters_on_channel_and_locales_for_a_product_model(): void
    {
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(true);
        $this->getProductModelScoresQuery->method('byProductModelCode')->with('a_product_model')->willReturn(new QualityScoreCollection([
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
                ], $this->sut->__invoke('a_product_model', 'ecommerce', ['en_US', 'fr_FR']));
    }
}
