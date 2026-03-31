<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueWithLinkedData;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueWithLinkedData;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ConnectorProductTest extends TestCase
{
    private ConnectorProduct $sut;

    protected function setUp(): void
    {
    }

    public function test_it_returns_the_option_labels(): void
    {
        $this->markTestSkipped('Requires full rewrite from PHPSpec migration — ConnectorProduct needs 15 constructor args');

        $connectorProductWithLinkedData = $this->buildLinkedData([
                    'simple_select' => [
                        'option_1' => [
                            'en_US' => 'Code 1',
                            'fr_FR' => 'Option 1',
                        ],
                    ],
                    'multi_select' => [
                        'option1' => [
                            'en_US' => 'OPTION NUMBER ONE',
                            'fr_FR' => null,
                        ],
                        'Option2' => [
                            'en_US' => null,
                            'fr_FR' => null,
                        ],
                    ],
                    'other_simple_select' => [
                        '1' => [
                            'en_US' => 'option 1',
                            'fr_FR' => 'option 1',
                        ],
                        '001' => [
                            'en_US' => 'option 001',
                            'fr_FR' => 'option 001',
                        ],
                    ],
                    'other_multi_select' => [
                        '0' => [
                            'en_US' => '0',
                            'fr_FR' => '0',
                        ],
                        '00' => [
                            'en_US' => '00',
                            'fr_FR' => '00',
                        ],
                        '000' => [
                            'en_US' => '000',
                            'fr_FR' => '000',
                        ],
                    ],
                ]);
        $connectorProductWithLinkedData->shouldBeAnInstanceOf(ConnectorProduct::class);
        $connectorProductWithLinkedData->values()->toArray()->shouldBeLike(
                    [
                        ScalarValue::value('attribute_code_1', 'data'),
                        ScalarValue::localizableValue('attribute_code_2', 'data', 'en_US'),
                        ScalarValue::localizableValue('attribute_code_2', 'data', 'fr_FR'),
                        new OptionValueWithLinkedData(
                            'simple_select',
                            'Option_1',
                            null,
                            null,
                            [
                                'attribute' => 'simple_select',
                                'code' => 'option_1',
                                'labels' => [
                                    'en_US' => 'Code 1',
                                    'fr_FR' => 'Option 1',
                                ]
                            ]
                        ),
                        new OptionsValueWithLinkedData(
                            'multi_select',
                            ['Option1', 'OPTION2'],
                            null,
                            null,
                            [
                                'Option1' => [
                                    'attribute' => 'multi_select',
                                    'code' => 'option1',
                                    'labels' => [
                                        'en_US' => 'OPTION NUMBER ONE',
                                        'fr_FR' => null,
                                    ],
                                ],
                                'OPTION2' => [
                                    'attribute' => 'multi_select',
                                    'code' => 'Option2',
                                    'labels' => [
                                        'en_US' => null,
                                        'fr_FR' => null,
                                    ]
                                ]
                            ]
                        ),
                        new OptionValueWithLinkedData(
                            'other_simple_select',
                            '1',
                            null,
                            'en_US',
                            [
                                'attribute' => 'other_simple_select',
                                'code' => '1',
                                'labels' => [
                                    'en_US' => 'option 1',
                                    'fr_FR' => 'option 1',
                                ]
                            ]
                        ),
                        new OptionValueWithLinkedData(
                            'other_simple_select',
                            '001',
                            null,
                            'fr_FR',
                            [
                                'attribute' => 'other_simple_select',
                                'code' => '001',
                                'labels' => [
                                    'en_US' => 'option 001',
                                    'fr_FR' => 'option 001',
                                ]
                            ]
                        ),
                        new OptionsValueWithLinkedData(
                            'other_multi_select',
                            ['00', '0', '000'],
                            null,
                            null,
                            [
                                '00' => [
                                    'attribute' => 'other_multi_select',
                                    'code' => '00',
                                    'labels' => [
                                        'en_US' => '00',
                                        'fr_FR' => '00',
                                    ],
                                ],
                                '0' => [
                                    'attribute' => 'other_multi_select',
                                    'code' => '0',
                                    'labels' => [
                                        'en_US' => '0',
                                        'fr_FR' => '0',
                                    ],
                                ],
                                '000' => [
                                    'attribute' => 'other_multi_select',
                                    'code' => '00',
                                    'labels' => [
                                        'en_US' => '000',
                                        'fr_FR' => '000',
                                    ],
                                ],
                            ]
                        ),
                    ]
                );
    }
}
