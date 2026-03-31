<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Attributes\InMemoryAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Channels\InMemoryChannels;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\CriterionEvaluationResultData\TransformCommonCriterionResultDataIds;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\CriterionEvaluationResultData\TransformCompletenessResultDataIds;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\CriterionEvaluationResultTransformationFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Locales\InMemoryLocales;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformChannelLocaleDataIds;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultCodes;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultIds;
use PHPUnit\Framework\TestCase;

class TransformCriterionEvaluationResultIdsTest extends TestCase
{
    private TransformCriterionEvaluationResultIds $sut;

    protected function setUp(): void
    {
        $attributes = new InMemoryAttributes([
        'name' => 12,
        'description' => 34,
        ]);
        $channels = new InMemoryChannels([
        'ecommerce' => 1,
        'mobile' => 2,
        ]);
        $locales = new InMemoryLocales([
        'en_US' => 58,
        'fr_FR' => 90,
        ]);
        $transformChannelLocaleDataIds = new TransformChannelLocaleDataIds($channels, $locales);
        $transformCommonCriterionResultData = new TransformCommonCriterionResultDataIds($transformChannelLocaleDataIds, $attributes);
        $transformCompletenessResultData = new TransformCompletenessResultDataIds($transformChannelLocaleDataIds);
        $this->sut = new TransformCriterionEvaluationResultIds($transformChannelLocaleDataIds, $transformCommonCriterionResultData, $transformCompletenessResultData);
    }

    public function test_it_transforms_a_common_criterion_evaluation_result_from_ids_to_codes(): void
    {
        $criterionEvaluationResultIds = $this->getCommonCriterionResultIds();
        $this->assertEquals($this->getCommonCriterionResultCodes(), $this->sut->transformToCodes(new CriterionCode('enrichment_image'), $criterionEvaluationResultIds));
    }

    public function test_it_transforms_a_completeness_criterion_evaluation_result_from_ids_to_codes(): void
    {
        $criterionEvaluationResultIds = $this->getCompletenessCriterionResultIds();
        $this->assertEquals($this->getCompletenessCriterionResultCodes(), $this->sut->transformToCodes(new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE), $criterionEvaluationResultIds));
    }

    public function test_it_transforms_a_deprecated_completeness_criterion_evaluation_result_from_ids_to_codes(): void
    {
        $criterionEvaluationResultIds = $this->getDeprecatedCompletenessCriterionResultIds();
        $this->assertEquals($this->getCompletenessCriterionResultCodes(), $this->sut->transformToCodes(new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE), $criterionEvaluationResultIds));
    }

    public function test_it_throws_an_exception_if_the_evaluation_result_has_an_unknown_property(): void
    {
        $invalidEvaluationResult = [
                    TransformCriterionEvaluationResultCodes::PROPERTIES_ID['rates'] => [
                        1 => [
                            58 => 25,
                            90 => 75,
                        ],
                    ],
                    999 => [
                        1 => [
                            58 => 25,
                            90 => 75,
                        ],
                    ],
                ];
        $this->expectException(CriterionEvaluationResultTransformationFailedException::class);
        $this->sut->transformToCodes(new CriterionCode('enrichment_image'), $invalidEvaluationResult);
    }

    public function test_it_throws_an_exception_if_the_evaluation_result_has_an_unknown_status(): void
    {
        $invalidEvaluationResult = [
                    TransformCriterionEvaluationResultCodes::PROPERTIES_ID['rates'] => [
                        1 => [
                            58 => 25,
                            90 => 75,
                        ],
                    ],
                    TransformCriterionEvaluationResultCodes::PROPERTIES_ID['status'] => [
                        1 => [
                            58 => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::DONE],
                            90 => 123456,
                        ],
                    ],
                ];
        $this->expectException(CriterionEvaluationResultTransformationFailedException::class);
        $this->sut->transformToCodes(new CriterionCode('enrichment_image'), $invalidEvaluationResult);
    }

    private function getCommonCriterionResultIds(): array
    {
        return [
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['data'] => [
                TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['attributes_with_rates'] => [
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
                TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['total_number_of_attributes'] => [
                    1 => [
                        58 => 4,
                        90 => 5,
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

    private function getCommonCriterionResultCodes(): array
    {
        return [
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
    }

    private function getCompletenessCriterionResultIds(): array
    {
        return [
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['data'] => [
                TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['total_number_of_attributes'] => [
                    1 => [
                        58 => 4,
                        90 => 5,
                    ],
                ],
                TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['number_of_improvable_attributes'] => [
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
                    90 => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::NOT_APPLICABLE],
                ],
            ],
        ];
    }

    private function getCompletenessCriterionResultCodes(): array
    {
        return [
            'data' => [
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
                    'fr_FR' => CriterionEvaluationResultStatus::NOT_APPLICABLE,
                ],
            ],
        ];
    }

    private function getDeprecatedCompletenessCriterionResultIds(): array
    {
        return [
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['data'] => [
                TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['attributes_with_rates'] => [
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
                TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['total_number_of_attributes'] => [
                    1 => [
                        58 => 4,
                        90 => 5,
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
                    90 => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::NOT_APPLICABLE],
                ],
            ],
        ];
    }
}
