<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductModelsWithQualityScores;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetProductModelsWithQualityScoresTest extends TestCase
{
    private GetProductModelScoresQueryInterface|MockObject $getProductModelScoresQuery;
    private FeatureFlag|MockObject $dataQualityInsightsFeature;
    private GetProductModelsWithQualityScores $sut;

    protected function setUp(): void
    {
        $this->getProductModelScoresQuery = $this->createMock(GetProductModelScoresQueryInterface::class);
        $this->dataQualityInsightsFeature = $this->createMock(FeatureFlag::class);
        $this->sut = new GetProductModelsWithQualityScores($this->getProductModelScoresQuery, $this->dataQualityInsightsFeature);
    }

    public function test_it_returns_product_models_with_empty_quality_scores_if_dqi_feature_is_disabled(): void
    {
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(false);
        $productModel1 = $this->givenAProductModelWithoutQualityScores('pm_1');
        $productModel2 = $this->givenAProductModelWithoutQualityScores('pm_2');
        $this->assertEquals($productModel1->buildWithQualityScores(new QualityScoreCollection([])), $this->sut->fromConnectorProductModel($productModel1));
        $this->assertEquals(new ConnectorProductModelList(2, [
                        $productModel1->buildWithQualityScores(new QualityScoreCollection([])),
                        $productModel2->buildWithQualityScores(new QualityScoreCollection([]))
                    ]), $this->sut->fromConnectorProductModelList(new ConnectorProductModelList(2, [$productModel1, $productModel2])));
    }

    public function test_it_returns_a_connector_product_model_with_quality_scores(): void
    {
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(true);
        $productModel = $this->givenAProductModelWithoutQualityScores('pm_1');
        $this->getProductModelScoresQuery->method('byProductModelCode')->with('pm_1')->willReturn($this->givenQualityScoresSample1());
        $this->assertEquals($productModel->buildWithQualityScores($this->givenQualityScoresSample1()), $this->sut->fromConnectorProductModel($productModel));
    }

    public function test_it_returns_a_list_of_connector_product_models_with_quality_scores_without_filters(): void
    {
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(true);
        $productModel1 = $this->givenAProductModelWithoutQualityScores('pm_1');
        $productModel2 = $this->givenAProductModelWithoutQualityScores('pm_2');
        $productModelList = new ConnectorProductModelList(2, [$productModel1, $productModel2]);
        $this->getProductModelScoresQuery->method('byProductModelCodes')->with(['pm_1', 'pm_2'])->willReturn([
                    'pm_1' => $this->givenQualityScoresSample1(),
                    'pm_2' => $this->givenQualityScoresSample2(),
                ]);
        $this->assertEquals(new ConnectorProductModelList(2, [
                    $productModel1->buildWithQualityScores($this->givenQualityScoresSample1()),
                    $productModel2->buildWithQualityScores($this->givenQualityScoresSample2())
                ]), $this->sut->fromConnectorProductModelList($productModelList, null, []));
    }

    public function test_it_returns_a_list_of_connector_product_models_with_quality_scores_filtered_by_channel(): void
    {
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(true);
        $productModel1 = $this->givenAProductModelWithoutQualityScores('pm_1');
        $productModel2 = $this->givenAProductModelWithoutQualityScores('pm_2');
        $productModelList = new ConnectorProductModelList(2, [$productModel1, $productModel2]);
        $this->getProductModelScoresQuery->method('byProductModelCodes')->with(['pm_1', 'pm_2'])->willReturn([
                    'pm_1' => $this->givenQualityScoresSample1(),
                    'pm_2' => $this->givenQualityScoresSample2(),
                ]);
        $expectedProductModelsList = new ConnectorProductModelList(2, [
                    $productModel1->buildWithQualityScores(new QualityScoreCollection([
                        'ecommerce' => [
                            'en_US' => new QualityScore('C', 75),
                            'fr_FR' => new QualityScore('E', 35),
                        ],
                    ])),
                    $productModel2->buildWithQualityScores(new QualityScoreCollection([
                        'ecommerce' => [
                            'en_US' => new QualityScore('D', 65),
                            'fr_FR' => new QualityScore('B', 85),
                        ],
                    ]))
                ]);
        $this->assertEquals($expectedProductModelsList, $this->sut->fromConnectorProductModelList($productModelList, 'ecommerce', []));
    }

    public function test_it_returns_a_list_of_connector_product_models_with_quality_scores_filtered_by_locales(): void
    {
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(true);
        $productModel1 = $this->givenAProductModelWithoutQualityScores('pm_1');
        $productModel2 = $this->givenAProductModelWithoutQualityScores('pm_2');
        $productModelList = new ConnectorProductModelList(2, [$productModel1, $productModel2]);
        $this->getProductModelScoresQuery->method('byProductModelCodes')->with(['pm_1', 'pm_2'])->willReturn([
                    'pm_1' => $this->givenQualityScoresSample1(),
                    'pm_2' => $this->givenQualityScoresSample2(),
                ]);
        $expectedProductModelsList = new ConnectorProductModelList(2, [
                    $productModel1->buildWithQualityScores(new QualityScoreCollection([
                        'ecommerce' => [
                            'fr_FR' => new QualityScore('E', 35),
                        ],
                        'print' => [
                            'fr_FR' => new QualityScore('E', 41),
                        ],
                    ])),
                    $productModel2->buildWithQualityScores(new QualityScoreCollection([
                        'ecommerce' => [
                            'fr_FR' => new QualityScore('B', 85),
                        ],
                        'print' => [
                            'fr_FR' => new QualityScore('C', 72),
                        ],
                    ]))
                ]);
        $this->assertEquals($expectedProductModelsList, $this->sut->fromConnectorProductModelList($productModelList, null, ['fr_FR']));
    }

    public function test_it_returns_a_list_of_connector_product_models_with_quality_scores_filtered_by_channel_an_locales(): void
    {
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(true);
        $productModel1 = $this->givenAProductModelWithoutQualityScores('pm_1');
        $productModel2 = $this->givenAProductModelWithoutQualityScores('pm_2');
        $productModelList = new ConnectorProductModelList(2, [$productModel1, $productModel2]);
        $this->getProductModelScoresQuery->method('byProductModelCodes')->with(['pm_1', 'pm_2'])->willReturn([
                    'pm_1' => $this->givenQualityScoresSample1(),
                    'pm_2' => $this->givenQualityScoresSample2(),
                ]);
        $expectedProductModelsList = new ConnectorProductModelList(2, [
                    $productModel1->buildWithQualityScores(new QualityScoreCollection([
                        'ecommerce' => [
                            'fr_FR' => new QualityScore('E', 35),
                        ],
                    ])),
                    $productModel2->buildWithQualityScores(new QualityScoreCollection([
                        'ecommerce' => [
                            'fr_FR' => new QualityScore('B', 85),
                        ],
                    ]))
                ]);
        $this->assertEquals($expectedProductModelsList, $this->sut->fromConnectorProductModelList($productModelList, 'ecommerce', ['fr_FR']));
    }

    private function givenAProductModelWithoutQualityScores(string $code): ConnectorProductModel
    {
            return new ConnectorProductModel(
                1234,
                $code,
                new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
                new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
                'my_parent',
                'my_family',
                'my_family_variant',
                ['workflow_status' => 'working_copy'],
                [],
                [],
                ['category_code_1'],
                new ReadValueCollection(),
                null
            );
        }

    private function givenQualityScoresSample1(): QualityScoreCollection
    {
            return new QualityScoreCollection([
                'ecommerce' => [
                    'en_US' => new QualityScore('C', 75),
                    'fr_FR' => new QualityScore('E', 35),
                ],
                'print' => [
                    'en_US' => new QualityScore('A', 91),
                    'fr_FR' => new QualityScore('E', 41),
                ],
            ]);
        }

    private function givenQualityScoresSample2(): QualityScoreCollection
    {
            return new QualityScoreCollection([
                'ecommerce' => [
                    'en_US' => new QualityScore('D', 65),
                    'fr_FR' => new QualityScore('B', 85),
                ],
                'print' => [
                    'en_US' => new QualityScore('B', 82),
                    'fr_FR' => new QualityScore('C', 72),
                ],
            ]);
        }
}
