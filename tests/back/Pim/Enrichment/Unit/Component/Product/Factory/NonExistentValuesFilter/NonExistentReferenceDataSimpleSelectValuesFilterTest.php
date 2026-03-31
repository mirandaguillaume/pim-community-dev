<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentReferenceDataSimpleSelectValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetExistingReferenceDataCodes;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NonExistentReferenceDataSimpleSelectValuesFilterTest extends TestCase
{
    private GetExistingReferenceDataCodes|MockObject $getExistingReferenceDataCodes;
    private NonExistentReferenceDataSimpleSelectValuesFilter $sut;

    protected function setUp(): void
    {
        $this->getExistingReferenceDataCodes = $this->createMock(GetExistingReferenceDataCodes::class);
        $this->sut = new NonExistentReferenceDataSimpleSelectValuesFilter($this->getExistingReferenceDataCodes);
    }

    public function test_it_has_a_type(): void
    {
        $this->assertInstanceOf(NonExistentReferenceDataSimpleSelectValuesFilter::class, $this->sut);
    }

    public function test_it_filters_reference_data_simple_select_values(): void
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
                    [
                        AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT => [
                            'a_reference_data_simple_select' => [
                                [
                                    'identifier' => 'product_A',
                                    'values' => [
                                        '<all_channels>' => [
                                            'en_US' => 'option_toto',
                                            'fr_FR' => 'OPTION_WITH_OTHER_CASE',
                                        ],
                                    ],
                                    'properties' => [
                                        'reference_data_name' => 'some_reference_data'
                                    ]
                                ],
                                [
                                    'identifier' => 'product_B',
                                    'values' => [
                                        'ecommerce' => [
                                            'en_US' => 'non_existent_option'
                                        ],
                                    ],
                                    'properties' => [
                                        'reference_data_name' => 'some_reference_data'
                                    ]
                                ]
                            ],
                            'another_reference_data_simple_select' => [
                                [
                                    'identifier' => 'product_B',
                                    'values' => [
                                        'ecommerce' => [
                                            'en_US' => 'option_tata'
                                        ],
                                    ],
                                    'properties' => [
                                        'reference_data_name' => 'another_reference_data'
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
                                    ],
                                    'properties' => []
                                ],
                            ]
                        ]
                    ]
                );
        $this->getExistingReferenceDataCodes->method('fromReferenceDataNameAndCodes')->with('some_reference_data',
                    [
                        'option_toto',
                        'OPTION_WITH_OTHER_CASE',
                        'non_existent_option',
                    ])->willReturn(['option_toto', 'Option_With_Other_Case']);
        $this->getExistingReferenceDataCodes->method('fromReferenceDataNameAndCodes')->with('another_reference_data', ['option_tata'])->willReturn([]);
        /** @var OnGoingFilteredRawValues $filteredCollection */
                $filteredCollection = $this->sut->filter($ongoingFilteredRawValues);
        $this->assertEquals(
                    [
                        AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT => [
                            'a_reference_data_simple_select' => [
                                [
                                    'identifier' => 'product_A',
                                    'values' => [
                                        '<all_channels>' => [
                                            'en_US' => 'option_toto',
                                            'fr_FR' => 'Option_With_Other_Case',
                                        ],
                                    ],
                                    'properties' => [
                                        'reference_data_name' => 'some_reference_data'
                                    ]
                                ],
                                [
                                    'identifier' => 'product_B',
                                    'values' => [
                                        'ecommerce' => [
                                            'en_US' => ''
                                        ],
                                    ],
                                    'properties' => [
                                        'reference_data_name' => 'some_reference_data'
                                    ]
                                ]
                            ],
                            'another_reference_data_simple_select' => [
                                [
                                    'identifier' => 'product_B',
                                    'values' => [
                                        'ecommerce' => [
                                            'en_US' => ''
                                        ],
                                    ],
                                    'properties' => [
                                        'reference_data_name' => 'another_reference_data'
                                    ]
                                ]
                            ]
                        ],
                    ]
                , $filteredCollection->filteredRawValuesCollectionIndexedByType());
    }
}
