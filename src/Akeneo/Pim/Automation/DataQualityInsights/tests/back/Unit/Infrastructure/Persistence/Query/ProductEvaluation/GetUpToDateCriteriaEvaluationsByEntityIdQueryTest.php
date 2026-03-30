<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByEntityIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetUpToDateCriteriaEvaluationsByEntityIdQuery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class GetUpToDateCriteriaEvaluationsByEntityIdQueryTest extends TestCase
{
    private GetCriteriaEvaluationsByEntityIdQueryInterface|MockObject $getCriteriaEvaluationsByProductIdQuery;
    private HasUpToDateEvaluationQueryInterface|MockObject $hasUpToDateEvaluationQuery;
    private GetUpToDateCriteriaEvaluationsByEntityIdQuery $sut;

    protected function setUp(): void
    {
        $this->getCriteriaEvaluationsByProductIdQuery = $this->createMock(GetCriteriaEvaluationsByEntityIdQueryInterface::class);
        $this->hasUpToDateEvaluationQuery = $this->createMock(HasUpToDateEvaluationQueryInterface::class);
        $this->sut = new GetUpToDateCriteriaEvaluationsByEntityIdQuery($this->getCriteriaEvaluationsByProductIdQuery, $this->hasUpToDateEvaluationQuery);
    }

    public function test_it_returns_criteria_evaluations_if_the_evaluation_of_the_product_is_up_to_date(): void
    {
        $productUuid = ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed');
        $this->hasUpToDateEvaluationQuery->method('forEntityId')->with($productUuid)->willReturn(true);
        $criteriaEvaluations = (new Read\CriterionEvaluationCollection())
                    ->add(new Read\CriterionEvaluation(
                        new CriterionCode('spelling'),
                        ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed'),
                        new \DateTimeImmutable(),
                        CriterionEvaluationStatus::pending(),
                        null
                    ));
        $this->getCriteriaEvaluationsByProductIdQuery->method('execute')->with($productUuid)->willReturn($criteriaEvaluations);
        $this->assertSame($criteriaEvaluations, $this->sut->execute($productUuid));
    }

    public function test_it_returns_empty_criteria_evaluations_if_the_evaluation_of_the_product_is_outdated(): void
    {
        $productUuid = ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed');
        $this->hasUpToDateEvaluationQuery->method('forEntityId')->with($productUuid)->willReturn(false);
        $this->getCriteriaEvaluationsByProductIdQuery->expects($this->never())->method('execute')->with($productUuid);
        $this->assertEquals(new Read\CriterionEvaluationCollection(), $this->sut->execute($productUuid));
    }
}
