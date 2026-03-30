<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentMultiSelectValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NonExistentMultiSelectValuesFilterTest extends TestCase
{
    private GetExistingAttributeOptionCodes|MockObject $getExistingAttributeOptionCodes;
    private NonExistentMultiSelectValuesFilter $sut;

    protected function setUp(): void
    {
        $this->getExistingAttributeOptionCodes = $this->createMock(GetExistingAttributeOptionCodes::class);
        $this->sut = new NonExistentMultiSelectValuesFilter($this->getExistingAttributeOptionCodes);
    }

    public function test_it_has_a_type(): void
    {
        $this->assertInstanceOf(NonExistentMultiSelectValuesFilter::class, $this->sut);
    }

    public function test_it_filters_multi_select_values(): void
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
                    [
                        AttributeTypes::OPTION_MULTI_SELECT => [
                            'a_multi_select' => [
                                [
                                    'identifier' => 'product_A',
                                    'values' => [
                                        'ecommerce' => [
                                            'en_US' => ['micHEL', 'sardou'],
                                        ],
                                        'tablet' => [
                                            'en_US' => ['jean', 'claude', 'van', 'damm'],
                                            'fr_FR' => ['des', 'Fraises'],
                                        ],
                                    ]
                                ],
                                [
                                    'identifier' => 'product_C',
                                    'values' => [
                                        'ecommerce' => [
                                            'en_US' => ['MIChel', 'sardou'],
                                        ],
                                        'tablet' => [
                                            '<all_locales>' => ['des', 'FRAISES', 'JEAN', 'TOUrloupe'],
                                        ],
                                    ]
                                ],
                            ],
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
                    'a_multi_select' => [
                        'micHEL',
                        'sardou',
                        'jean',
                        'claude',
                        'van',
                        'damm',
                        'des',
                        'Fraises',
                        'MIChel',
                        'FRAISES',
                        'JEAN',
                        'TOUrloupe',
                    ],
                ];
        $this->getExistingAttributeOptionCodes->expects($this->once())->method('fromOptionCodesByAttributeCode')->with($optionCodes)->willReturn([
                        'a_multi_select' => ['michel', 'fraises', 'tourlOUPE'],
                    ]);
        /** @var OnGoingFilteredRawValues $filteredCollection */
                $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
                    [
                        AttributeTypes::OPTION_MULTI_SELECT => [
                            'a_multi_select' => [
                                [
                                    'identifier' => 'product_A',
                                    'values' => [
                                        'ecommerce' => [
                                            'en_US' => ['michel'],
                                        ],
                                        'tablet' => [
                                            'en_US' => [],
                                            'fr_FR' => ['fraises'],
                                        ],
                                    ],
                                ],
                                [
                                    'identifier' => 'product_C',
                                    'values' => [
                                        'ecommerce' => [
                                            'en_US' => ['michel'],
                                        ],
                                        'tablet' => [
                                            '<all_locales>' => ['fraises', 'tourlOUPE'],
                                        ],
                                    ]
                                ],
                            ],
                        ],
                    ]
                );
    }
}
