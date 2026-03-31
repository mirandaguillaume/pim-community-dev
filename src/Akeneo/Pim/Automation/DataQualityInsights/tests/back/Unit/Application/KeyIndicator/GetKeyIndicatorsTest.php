<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator\GetKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator\ProductKeyIndicatorsByFeatureRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\KeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetProductKeyIndicatorsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetKeyIndicatorsTest extends TestCase
{
    private GetProductKeyIndicatorsQueryInterface|MockObject $getProductKeyIndicatorsQuery;
    private GetProductKeyIndicatorsQueryInterface|MockObject $getProductModelKeyIndicatorsQuery;
    private ProductKeyIndicatorsByFeatureRegistry|MockObject $productKeyIndicatorsRegistry;
    private GetKeyIndicators $sut;

    protected function setUp(): void
    {
        $this->getProductKeyIndicatorsQuery = $this->createMock(GetProductKeyIndicatorsQueryInterface::class);
        $this->getProductModelKeyIndicatorsQuery = $this->createMock(GetProductKeyIndicatorsQueryInterface::class);
        $this->productKeyIndicatorsRegistry = $this->createMock(ProductKeyIndicatorsByFeatureRegistry::class);
        $this->productKeyIndicatorsRegistry->method('getCodes')->willReturn([
            new KeyIndicatorCode('good_enrichment'),
            new KeyIndicatorCode('has_image'),
        ]);
        $this->sut = new GetKeyIndicators($this->getProductKeyIndicatorsQuery, $this->getProductModelKeyIndicatorsQuery, $this->productKeyIndicatorsRegistry);
    }

    public function test_it_gives_key_indicators_for_all_products_and_product_models(): void
    {
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
        $goodEnrichment = new KeyIndicatorCode('good_enrichment');
        $hasImage = new KeyIndicatorCode('has_image');
        $this->getProductKeyIndicatorsQuery->method('all')->willReturn([
                        'good_enrichment' => new KeyIndicator($goodEnrichment, 15, 60),
                        'has_image' => new KeyIndicator($hasImage, 25, 26),
                    ]);
        $this->getProductModelKeyIndicatorsQuery->method('all')->willReturn([
                        'good_enrichment' => new KeyIndicator($goodEnrichment, 23, 52),
                        'has_image' => new KeyIndicator($hasImage, 24, 89),
                    ]);
        $this->assertEquals([
                    'good_enrichment' => [
                        'products' => [
                            'totalGood' => 15,
                            'totalToImprove' => 60,
                        ],
                        'product_models'
                        => [
                            'totalGood' => 23,
                            'totalToImprove' => 52,
                        ],
                    ],
                    'has_image' => [
                        'products' => [
                            'totalGood' => 25,
                            'totalToImprove' => 26,
                        ],
                        'product_models'
                        => [
                            'totalGood' => 24,
                            'totalToImprove' => 89,
                        ],
                    ],
                ], $this->sut->all($channel, $locale));
    }

    public function test_it_gives_key_indicators_for_a_given_family(): void
    {
        $family = new FamilyCode('shoes');
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
        $goodEnrichment = new KeyIndicatorCode('good_enrichment');
        $hasImage = new KeyIndicatorCode('has_image');
        $this->getProductKeyIndicatorsQuery->method('byFamily')->willReturn([
                        'good_enrichment' => new KeyIndicator($goodEnrichment, 15, 60),
                    ]);
        $this->getProductModelKeyIndicatorsQuery->method('byFamily')->willReturn([
                        'good_enrichment' => new KeyIndicator($goodEnrichment, 30, 40),
                    ]);
        $this->assertEquals([
                    'good_enrichment' => [
                        'products' => [
                            'totalGood' => 15,
                            'totalToImprove' => 60,
                        ],
                        'product_models'
                        => [
                            'totalGood' => 30,
                            'totalToImprove' => 40,
                        ],
                    ],
                    'has_image' => [
                        'products' => [
                            'totalGood' => 0,
                            'totalToImprove' => 0,
                        ],
                        'product_models'
                        => [
                            'totalGood' => 0,
                            'totalToImprove' => 0,
                        ],
                    ],
                ], $this->sut->byFamily($channel, $locale, $family));
    }

    public function test_it_gives_key_indicators_for_a_given_category(): void
    {
        $category = new CategoryCode('shoes');
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
        $goodEnrichment = new KeyIndicatorCode('good_enrichment');
        $hasImage = new KeyIndicatorCode('has_image');
        $this->getProductKeyIndicatorsQuery->method('byCategory')->willReturn([
                        'good_enrichment' => new KeyIndicator($goodEnrichment, 15, 60),
                        'has_image' => new KeyIndicator($hasImage, 0, 0),
                    ]);
        $this->getProductModelKeyIndicatorsQuery->method('byCategory')->willReturn([
                        'good_enrichment' => new KeyIndicator($goodEnrichment, 45, 65),
                        'has_image' => new KeyIndicator($hasImage, 0, 0),
                    ]);
        $this->assertEquals([
                    'good_enrichment' => [
                        'products' => [
                            'totalGood' => 15,
                            'totalToImprove' => 60,
                        ],
                        'product_models'
                        => [
                            'totalGood' => 45,
                            'totalToImprove' => 65,
                        ],
                    ],
                    'has_image' => [
                        'products' => [
                            'totalGood' => 0,
                            'totalToImprove' => 0,
                        ],
                        'product_models'
                        => [
                            'totalGood' => 0,
                            'totalToImprove' => 0,
                        ],
                    ],
                ], $this->sut->byCategory($channel, $locale, $category));
    }
}
