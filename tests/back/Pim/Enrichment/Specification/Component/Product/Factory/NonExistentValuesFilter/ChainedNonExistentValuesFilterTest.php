<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentChannelLocaleValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Enrichment\Component\Product\Factory\TransformRawValuesCollections;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChainedNonExistentValuesFilterTest extends TestCase
{
    private NonExistentValuesFilter|MockObject $filter1;
    private NonExistentValuesFilter|MockObject $filter2;
    private NonExistentChannelLocaleValuesFilter|MockObject $nonExistentChannelLocaleValuesFilter;
    private GetAttributes|MockObject $getAttributes;
    private ChainedNonExistentValuesFilter $sut;

    protected function setUp(): void
    {
        $this->filter1 = $this->createMock(NonExistentValuesFilter::class);
        $this->filter2 = $this->createMock(NonExistentValuesFilter::class);
        $this->nonExistentChannelLocaleValuesFilter = $this->createMock(NonExistentChannelLocaleValuesFilter::class);
        $this->getAttributes = $this->createMock(GetAttributes::class);
        $this->sut = new ChainedNonExistentValuesFilter([$this->filter1, $this->filter2],
            $this->nonExistentChannelLocaleValuesFilter,
            new EmptyValuesCleaner(),
            new TransformRawValuesCollections($this->getAttributes));
        $description = new Attribute('description', AttributeTypes::TEXTAREA, [], true, true, null, null, false, 'textarea', []);
        $name = new Attribute('name', AttributeTypes::TEXT, [], true, true, null, null, false, 'text', []);
        $color = new Attribute('color', AttributeTypes::OPTION_SIMPLE_SELECT, [], false, false, null, null, false, 'option', []);
        $this->getAttributes->method('forCodes')->with(['attribute_that_does_not_exists'])->willReturn(['unknown_attribute' => null]);
        $this->getAttributes->method('forCodes')->with(['color'])->willReturn(['color' => $color]);
        $this->getAttributes->method('forCodes')->with(['description'])->willReturn(['description' => $description]);
        $this->getAttributes->method('forCodes')->with(['description', 'name'])->willReturn(['description' => $description, 'name' => $name]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ChainedNonExistentValuesFilterInterface::class, $this->sut);
    }

    public function test_it_filters_raw_value_collection(): void
    {
        $rawValuesCollection = [
                    'productA' => [
                        'description' => ['<all_channels>' => ['<all_locales>' => 'a description']],
                        'name' => ['<all_channels>' => ['<all_locales>' => 'a name']],
                    ],
                ];
        $textareaValues = [AttributeTypes::TEXTAREA => [
                    'description' => [
                        [
                            'identifier' => 'productA',
                            'values' => ['<all_channels>' => ['<all_locales>' => 'a description']],
                            'properties' => [],
                        ],
                    ],
                ]];
        $textValues = [AttributeTypes::TEXT => [
                    'name' => [
                        [
                            'identifier' => 'productA',
                            'values' => ['<all_channels>' => ['<all_locales>' => 'a name']],
                            'properties' => [],
                        ],
                    ],
                ]];
        $nonFilterRawValues = \array_merge($textareaValues, $textValues);
        $ongoingRawValues = new OnGoingFilteredRawValues([], $nonFilterRawValues);
        $ongoingRawValuesAfterFilter1 = new OnGoingFilteredRawValues($textValues, $textareaValues);
        $this->filter1->expects($this->once())->method('filter')->with($ongoingRawValues)->willReturn($ongoingRawValuesAfterFilter1);
        $ongoingRawValuesAfterFilter2 = new OnGoingFilteredRawValues(\array_merge($textareaValues, $textValues), []);
        $this->filter2->expects($this->once())->method('filter')->with($ongoingRawValuesAfterFilter1)->willReturn($ongoingRawValuesAfterFilter2);
        $this->nonExistentChannelLocaleValuesFilter->expects($this->once())->method('filter')->with($ongoingRawValues)->willReturn($ongoingRawValues);
        $this->assertEquals($rawValuesCollection, $this->sut->filterAll($rawValuesCollection));
    }

    public function test_it_filters_empty_values(): void
    {
        $rawValuesCollection = [
                    'productA' => [
                        'description' => [
                            '<all_channels>' => [
                                '<all_locales>' => null
                            ]
                        ]
                    ],
                    '123' => [
                        'description' => [
                            '<all_channels>' => [
                                '<all_locales>' => null
                            ]
                        ]
                    ]
                ];
        $nonFilterRawValues = [
                    AttributeTypes::TEXTAREA => [
                        'description' => [
                            [
                                'identifier' => 'productA',
                                'values' => [
                                    '<all_channels>' => [
                                        '<all_locales>' => null
                                    ]
                                ],
                                'properties' => []
                            ],
                            [
                                'identifier' => '123',
                                'values' => [
                                    '<all_channels>' => [
                                        '<all_locales>' => null
                                    ]
                                ],
                                'properties' => []
                            ]
                        ]
                    ]
                ];
        $ongoingRawValues = new OnGoingFilteredRawValues([], $nonFilterRawValues);
        $this->filter1->method('filter')->with($ongoingRawValues)->willReturn($ongoingRawValues);
        $this->filter2->method('filter')->with($ongoingRawValues)->willReturn($ongoingRawValues);
        $this->nonExistentChannelLocaleValuesFilter->expects($this->once())->method('filter')->with($ongoingRawValues)->willReturn($ongoingRawValues);
        $this->assertEquals(['productA' => [], '123' => []], $this->sut->filterAll($rawValuesCollection));
    }
}
