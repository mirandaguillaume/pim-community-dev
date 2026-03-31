<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\CriterionEvaluationResultData;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Channels\InMemoryChannels;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\CriterionEvaluationResultData\TransformCompletenessResultDataIds;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Locales\InMemoryLocales;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformChannelLocaleDataIds;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultCodes;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformCompletenessResultDataIdsTest extends TestCase
{
    private TransformCompletenessResultDataIds $sut;

    protected function setUp(): void
    {
        $channels = new InMemoryChannels([
            'ecommerce' => 1,
            'mobile' => 2,
        ]);
        $locales = new InMemoryLocales([
            'en_US' => 58,
            'fr_FR' => 90,
        ]);
        $this->sut = new TransformCompletenessResultDataIds(new TransformChannelLocaleDataIds($channels, $locales));
    }

    public function test_it_transforms_a_completeness_criterion_result_from_ids_to_codes(): void
    {
        $dataToTransform = [
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
                ];
        $expectedTransformedData = [
                    'number_of_improvable_attributes' => [
                        'ecommerce' => [
                            'en_US' => 2,
                            'fr_FR' => 1,
                        ],
                    ],
                    'total_number_of_attributes' => [
                        'ecommerce' => [
                            'en_US' => 4,
                            'fr_FR' => 5,
                        ],
                    ],
                ];
        $this->assertSame($expectedTransformedData, $this->sut->transformToCodes($dataToTransform));
    }

    public function test_it_transforms_a_deprecated_completeness_criterion_result_from_ids_to_codes(): void
    {
        $dataToTransform = [
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
                ];
        $expectedTransformedData = [
                    'number_of_improvable_attributes' => [
                        'ecommerce' => [
                            'en_US' => 2,
                            'fr_FR' => 1,
                        ],
                    ],
                    'total_number_of_attributes' => [
                        'ecommerce' => [
                            'en_US' => 4,
                            'fr_FR' => 5,
                        ],
                    ],
                ];
        $this->assertSame($expectedTransformedData, $this->sut->transformToCodes($dataToTransform));
    }
}
