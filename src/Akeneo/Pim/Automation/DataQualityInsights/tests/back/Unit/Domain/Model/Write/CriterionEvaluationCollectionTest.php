<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class CriterionEvaluationCollectionTest extends TestCase
{
    private CriterionEvaluationCollection $sut;

    protected function setUp(): void
    {
        $this->sut = new CriterionEvaluationCollection();
    }

    public function test_it_is_iterable(): void
    {
        $this->assertInstanceOf(\IteratorAggregate::class, $this->sut);
    }

    public function test_it_is_countable(): void
    {
        $this->assertInstanceOf(\Countable::class, $this->sut);
    }

    public function test_it_adds_criterion_evaluations(): void
    {
        $this->assertSame(0, $this->sut->count());
        $criterionEvaluation1 = new Write\CriterionEvaluation(new CriterionCode('completeness'), ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')), CriterionEvaluationStatus::pending());
        $criterionEvaluation2 = new Write\CriterionEvaluation(new CriterionCode('completion'), ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')), CriterionEvaluationStatus::pending());
        $this->sut->add($criterionEvaluation1)->add($criterionEvaluation2);
        $this->assertSame(2, $this->sut->count());
    }
}
