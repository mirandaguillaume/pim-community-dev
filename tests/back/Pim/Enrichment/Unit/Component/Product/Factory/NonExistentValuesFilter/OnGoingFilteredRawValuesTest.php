<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PHPUnit\Framework\TestCase;

class OnGoingFilteredRawValuesTest extends TestCase
{
    private OnGoingFilteredRawValues $sut;

    protected function setUp(): void
    {
        $rawValues = [
        AttributeTypes::OPTION_SIMPLE_SELECT => [
        'a_select' => [
        [
        'identifier' => 'product_A',
        'values' => [
        '<all_channels>' => [
        '<all_locales>' => 'option_toto'
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
        ]
        ]
        ],
        AttributeTypes::OPTION_MULTI_SELECT => [
        'a_multi_select' => [
        [
        'identifier' => 'product_A',
        'values' => [
        'ecommerce' => [
        'en_US' => ['michel', 'sardou'],
        ],
        'tablet' => [
        'en_US' => ['jean', 'claude', 'van', 'damm'],
        'fr_FR' => ['des', 'fraises'],
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
        ];
        $this->sut = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType($rawValues);
    }

    public function test_it_has_a_type(): void
    {
        $this->assertInstanceOf(OnGoingFilteredRawValues::class, $this->sut);
    }

    public function test_it_returns_the_values_of_a_given_type(): void
    {
        $values = [
                    'a_multi_select' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => ['michel', 'sardou'],
                                ],
                                'tablet' => [
                                    'en_US' => ['jean', 'claude', 'van', 'damm'],
                                    'fr_FR' => ['des', 'fraises'],

                                ],
                            ]
                        ]
                    ],
                    'a_select' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => 'option_toto'
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
                        ]
                    ],
                ];
        $this->assertSame($values, $this->sut->notFilteredValuesOfTypes(AttributeTypes::OPTION_MULTI_SELECT, AttributeTypes::OPTION_SIMPLE_SELECT));
    }

    public function test_it_adds_some_filtered_values(): void
    {
        $rawValues = [
                    AttributeTypes::OPTION_SIMPLE_SELECT => [
                        'a_select' => [
                            [
                                'identifier' => 'product_A',
                                'values' => [
                                    '<all_channels>' => [
                                        '<all_locales>' => 'option_toto'
                                    ],
                                ]
                            ],
                            [
                                'identifier' => 'product_B',
                                'values' => [
                                    'ecommerce' => [
                                        'en_US' => ''
                                    ],
                                ]
                            ]
                        ]
                    ],
                    AttributeTypes::OPTION_MULTI_SELECT => [
                        'a_multi_select' => [
                            [
                                'identifier' => 'product_A',
                                'values' => [
                                    'ecommerce' => [
                                        'en_US' => ['sardou'],
                                    ],
                                    'tablet' => [
                                        'en_US' => ['jean', 'van', 'damm'],
                                        'fr_FR' => ['des'],

                                    ],
                                ]
                            ]
                        ]
                    ]
                ];
        $notFilteredValues = [
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
                ];
        /** @var OnGoingFilteredRawValues $newOngoingFilteredRawValues */
                $newOngoingFilteredRawValues = $this->sut->addFilteredValuesIndexedByType($rawValues);
        $this->assertEquals($notFilteredValues, $newOngoingFilteredRawValues->nonFilteredRawValuesCollectionIndexedByType());
    }
}
