<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEvaluationResultsByProductsAndCriterionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator\ComputeProductsEnrichmentStatusQuery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ComputeProductsEnrichmentStatusQueryTest extends TestCase
{
    private GetLocalesByChannelQueryInterface|MockObject $getLocalesByChannelQuery;
    private GetEvaluationResultsByProductsAndCriterionQueryInterface|MockObject $getEvaluationResultsByProductsAndCriterionQuery;
    private ComputeProductsEnrichmentStatusQuery $sut;

    protected function setUp(): void
    {
        $this->getLocalesByChannelQuery = $this->createMock(GetLocalesByChannelQueryInterface::class);
        $this->getEvaluationResultsByProductsAndCriterionQuery = $this->createMock(GetEvaluationResultsByProductsAndCriterionQueryInterface::class);
        $this->sut = new ComputeProductsEnrichmentStatusQuery($this->getLocalesByChannelQuery, $this->getEvaluationResultsByProductsAndCriterionQuery);
        $this->getLocalesByChannelQuery->method('getArray')->willReturn([
            'ecommerce' => ['en_US', 'fr_FR'],
            'mobile' => ['en_US'],
        ]);
    }

    private function stubExecute(array $requiredResult, array $nonRequiredResult): void
    {
        $this->getEvaluationResultsByProductsAndCriterionQuery->method('execute')->willReturnCallback(
            function (ProductUuidCollection $ids, CriterionCode $code) use ($requiredResult, $nonRequiredResult): array {
                return match ((string) $code) {
                    EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE => $requiredResult,
                    EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE => $nonRequiredResult,
                    default => [],
                };
            }
        );
    }

    public function test_it_computes_enrichment_status_for_a_list_of_products(): void
    {
        $uuid42 = '54162e35-ff81-48f1-96d5-5febd3f00fd5';
        $uuid56 = 'df470d52-7723-4890-85a0-e79be625e2ed';
        $productIds = ProductUuidCollection::fromStrings([$uuid42, $uuid56]);

        $this->stubExecute(
            [
                $uuid42 => new CriterionEvaluationResult(
                    new ChannelLocaleRateCollection(),
                    new CriterionEvaluationResultStatusCollection(),
                    [
                        'total_number_of_attributes' => [
                            'ecommerce' => ['en_US' => 5, 'fr_FR' => 5],
                            'mobile' => ['en_US' => 2],
                        ],
                        'number_of_improvable_attributes' => [
                            'ecommerce' => ['en_US' => 1, 'fr_FR' => 1],
                            'mobile' => ['en_US' => 0],
                        ],
                    ]
                ),
                $uuid56 => new CriterionEvaluationResult(
                    new ChannelLocaleRateCollection(),
                    new CriterionEvaluationResultStatusCollection(),
                    [
                        'total_number_of_attributes' => [
                            'ecommerce' => ['en_US' => 2, 'fr_FR' => 2],
                            'mobile' => ['en_US' => 5],
                        ],
                        'number_of_improvable_attributes' => [
                            'ecommerce' => ['en_US' => 0, 'fr_FR' => 1],
                            'mobile' => ['en_US' => 2],
                        ],
                    ]
                ),
            ],
            [
                $uuid42 => new CriterionEvaluationResult(
                    new ChannelLocaleRateCollection(),
                    new CriterionEvaluationResultStatusCollection(),
                    [
                        'total_number_of_attributes' => [
                            'ecommerce' => ['en_US' => 5, 'fr_FR' => 5],
                            'mobile' => ['en_US' => 8],
                        ],
                        'number_of_improvable_attributes' => [
                            'ecommerce' => ['en_US' => 0, 'fr_FR' => 1],
                            'mobile' => ['en_US' => 5],
                        ],
                    ]
                ),
                $uuid56 => new CriterionEvaluationResult(
                    new ChannelLocaleRateCollection(),
                    new CriterionEvaluationResultStatusCollection(),
                    [
                        'total_number_of_attributes' => [
                            'ecommerce' => ['en_US' => 8, 'fr_FR' => 8],
                            'mobile' => ['en_US' => 5],
                        ],
                        'number_of_improvable_attributes' => [
                            'ecommerce' => ['en_US' => 0, 'fr_FR' => 2],
                            'mobile' => ['en_US' => 0],
                        ],
                    ]
                ),
            ]
        );

        $this->assertSame([
            $uuid42 => [
                'ecommerce' => ['en_US' => true, 'fr_FR' => true],
                'mobile' => ['en_US' => false],
            ],
            $uuid56 => [
                'ecommerce' => ['en_US' => true, 'fr_FR' => false],
                'mobile' => ['en_US' => true],
            ],
        ], $this->sut->compute($productIds));
    }

    public function test_it_does_not_compute_products_without_evaluations(): void
    {
        $uuid = '54162e35-ff81-48f1-96d5-5febd3f00fd5';
        $productIds = ProductUuidCollection::fromString($uuid);
        $this->stubExecute([], []);

        $this->assertSame([
            $uuid => [
                'ecommerce' => ['en_US' => null, 'fr_FR' => null],
                'mobile' => ['en_US' => null],
            ],
        ], $this->sut->compute($productIds));
    }

    public function test_it_computes_enrichment_status_for_products_with_only_required_attributes(): void
    {
        $uuid = '54162e35-ff81-48f1-96d5-5febd3f00fd5';
        $productIds = ProductUuidCollection::fromString($uuid);

        $this->stubExecute(
            [
                $uuid => new CriterionEvaluationResult(
                    new ChannelLocaleRateCollection(),
                    new CriterionEvaluationResultStatusCollection(),
                    [
                        'total_number_of_attributes' => [
                            'ecommerce' => ['en_US' => 10, 'fr_FR' => 10],
                            'mobile' => ['en_US' => 5],
                        ],
                        'number_of_improvable_attributes' => [
                            'ecommerce' => ['en_US' => 1, 'fr_FR' => 4],
                            'mobile' => ['en_US' => 3],
                        ],
                    ]
                ),
            ],
            [
                $uuid => new CriterionEvaluationResult(
                    new ChannelLocaleRateCollection(),
                    new CriterionEvaluationResultStatusCollection(),
                    []
                ),
            ]
        );

        $this->assertSame([
            $uuid => [
                'ecommerce' => ['en_US' => true, 'fr_FR' => false],
                'mobile' => ['en_US' => false],
            ],
        ], $this->sut->compute($productIds));
    }

    public function test_it_computes_enrichment_status_for_products_without_required_attributes(): void
    {
        $uuid = '54162e35-ff81-48f1-96d5-5febd3f00fd5';
        $productIds = ProductUuidCollection::fromString($uuid);

        $this->stubExecute(
            [
                $uuid => new CriterionEvaluationResult(
                    new ChannelLocaleRateCollection(),
                    new CriterionEvaluationResultStatusCollection(),
                    []
                ),
            ],
            [
                $uuid => new CriterionEvaluationResult(
                    new ChannelLocaleRateCollection(),
                    new CriterionEvaluationResultStatusCollection(),
                    [
                        'total_number_of_attributes' => [
                            'ecommerce' => ['en_US' => 10, 'fr_FR' => 10],
                            'mobile' => ['en_US' => 5],
                        ],
                        'number_of_improvable_attributes' => [
                            'ecommerce' => ['en_US' => 1, 'fr_FR' => 4],
                            'mobile' => ['en_US' => 3],
                        ],
                    ]
                ),
            ]
        );

        $this->assertSame([
            $uuid => [
                'ecommerce' => ['en_US' => true, 'fr_FR' => false],
                'mobile' => ['en_US' => false],
            ],
        ], $this->sut->compute($productIds));
    }
}
