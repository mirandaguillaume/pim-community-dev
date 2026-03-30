<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaByFeatureRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateCriteriaEvaluationsTest extends TestCase
{
    private CreateCriteriaEvaluations $sut;

    protected function setUp(): void
    {
    }

    public function test_it_creates_all_criteria(): void
    {
        $criteriaRegistry = $this->createMock(CriteriaByFeatureRegistry::class);
        $criterionEvaluationRepository = $this->createMock(CriterionEvaluationRepositoryInterface::class);

        $this->sut = new CreateCriteriaEvaluations($criteriaRegistry, $criterionEvaluationRepository);
        $productUuids = ProductUuidCollection::fromStrings(['df470d52-7723-4890-85a0-e79be625e2ed']);
        $criteriaRegistry->method('getAllCriterionCodes')->willReturn([new CriterionCode('criterion1'), new CriterionCode('criterion2')]);
        $criterionEvaluationRepository->expects($this->once())->method('create')->with($this->callback(fn (CriterionEvaluationCollection $collection) => $collection->count() === 2));
        $this->sut->createAll($productUuids);
    }
}
