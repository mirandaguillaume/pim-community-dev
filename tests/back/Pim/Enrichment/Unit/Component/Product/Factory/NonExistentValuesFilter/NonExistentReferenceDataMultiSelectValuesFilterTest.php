<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentReferenceDataMultiSelectValuesFilter;
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
class NonExistentReferenceDataMultiSelectValuesFilterTest extends TestCase
{
    private GetExistingReferenceDataCodes|MockObject $getExistingReferenceDataCodes;
    private NonExistentReferenceDataMultiSelectValuesFilter $sut;

    protected function setUp(): void
    {
        $this->getExistingReferenceDataCodes = $this->createMock(GetExistingReferenceDataCodes::class);
        $this->sut = new NonExistentReferenceDataMultiSelectValuesFilter($this->getExistingReferenceDataCodes);
    }

    public function test_it_has_a_type(): void
    {
        $this->assertInstanceOf(NonExistentReferenceDataMultiSelectValuesFilter::class, $this->sut);
    }

    public function test_it_filters_multi_select_values(): void
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
                    [
                        AttributeTypes::REFERENCE_DATA_MULTI_SELECT => [
                            'a_reference_data_multi_select' => [
                                [
                                    'identifier' => 'product_A',
                                    'values' => [
                                        'ecommerce' => [
                                            'en_US' => ['MiChel', 'sardou'],
                                        ],
                                        'tablet' => [
                                            'en_US' => ['jean', 'CLAUDE', 'van', 'damme'],
                                            'fr_FR' => ['des', 'fRaises'],

                                        ],
                                    ],
                                    'properties' => [
                                        'reference_data_name' => 'some_reference_data'
                                    ]
                                ],
                            ],
                            'another_reference_data_multi_select' => [
                                [
                                    'identifier' => 'product_B',
                                    'values' => [
                                        'mobile' => [
                                            'en_US' => ['des', 'damme'],
                                        ],
                                        'tablet' => [
                                            'en_US' => ['Claude', 'fRaiseS'],
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
                                    ]
                                ]
                            ]
                        ]
                    ]
                );
        $referenceDataCodes = [
                    'MiChel',
                    'sardou',
                    'jean',
                    'CLAUDE',
                    'van',
                    'damme',
                    'des',
                    'fRaises'
                ];
        $this->getExistingReferenceDataCodes->method('fromReferenceDataNameAndCodes')->with('some_reference_data', $referenceDataCodes)->willReturn(['Michel', 'Fraises']);
        $this->getExistingReferenceDataCodes->method('fromReferenceDataNameAndCodes')->with('another_reference_data', ['des', 'damme', 'Claude', 'fRaiseS'])->willReturn(['Claude', 'Damme']);
        /** @var OnGoingFilteredRawValues $filteredCollection */
                $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
                    [
                        AttributeTypes::REFERENCE_DATA_MULTI_SELECT => [
                            'a_reference_data_multi_select' => [
                                [
                                    'identifier' => 'product_A',
                                    'values' => [
                                        'ecommerce' => [
                                            'en_US' => ['Michel'],
                                        ],
                                        'tablet' => [
                                            'en_US' => [],
                                            'fr_FR' => ['Fraises'],
                                        ],
                                    ],
                                    'properties' => [
                                        'reference_data_name' => 'some_reference_data'
                                    ]
                                ]
                            ],
                            'another_reference_data_multi_select' => [
                                [
                                    'identifier' => 'product_B',
                                    'values' => [
                                        'mobile' => [
                                            'en_US' => ['Damme'],
                                        ],
                                        'tablet' => [
                                            'en_US' => ['Claude'],

                                        ],
                                    ],
                                    'properties' => [
                                        'reference_data_name' => 'another_reference_data'
                                    ]
                                ]
                            ]
                        ],
                    ]
                );
    }
}
