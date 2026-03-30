<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\ComputeProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ComputeProductsKeyIndicatorsTest extends TestCase
{
    private GetLocalesByChannelQueryInterface|MockObject $getLocalesByChannelQuery;
    private ComputeProductsKeyIndicator|MockObject $goodEnrichment;
    private ComputeProductsKeyIndicator|MockObject $hasImage;
    private ComputeProductsKeyIndicators $sut;

    protected function setUp(): void
    {
        $this->getLocalesByChannelQuery = $this->createMock(GetLocalesByChannelQueryInterface::class);
        $this->goodEnrichment = $this->createMock(ComputeProductsKeyIndicator::class);
        $this->hasImage = $this->createMock(ComputeProductsKeyIndicator::class);
        $this->sut = new ComputeProductsKeyIndicators($this->getLocalesByChannelQuery, [$this->goodEnrichment, $this->hasImage]);
    }

    public function test_it_computes_all_the_key_indicators_for_a_given_list_of_products(): void
    {
        $this->getLocalesByChannelQuery->method('getArray')->willReturn([
                    'ecommerce' => ['en_US', 'fr_FR'],
                    'mobile' => ['en_US'],
                ]);
        $productIds = ProductUuidCollection::fromStrings(['0932dfd0-5f9a-49fb-ad31-a990339406a2', '3370280b-6c76-4720-aac1-ae3f9613d555']);
        $expectedKeyIndicators = [
                    '0932dfd0-5f9a-49fb-ad31-a990339406a2' => [
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
                    '3370280b-6c76-4720-aac1-ae3f9613d555' => [
                        'ecommerce' => [
                            'en_US' => [
                                'good_enrichment' => null,
                                'has_image' => null,
                            ],
                            'fr_FR' => [
                                'good_enrichment' => null,
                                'has_image' => null,
                            ],
                        ],
                        'mobile' => [
                            'en_US' => [
                                'good_enrichment' => null,
                                'has_image' => null,
                            ],
                        ],
                    ],
                ];
        $this->goodEnrichment->method('getCode')->willReturn(new KeyIndicatorCode('good_enrichment'));
        $this->hasImage->method('getCode')->willReturn(new KeyIndicatorCode('has_image'));
        $this->goodEnrichment->method('compute')->with($productIds)->willReturn([
                    '0932dfd0-5f9a-49fb-ad31-a990339406a2' => [
                        'ecommerce' => [
                            'en_US' => true,
                            'fr_FR' => false,
                        ],
                    ],
                    '3370280b-6c76-4720-aac1-ae3f9613d555' => [
                        'ecommerce' => [
                            'en_US' => null,
                            'fr_FR' => null,
                        ],
                        'mobile' => [
                            'en_US' => null,
                        ],
                    ],
                ]);
        $this->hasImage->method('compute')->with($productIds)->willReturn([
                    '0932dfd0-5f9a-49fb-ad31-a990339406a2' => [
                        'ecommerce' => [
                            'en_US' => true,
                        ],
                        'mobile' => [
                            'en_US' => false,
                        ],
                    ],
                ]);
        $this->assertEquals($expectedKeyIndicators, $this->sut->compute($productIds));
    }
}
