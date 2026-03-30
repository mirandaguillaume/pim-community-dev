<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use PHPUnit\Framework\TestCase;

class EmptyValuesCleanerTest extends TestCase
{
    private EmptyValuesCleaner $sut;

    protected function setUp(): void
    {
        $this->sut = new EmptyValuesCleaner();
    }

    public function test_it_has_a_type(): void
    {
        $this->assertInstanceOf(EmptyValuesCleaner::class, $this->sut);
    }

    public function test_it_cleans_empty_or_null_values(): void
    {
        $rawValues = [
                    'productA' => [
                        'color' => [
                            'ecommerce' => [
                                '<all_locales>' => '',
                            ],
                            'tablet' => [
                                '<all_locales>' => 'red',
                            ],
                        ],
                        'colors' => [
                            'ecommerce' => [
                                '<all_locales>' => [],
                            ],
                            'tablet' => [
                                '<all_locales>' => ['blue'],
                            ],
                        ],
                        'a_metric' => [
                            '<all_channels>' => [
                                'en_US' => [
                                    'amount' => 22,
                                    'unit' => 'KILOGRAM',
                                    'base_data' => 22000,
                                    'base_unit' => 'GRAM',
                                    'family' => 'Weight',
                                ],
                                'fr_FR' => [
                                    'amount' => null,
                                    'unit' => 'KILOGRAM',
                                    'base_data' => null,
                                    'base_unit' => 'GRAM',
                                    'family' => 'Weight',
                                ],
                            ],
                        ],
                    ],
                    'productB' => [
                        'an_attribute' => [
                            '<all_channels>' => [
                                'en_US' => null,
                                'fr_FR' => 'a_value',
                                'be_BE' => '',
                            ],
                        ],
                    ],
                    'productC' => [
                        'an_attribute' => [
                            '<all_channels>' => [
                                'en_US' => null,
                            ],
                        ],
                    ],
                ];
        $expected = [
                    'productA' => [
                        'color' => [
                            'tablet' => [
                                '<all_locales>' => 'red',
                            ],
                        ],
                        'colors' => [
                            'tablet' => [
                                '<all_locales>' => ['blue'],
                            ],
                        ],
                        'a_metric' => [
                            '<all_channels>' => [
                                'en_US' => [
                                    'amount' => 22,
                                    'unit' => 'KILOGRAM',
                                    'base_data' => 22000,
                                    'base_unit' => 'GRAM',
                                    'family' => 'Weight',
                                ],
                            ],
                        ],
                    ],
                    'productB' => [
                        'an_attribute' => [
                            '<all_channels>' => [
                                'fr_FR' => 'a_value',
                            ],
                        ],
                    ],
                ];
        $this->assertEquals($expected, $this->sut->cleanAllValues($rawValues));
    }
}
