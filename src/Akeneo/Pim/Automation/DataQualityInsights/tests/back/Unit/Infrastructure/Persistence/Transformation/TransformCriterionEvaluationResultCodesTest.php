<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Attributes\SqlAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Channels\ChannelsInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\CriterionEvaluationResultTransformationFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Locales\LocalesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultCodes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransformCriterionEvaluationResultCodesTest extends TestCase
{
    private SqlAttributes|MockObject $attributes;
    private ChannelsInterface|MockObject $channels;
    private LocalesInterface|MockObject $locales;
    private TransformCriterionEvaluationResultCodes $sut;

    protected function setUp(): void
    {
        $this->attributes = $this->createMock(SqlAttributes::class);
        $this->channels = $this->createMock(ChannelsInterface::class);
        $this->locales = $this->createMock(LocalesInterface::class);
        $this->sut = new TransformCriterionEvaluationResultCodes($this->attributes, $this->channels, $this->locales);
        $this->attributes->method('getIdsByCodes')->with(['name', 'description'])->willReturn(['name' => 12, 'description' => 34]);
        $this->attributes->method('getIdsByCodes')->with(['description'])->willReturn(['description' => 34]);
        $this->channels->method('getIdByCode')->with('ecommerce')->willReturn(1);
        $this->locales->method('getIdByCode')->with('en_US')->willReturn(58);
        $this->locales->method('getIdByCode')->with('fr_FR')->willReturn(90);
    }

    public function test_it_transforms_a_criterion_evaluation_result_from_codes_to_ids(): void
    {
        $criterionEvaluationResultCodes = [
                    'data' => [
                        'attributes_with_rates' => [
                            'ecommerce' => [
                                'en_US' => [
                                    'name' => 50,
                                    'description' => 0,
                                ],
                                'fr_FR' => [
                                    'description' => 20,
                                ],
                            ],
                        ],
                        'total_number_of_attributes' => [
                            'ecommerce' => [
                                'en_US' => 4,
                                'fr_FR' => 5,
                            ],
                        ],
                        'number_of_improvable_attributes' => [
                            'ecommerce' => [
                                'en_US' => 2,
                                'fr_FR' => 1,
                            ],
                        ],
                    ],
                    'rates' => [
                        'ecommerce' => [
                            'en_US' => 25,
                            'fr_FR' => 75,
                        ],
                    ],
                    'status' => [
                        'ecommerce' => [
                            'en_US' => CriterionEvaluationResultStatus::DONE,
                            'fr_FR' => CriterionEvaluationResultStatus::IN_PROGRESS,
                        ],
                    ],
                ];
        $this->assertEquals($this->getExpectedResult(), $this->sut->transformToIds($criterionEvaluationResultCodes));
    }

    public function test_it_throws_an_exception_if_the_evaluation_result_has_an_unknown_property(): void
    {
        $invalidEvaluationResult = [
                    'rates' => [
                        'ecommerce' => [
                            'en_US' => 25,
                            'fr_FR' => 75,
                        ],
                    ],
                    'foo' => [
                        'ecommerce' => [
                            'en_US' => 'done',
                            'fr_FR' => 'in_progress',
                        ],
                    ],
                ];
        $this->expectException(CriterionEvaluationResultTransformationFailedException::class);
        $this->sut->transformToIds($invalidEvaluationResult);
    }

    public function test_it_throws_an_exception_if_the_evaluation_result_has_an_unknown_status(): void
    {
        $invalidEvaluationResult = [
                    'data' => [
                        'attributes_with_rates' => [
                            'ecommerce' => [
                                'en_US' => [
                                    'name' => 50,
                                    'description' => 0,
                                ],
                            ],
                        ],
                    ],
                    'rates' => [
                        'ecommerce' => [
                            'en_US' => 25,
                            'fr_FR' => null,
                        ],
                    ],
                    'status' => [
                        'ecommerce' => [
                            'en_US' => CriterionEvaluationResultStatus::DONE,
                            'fr_FR' => 'foo',
                        ],
                    ],
                ];
        $this->expectException(CriterionEvaluationResultTransformationFailedException::class);
        $this->sut->transformToIds($invalidEvaluationResult);
    }

    public function test_it_throws_an_exception_if_the_evaluation_result_has_an_unknown_data_type(): void
    {
        $invalidEvaluationResult = [
                    'data' => [
                        'attributes_with_rates' => [
                            'ecommerce' => [
                                'en_US' => [
                                    'name' => 50,
                                    'description' => 0,
                                ],
                            ],
                        ],
                        'foo' => [
                            'ecommerce' => [
                                'en_US' => 4,
                                'fr_FR' => 5,
                            ],
                        ],
                    ],
                    'rates' => [],
                    'status' => [],
                ];
        $this->expectException(CriterionEvaluationResultTransformationFailedException::class);
        $this->sut->transformToIds($invalidEvaluationResult);
    }

    public function test_it_removes_unknown_channels(): void
    {
        $this->channels->method('getIdByCode')->with('foo')->willReturn(null);
        $criterionEvaluationResultCodes = [
                    'data' => [
                        'attributes_with_rates' => [
                            'ecommerce' => [
                                'en_US' => [
                                    'name' => 50,
                                    'description' => 0,
                                ],
                                'fr_FR' => [
                                    'description' => 20,
                                ],
                            ],
                            'foo' => [
                                'en_US' => [
                                    'name' => 50,
                                ],
                            ],
                        ],
                        'total_number_of_attributes' => [
                            'ecommerce' => [
                                'en_US' => 4,
                                'fr_FR' => 5,
                            ],
                            'foo' => [
                                'en_US' => 3,
                            ],
                        ],
                        'number_of_improvable_attributes' => [
                            'ecommerce' => [
                                'en_US' => 2,
                                'fr_FR' => 1,
                            ],
                        ],
                    ],
                    'rates' => [
                        'ecommerce' => [
                            'en_US' => 25,
                            'fr_FR' => 75,
                        ],
                        'foo' => [
                            'en_US' => 56,
                        ],
                    ],
                    'status' => [
                        'ecommerce' => [
                            'en_US' => CriterionEvaluationResultStatus::DONE,
                            'fr_FR' => CriterionEvaluationResultStatus::IN_PROGRESS,
                        ],
                        'foo' => [
                            'en_US' => CriterionEvaluationResultStatus::DONE,
                        ],
                    ],
                ];
        $this->assertEquals($this->getExpectedResult(), $this->sut->transformToIds($criterionEvaluationResultCodes));
    }

    public function test_it_removes_unknown_locales(): void
    {
        $this->locales->method('getIdByCode')->with('fo_FO')->willReturn(null);
        $criterionEvaluationResultCodes = [
                    'data' => [
                        'attributes_with_rates' => [
                            'ecommerce' => [
                                'en_US' => [
                                    'name' => 50,
                                    'description' => 0,
                                ],
                                'fr_FR' => [
                                    'description' => 20,
                                ],
                                'fo_FO' => [
                                    'name' => 80,
                                ],
                            ],
                        ],
                        'total_number_of_attributes' => [
                            'ecommerce' => [
                                'en_US' => 4,
                                'fr_FR' => 5,
                                'fo_FO' => 3,
                            ],
                        ],
                        'number_of_improvable_attributes' => [
                            'ecommerce' => [
                                'en_US' => 2,
                                'fr_FR' => 1,
                                'fo_FO' => 1,
                            ],
                        ],
                    ],
                    'rates' => [
                        'ecommerce' => [
                            'en_US' => 25,
                            'fr_FR' => 75,
                            'fo_FO' => 89,
                        ],
                    ],
                    'status' => [
                        'ecommerce' => [
                            'en_US' => CriterionEvaluationResultStatus::DONE,
                            'fr_FR' => CriterionEvaluationResultStatus::IN_PROGRESS,
                            'fo_FO' => CriterionEvaluationResultStatus::DONE,
                        ],
                    ],
                ];
        $this->assertEquals($this->getExpectedResult(), $this->sut->transformToIds($criterionEvaluationResultCodes));
    }

    private function getExpectedResult(): array
    {
        return [
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['data'] => [
                1 => [
                    1 => [
                        58 => [
                            12 => 50,
                            34 => 0,
                        ],
                        90 => [
                            34 => 20,
                        ],
                    ],
                ],
                2 => [
                    1 => [
                        58 => 4,
                        90 => 5,
                    ],
                ],
                3 => [
                    1 => [
                        58 => 2,
                        90 => 1,
                    ],
                ],
            ],
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['rates'] => [
                1 => [
                    58 => 25,
                    90 => 75,
                ],
            ],
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['status'] => [
                1 => [
                    58 => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::DONE],
                    90 => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::IN_PROGRESS],
                ],
            ],
        ];
    }
}
