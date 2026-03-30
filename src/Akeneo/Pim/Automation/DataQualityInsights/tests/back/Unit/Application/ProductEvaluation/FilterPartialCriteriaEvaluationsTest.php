<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaByFeatureRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\FilterPartialCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterPartialCriteriaEvaluationsTest extends TestCase
{
    private CriteriaByFeatureRegistry|MockObject $criteriaRegistry;
    private FilterPartialCriteriaEvaluations $sut;

    protected function setUp(): void
    {
        $this->criteriaRegistry = $this->createMock(CriteriaByFeatureRegistry::class);
        $this->sut = new FilterPartialCriteriaEvaluations($this->criteriaRegistry);
        $this->criteriaRegistry->method('getPartialCriterionCodes')->willReturn([
        new CriterionCode('criterion_partial_A'),
        new CriterionCode('criterion_partial_B'),
        ]);
    }

    public function test_it_filters_criteria_evaluations_for_partial_score(): void
    {
        $criterionEvaluationPartialA = $this->buildCriterionEvaluation('criterion_partial_A');
        $criterionEvaluationPartialB = $this->buildCriterionEvaluation('criterion_partial_B');
        $criterionEvaluationAll = $this->buildCriterionEvaluation('criterion_all');
        $criteriaEvaluations = (new Read\CriterionEvaluationCollection())
                    ->add($criterionEvaluationPartialA)
                    ->add($criterionEvaluationAll)
                    ->add($criterionEvaluationPartialB);
        $expectedCriteriaEvaluations = (new Read\CriterionEvaluationCollection())
                    ->add($criterionEvaluationPartialA)
                    ->add($criterionEvaluationPartialB);
        $this->assertEquals($expectedCriteriaEvaluations, $this->sut->__invoke($criteriaEvaluations));
    }

    private function buildCriterionEvaluation(string $criterionCode): Read\CriterionEvaluation
    {
        return new Read\CriterionEvaluation(
            new CriterionCode($criterionCode),
            ProductUuid::fromString(Uuid::uuid4()->toString()),
            null,
            CriterionEvaluationStatus::pending(),
            null
        );
    }
}
