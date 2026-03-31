<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentSimpleSelectValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NonExistentSimpleSelectValuesFilterTest extends TestCase
{
    private GetExistingAttributeOptionCodes|MockObject $getExistingAttributeOptionCodes;
    private NonExistentSimpleSelectValuesFilter $sut;

    protected function setUp(): void
    {
        $this->getExistingAttributeOptionCodes = $this->createMock(GetExistingAttributeOptionCodes::class);
        $this->sut = new NonExistentSimpleSelectValuesFilter($this->getExistingAttributeOptionCodes);
    }

    public function test_it_has_a_type(): void
    {
        $this->assertInstanceOf(NonExistentSimpleSelectValuesFilter::class, $this->sut);
    }

    public function test_it_filters_select_values(): void
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
                    [
                        AttributeTypes::OPTION_SIMPLE_SELECT => [
                            'a_select' => [
                                [
                                    'identifier' => 'product_A',
                                    'values' => [
                                        '<all_channels>' => [
                                            '<all_locales>' => 'option_ToTo'
                                        ],
                                    ]
                                ],
                                [
                                    'identifier' => 'product_B',
                                    'values' => [
                                        'ecommerce' => [
                                            'en_US' => 'option_tata'
                                        ],
                                    ]
                                ],
                                [
                                'identifier' => 'product_C',
                                'values' => [
                                    '<all_channels>' => [
                                        '<all_locales>' => 'OPTION_toto'
                                    ],
                                ]
                            ],
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
        $optionCodes = [
                    'a_select' => [
                        'option_ToTo',
                        'option_tata',
                        'OPTION_toto',
                    ]
                ];
        $this->getExistingAttributeOptionCodes->method('fromOptionCodesByAttributeCode')->with($optionCodes)->willReturn([
                        'a_select' => ['option_toto'],
                    ]);
        /** @var OnGoingFilteredRawValues $filteredCollection */
                $filteredCollection = $this->sut->filter($ongoingFilteredRawValues);
        $this->assertEquals(
                    [
                        AttributeTypes::OPTION_SIMPLE_SELECT => [
                            'a_select' => [
                                [
                                    'identifier' => 'product_A',
                                    'values' => [
                                        '<all_channels>' => [
                                            '<all_locales>' => 'option_toto'
                                        ],
                                    ],
                                ],
                                [
                                    'identifier' => 'product_B',
                                    'values' => [
                                        'ecommerce' => [
                                            'en_US' => ''
                                        ],
                                    ],
                                ],
                                [
                                    'identifier' => 'product_C',
                                    'values' => [
                                        '<all_channels>' => [
                                            '<all_locales>' => 'option_toto'
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                , $filteredCollection->filteredRawValuesCollectionIndexedByType());
    }
}
