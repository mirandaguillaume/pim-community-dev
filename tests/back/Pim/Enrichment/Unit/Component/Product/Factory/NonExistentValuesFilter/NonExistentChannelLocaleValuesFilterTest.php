<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Channel\API\Query\GetCaseSensitiveChannelCodeInterface;
use Akeneo\Channel\API\Query\GetCaseSensitiveLocaleCodeInterface;
use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentChannelLocaleValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NonExistentChannelLocaleValuesFilterTest extends TestCase
{
    private ChannelExistsWithLocaleInterface|MockObject $channelsLocales;
    private GetCaseSensitiveLocaleCodeInterface|MockObject $getCaseSensitiveLocaleCode;
    private GetCaseSensitiveChannelCodeInterface|MockObject $getCaseSensitiveChannelCode;
    private GetAttributes|MockObject $getAttributes;
    private NonExistentChannelLocaleValuesFilter $sut;

    protected function setUp(): void
    {
        $this->channelsLocales = $this->createMock(ChannelExistsWithLocaleInterface::class);
        $this->getCaseSensitiveLocaleCode = $this->createMock(GetCaseSensitiveLocaleCodeInterface::class);
        $this->getCaseSensitiveChannelCode = $this->createMock(GetCaseSensitiveChannelCodeInterface::class);
        $this->getAttributes = $this->createMock(GetAttributes::class);
        $this->sut = new NonExistentChannelLocaleValuesFilter($this->channelsLocales, $this->getCaseSensitiveLocaleCode, $this->getCaseSensitiveChannelCode, $this->getAttributes);
        $this->getCaseSensitiveLocaleCode->method('forLocaleCode')->willReturnCallback(fn (string $code) => $code);
        $this->getCaseSensitiveChannelCode->method('forChannelCode')->willReturnCallback(fn (string $code) => $code);
    }

    public function test_it_filters_values_of_non_existing_channels(): void
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType([
                    AttributeTypes::OPTION_SIMPLE_SELECT => [
                        'a_select' => [
                            [
                                'identifier' => 'product_A',
                                'values' => [
                                    '<all_channels>' => [
                                        '<all_locales>' => 'option_A'
                                    ],
                                ]
                            ],
                            [
                                'identifier' => 'product_B',
                                'values' => [
                                    'ecommerce' => [
                                        'en_US' => 'option_B'
                                    ],
                                    'foo' => [
                                        'en_US' => 'option_B'
                                    ],
                                ]
                            ]
                        ],
                        'another_select' => [
                            [
                                'identifier' => 'product_B',
                                'values' => [
                                    'foo' => [
                                        'en_US' => 'option_B'
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
                                    'foo' => [
                                        '<all_locales>' => 'plop'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);
        $this->channelsLocales->method('doesChannelExist')->willReturnCallback(fn (string $channel) => match ($channel) {
            'ecommerce' => true,
            'foo' => false,
            default => false,
        });
        $this->channelsLocales->method('isLocaleBoundToChannel')->willReturn(true);
        $attributes = $this->getAttributes();
        $this->getAttributes->method('forCode')->willReturnCallback(fn (string $code) => $attributes[$code] ?? null);
        $filteredRawValues = $this->sut->filter($ongoingFilteredRawValues)->filteredRawValuesCollectionIndexedByType();
        $this->assertEquals([
                    AttributeTypes::OPTION_SIMPLE_SELECT => [
                        'a_select' => [
                            [
                                'identifier' => 'product_A',
                                'values' => [
                                    '<all_channels>' => [
                                        '<all_locales>' => 'option_A'
                                    ],
                                ],
                            ],
                            [
                                'identifier' => 'product_B',
                                'values' => [
                                    'ecommerce' => [
                                        'en_US' => 'option_B'
                                    ],
                                ],
                            ],
                        ],
                        'another_select' => [
                            [
                                'identifier' => 'product_B',
                                'values' => [],
                            ],
                        ],
                    ],
                    AttributeTypes::TEXTAREA => [
                        'a_description' => [
                            [
                                'identifier' => 'product_B',
                                'values' => [],
                            ],
                        ],
                    ],
                ], $filteredRawValues);
    }

    public function test_it_filters_values_of_not_activated_locales(): void
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType([
                    AttributeTypes::OPTION_SIMPLE_SELECT => [
                        'a_select' => [
                            [
                                'identifier' => 'product_A',
                                'values' => [
                                    'ecommerce' => [
                                        'en_US' => 'option_A',
                                        'en_CA' => 'option_A',
                                        'fr_FR' => 'option_A',
                                    ],
                                ],
                            ],
                            [
                                'identifier' => 'product_B',
                                'values' => [
                                    'ecommerce' => [
                                        'en_CA' => 'option_B'
                                    ],
                                ],
                            ],
                        ],
                        'another_select' => [
                            [
                                'identifier' => 'product_B',
                                'values' => [
                                    'ecommerce' => [
                                        '<all_locales>' => 'option_B'
                                    ],
                                ],
                            ],
                        ],
                    ],
                    AttributeTypes::TEXTAREA => [
                        'a_description' => [
                            [
                                'identifier' => 'product_B',
                                'values' => [
                                    '<all_channels>' => [
                                        'en_US' => 'plop',
                                        'fr_FR' => 'hop',
                                        'en_CA' => 'bar'
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]);
        $this->channelsLocales->method('doesChannelExist')->willReturn(true);
        $this->channelsLocales->method('isLocaleBoundToChannel')->willReturnCallback(fn (string $locale, string $channel) => match ("$locale|$channel") {
            'en_US|ecommerce' => true,
            'en_CA|ecommerce' => false,
            'fr_FR|ecommerce' => false,
            default => false,
        });
        $this->channelsLocales->method('isLocaleActive')->willReturnCallback(fn (string $locale) => match ($locale) {
            'en_US' => true,
            'en_CA' => false,
            'fr_FR' => true,
            default => false,
        });
        $attributes = $this->getAttributes();
        $this->getAttributes->method('forCode')->willReturnCallback(fn (string $code) => $attributes[$code] ?? null);
        $filteredRawValues = $this->sut->filter($ongoingFilteredRawValues)->filteredRawValuesCollectionIndexedByType();
        $this->assertEquals([
                    AttributeTypes::OPTION_SIMPLE_SELECT => [
                        'a_select' => [
                            [
                                'identifier' => 'product_A',
                                'values' => [
                                    'ecommerce' => [
                                        'en_US' => 'option_A',
                                    ],
                                ]
                            ],
                            [
                                'identifier' => 'product_B',
                                'values' => [],
                            ],
                        ],
                        'another_select' => [
                            [
                                'identifier' => 'product_B',
                                'values' => [
                                    'ecommerce' => [
                                        '<all_locales>' => 'option_B'
                                    ],
                                ],
                            ],
                        ],
                    ],
                    AttributeTypes::TEXTAREA => [
                        'a_description' => [
                            [
                                'identifier' => 'product_B',
                                'values' => [
                                    '<all_channels>' => [
                                        'en_US' => 'plop',
                                        'fr_FR' => 'hop',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ], $filteredRawValues);
    }

    private function getAttributes(): array
    {
            return [
                'a_select' => new Attribute(
                    'a_select',
                    AttributeTypes::OPTION_SIMPLE_SELECT,
                    [],
                    false,
                    false,
                    null,
                    null,
                    null,
                    AttributeTypes::BACKEND_TYPE_OPTION,
                    []
                ),
                'another_select' => new Attribute(
                    'another_select',
                    AttributeTypes::OPTION_SIMPLE_SELECT,
                    [],
                    false,
                    false,
                    null,
                    null,
                    null,
                    AttributeTypes::BACKEND_TYPE_OPTION,
                    []
                ),
                'a_description' => new Attribute(
                    'a_description',
                    AttributeTypes::TEXTAREA,
                    [],
                    false,
                    false,
                    null,
                    null,
                    null,
                    AttributeTypes::BACKEND_TYPE_TEXTAREA,
                    []
                ),
                'a_locale_specific_select' => new Attribute(
                    'a_locale_specific_select',
                    AttributeTypes::OPTION_SIMPLE_SELECT,
                    [],
                    true,
                    false,
                    null,
                    null,
                    null,
                    AttributeTypes::BACKEND_TYPE_OPTION,
                    ['fr_FR']
                ),
            ];
        }
}
