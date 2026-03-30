<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class CriterionEvaluationCollectionTest extends TestCase
{
    private CriterionEvaluationCollection $sut;

    protected function setUp(): void
    {
        $this->sut = new CriterionEvaluationCollection();
    }

    public function test_it_is_a_criterion_evaluation_collection(): void
    {
        $this->assertInstanceOf(Read\CriterionEvaluationCollection::class, $this->sut);
    }

    public function test_it_gives_a_criterion_evaluation_by_its_code(): void
    {
        $completenessEvaluation = new Read\CriterionEvaluation(
            new CriterionCode('completeness_of_required_attributes'),
            ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending(),
            null
        );
        $spellingEvaluation = new Read\CriterionEvaluation(
            new CriterionCode('consistency_textarea_uppercase_words'),
            ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending(),
            null
        );
        $this->sut->add($completenessEvaluation)
                    ->add($spellingEvaluation);
        $this->assertSame($completenessEvaluation, $this->sut->get(new CriterionCode('completeness_of_required_attributes')));
    }

    public function test_it_gives_the_count_of_the_criteria_evaluations(): void
    {
        $completenessEvaluation = new Read\CriterionEvaluation(
            new CriterionCode('completeness_of_required_attributes'),
            ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending(),
            null
        );
        $spellingEvaluation = new Read\CriterionEvaluation(
            new CriterionCode('consistency_textarea_uppercase_words'),
            ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending(),
            null
        );
        $this->sut->add($completenessEvaluation)
                    ->add($spellingEvaluation);
        $this->assertSame(2, $this->sut->count());
    }

    public function test_it_gives_the_rates_of_a_given_criterion(): void
    {
        $completenessResult = new Read\CriterionEvaluationResult(
            (new ChannelLocaleRateCollection())
                        ->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), new Rate(100)),
            (new CriterionEvaluationResultStatusCollection())
                        ->add(new ChannelCode('mobile'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done()),
            []
        );
        $completenessEvaluation = new Read\CriterionEvaluation(
            new CriterionCode('completeness_of_required_attributes'),
            ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending(),
            $completenessResult
        );
        $spellingEvaluation = new Read\CriterionEvaluation(
            new CriterionCode('consistency_textarea_uppercase_words'),
            ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending(),
            null
        );
        $this->sut->add($completenessEvaluation)
                    ->add($spellingEvaluation);
        $this->assertNull($this->sut->getCriterionRates(new CriterionCode('foo')));
        $this->assertNull($this->sut->getCriterionRates(new CriterionCode('consistency_textarea_uppercase_words')));
        $this->assertSame($completenessResult->getRates(), $this->sut->getCriterionRates(new CriterionCode('completeness_of_required_attributes')));
    }
}
