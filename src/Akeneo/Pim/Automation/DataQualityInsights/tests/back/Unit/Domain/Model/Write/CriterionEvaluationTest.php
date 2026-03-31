<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionApplicability;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class CriterionEvaluationTest extends TestCase
{
    private CriterionEvaluation $sut;

    protected function setUp(): void
    {
        $this->sut = new CriterionEvaluation(new CriterionCode('test'), ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')), CriterionEvaluationStatus::pending());
    }

    public function test_it_starts_and_ends_an_evaluation(): void
    {
        $this->assertEquals(CriterionEvaluationStatus::pending(), $this->sut->getStatus());
        $this->sut->start();
        $this->assertEquals(CriterionEvaluationStatus::inProgress(), $this->sut->getStatus());
        $this->assertNull($this->sut->getEvaluatedAt());
        $result = new CriterionEvaluationResult();
        $this->sut->end($result);
        $this->assertEquals(CriterionEvaluationStatus::done(), $this->sut->getStatus());
        $this->assertSame($result, $this->sut->getResult());
        $this->assertNotNull($this->sut->getEvaluatedAt());
    }

    public function test_it_changes_it_status_to_done_if_it_is_not_applicable(): void
    {
        $result = new CriterionEvaluationResult();
        $this->sut->applicabilityEvaluated(new CriterionApplicability($result, false));
        $this->assertEquals(CriterionEvaluationStatus::done(), $this->sut->getStatus());
        $this->assertSame($result, $this->sut->getResult());
        $this->assertNotNull($this->sut->getEvaluatedAt());
    }

    public function test_it_changes_it_status_to_pending_if_it_is_applicable(): void
    {
        $result = new CriterionEvaluationResult();
        $this->sut->applicabilityEvaluated(new CriterionApplicability($result, true));
        $this->assertEquals(CriterionEvaluationStatus::pending(), $this->sut->getStatus());
        $this->assertSame($result, $this->sut->getResult());
        $this->assertNull($this->sut->getEvaluatedAt());
    }
}
