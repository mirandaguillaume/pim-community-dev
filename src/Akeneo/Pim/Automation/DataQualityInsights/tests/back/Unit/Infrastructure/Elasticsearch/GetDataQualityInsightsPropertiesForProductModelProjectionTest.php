<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductModelIdsFromProductModelCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\GetDataQualityInsightsPropertiesForProductModelProjection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetDataQualityInsightsPropertiesForProductModelProjectionTest extends TestCase
{
    private GetProductModelScoresQueryInterface|MockObject $getProductModelScoresQuery;
    private GetProductModelIdsFromProductModelCodesQueryInterface|MockObject $getProductModelIdsFromProductModelCodesQuery;
    private ComputeProductsKeyIndicators|MockObject $computeProductsKeyIndicators;
    private ProductEntityIdFactoryInterface|MockObject $idFactory;
    private GetDataQualityInsightsPropertiesForProductModelProjection $sut;

    protected function setUp(): void
    {
        $this->getProductModelScoresQuery = $this->createMock(GetProductModelScoresQueryInterface::class);
        $this->getProductModelIdsFromProductModelCodesQuery = $this->createMock(GetProductModelIdsFromProductModelCodesQueryInterface::class);
        $this->computeProductsKeyIndicators = $this->createMock(ComputeProductsKeyIndicators::class);
        $this->idFactory = $this->createMock(ProductEntityIdFactoryInterface::class);
        $this->sut = new GetDataQualityInsightsPropertiesForProductModelProjection($this->getProductModelScoresQuery, $this->getProductModelIdsFromProductModelCodesQuery, $this->computeProductsKeyIndicators, $this->idFactory);
    }

    public function test_it_returns_additional_properties_from_product_model_codes(): void
    {
        $productModelId42 = new ProductModelId(42);
        $productModelId123 = new ProductModelId(123);
        $productModelId456 = new ProductModelId(456);
        $productModelIds = [
                    'product_model_1' => $productModelId42,
                    'product_model_2' => $productModelId123,
                    'product_model_without_rates' => $productModelId456,
                ];
        $productModelCodes = [
                    'product_model_1', 'product_model_2', 'product_model_without_rates',
                ];
        $collection = ProductModelIdCollection::fromStrings(['42', '123', '456']);
        $this->getProductModelIdsFromProductModelCodesQuery->method('execute')->with($productModelCodes)->willReturn($productModelIds);
        $this->idFactory->method('createCollection')->with(['42', '123', '456'])->willReturn($collection);
        $channelEcommerce = new ChannelCode('ecommerce');
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');
        $this->getProductModelScoresQuery->method('byProductModelIdCollection')->with($collection)->willReturn([
                    42 => new Read\Scores(
                        (new ChannelLocaleRateCollection())
                            ->addRate($channelMobile, $localeEn, new Rate(81))
                            ->addRate($channelMobile, $localeFr, new Rate(30))
                            ->addRate($channelEcommerce, $localeEn, new Rate(73)),
                        (new ChannelLocaleRateCollection())
                            ->addRate($channelMobile, $localeEn, new Rate(78))
                            ->addRate($channelMobile, $localeFr, new Rate(46))
                            ->addRate($channelEcommerce, $localeEn, new Rate(81))
                    ),
                    123 => new Read\Scores(
                        (new ChannelLocaleRateCollection())
                            ->addRate($channelMobile, $localeEn, new Rate(66)),
                        (new ChannelLocaleRateCollection())
                            ->addRate($channelMobile, $localeEn, new Rate(74)),
                    ),
                ]);
        $productModelsKeyIndicators = [
                    42 => [
                        'ecommerce' => [
                            'en_US' => [
                                'good_enrichment' => true,
                                'has_image' => true,
                            ],
                            'fr_FR' => [
                                'good_enrichment' => false,
                                'has_image' => null,
                            ],
                        ],
                        'mobile' => [
                            'en_US' => [
                                'good_enrichment' => null,
                                'has_image' => false,
                            ],
                        ],
                    ],
                    123 => [
                        'ecommerce' => [
                            'en_US' => [
                                'good_enrichment' => true,
                                'has_image' => true,
                            ],
                            'fr_FR' => [
                                'good_enrichment' => false,
                                'has_image' => true,
                            ],
                        ],
                        'mobile' => [
                            'en_US' => [
                                'good_enrichment' => false,
                                'has_image' => true,
                            ],
                        ],
                    ],
                ];
        $this->computeProductsKeyIndicators->method('compute')->with(ProductModelIdCollection::fromStrings(['42', '123', '456']))->willReturn($productModelsKeyIndicators);
        $this->assertSame([
                    'product_model_1' => [
                        'data_quality_insights' => [
                            'scores' => [
                                'mobile' => [
                                    'en_US' => 2,
                                    'fr_FR' => 5,
                                ],
                                'ecommerce' => [
                                    'en_US' => 3,
                                ],
                            ],
                            'scores_partial_criteria' => [
                                'mobile' => [
                                    'en_US' => 3,
                                    'fr_FR' => 5,
                                ],
                                'ecommerce' => [
                                    'en_US' => 2,
                                ],
                            ],
                            'key_indicators' => $productModelsKeyIndicators[42],
                        ],
                    ],
                    'product_model_2' => [
                        'data_quality_insights' => [
                            'scores' => [
                                'mobile' => [
                                    'en_US' => 4,
                                ],
                            ],
                            'scores_partial_criteria' => [
                                'mobile' => [
                                    'en_US' => 3,
                                ],
                            ],
                            'key_indicators' => $productModelsKeyIndicators[123],
                        ],
                    ],
                    'product_model_without_rates' => [
                        'data_quality_insights' => ['scores' => [], 'scores_partial_criteria' => [], 'key_indicators' => []],
                    ],
                ], $this->sut->fromProductModelCodes($productModelCodes));
    }
}
