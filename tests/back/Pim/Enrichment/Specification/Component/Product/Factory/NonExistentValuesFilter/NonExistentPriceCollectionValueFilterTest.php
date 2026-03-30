<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\FindActivatedCurrenciesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentPriceCollectionValueFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NonExistentPriceCollectionValueFilterTest extends TestCase
{
    private FindActivatedCurrenciesInterface|MockObject $findActivatedCurrencies;
    private NonExistentPriceCollectionValueFilter $sut;

    protected function setUp(): void
    {
        $this->findActivatedCurrencies = $this->createMock(FindActivatedCurrenciesInterface::class);
        $this->sut = new NonExistentPriceCollectionValueFilter($this->findActivatedCurrencies);
    }

    public function test_it_has_a_type(): void
    {
        $this->assertInstanceOf(NonExistentPriceCollectionValueFilter::class, $this->sut);
    }

    public function test_it_filters_price_collection_values(): void
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
                    [
                        AttributeTypes::PRICE_COLLECTION => [
                            'a_price_collection' => [
                                [
                                    'identifier' => 'product_A',
                                    'values' => [
                                        'ecommerce' => [
                                            'en_US' => [
                                                ['currency' => 'USD', 'amount' => '12.05']
                                            ],
                                        ],
                                        'tablet' => [
                                            'fr_FR' => [
                                                ['currency' => 'EUR', 'amount' => '14'],
                                                ['currency' => 'EUR', 'amount' => '16.04']
                                            ]
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        AttributeTypes::TEXTAREA => [
                            'a_description' => [
                                [
                                    'identifier' => 'product_B',
                                    'values' => [
                                        '<all_channels>' => [
                                            '<all_locales>' => 'plop'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                );
        $this->findActivatedCurrencies->method('forChannel')->with('ecommerce')->willReturn(['EUR']);
        $this->findActivatedCurrencies->method('forChannel')->with('tablet')->willReturn(['EUR']);
        /** @var OnGoingFilteredRawValues $filteredCollection */
                $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
                    [
                        AttributeTypes::PRICE_COLLECTION => [
                            'a_price_collection' => [
                                [
                                    'identifier' => 'product_A',
                                    'values' => [
                                        'tablet' => [
                                            'fr_FR' => [
                                                ['currency' => 'EUR', 'amount' => '16.04']
                                            ],
                                        ],
                                    ],
                                    'properties' => [],
                                ]
                            ]
                        ],
                    ]
                );
    }
}
