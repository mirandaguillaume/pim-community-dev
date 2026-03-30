<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ComputeScores;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaEvaluationRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ComputeScoresTest extends TestCase
{
    private GetLocalesByChannelQueryInterface|MockObject $getLocalesByChannelQuery;
    private CriteriaEvaluationRegistry|MockObject $criteriaEvaluationRegistry;
    private ComputeScores $sut;

    protected function setUp(): void
    {
        $this->getLocalesByChannelQuery = $this->createMock(GetLocalesByChannelQueryInterface::class);
        $this->criteriaEvaluationRegistry = $this->createMock(CriteriaEvaluationRegistry::class);
        $this->sut = new ComputeScores($this->getLocalesByChannelQuery, $this->criteriaEvaluationRegistry);
    }

    public function test_it_computes_the_product_scores_from_the_product_evaluation(): void
    {
        $this->getLocalesByChannelQuery->method('getChannelLocaleCollection')->willReturn(new ChannelLocaleCollection([
                    'mobile' => ['en_US', 'fr_FR'],
                    'print' => ['en_US', 'fr_FR'],
                ]));
        $channelMobile = new ChannelCode('mobile');
        $channelPrint = new ChannelCode('print');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');
        $criterionResultA = (new ChannelLocaleRateCollection())
                        ->addRate($channelMobile, $localeEn, new Rate(100))
                        ->addRate($channelMobile, $localeFr, new Rate(90))
                        ->addRate($channelPrint, $localeEn, new Rate(60));
        $criterionResultB = (new ChannelLocaleRateCollection())
                        ->addRate($channelMobile, $localeEn, new Rate(90))
                        ->addRate($channelMobile, $localeFr, new Rate(80))
                        ->addRate($channelPrint, $localeEn, new Rate(100));
        $criterionResultC = (new ChannelLocaleRateCollection())
                        ->addRate($channelMobile, $localeEn, new Rate(81))
                        ->addRate($channelPrint, $localeEn, new Rate(70));
        $criterionA = new CriterionCode('criterion_A');
        $criterionB = new CriterionCode('criterion_B');
        $criterionC = new CriterionCode('criterion_C');
        $criteriaEvaluations = (new CriterionEvaluationCollection())
                    ->add(new CriterionEvaluation(
                        $criterionA,
                        ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed'),
                        new \DateTimeImmutable(),
                        CriterionEvaluationStatus::done(),
                        new CriterionEvaluationResult($criterionResultA, new CriterionEvaluationResultStatusCollection(), [])
                    ))
                    ->add(new CriterionEvaluation(
                        $criterionB,
                        ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed'),
                        new \DateTimeImmutable(),
                        CriterionEvaluationStatus::done(),
                        new CriterionEvaluationResult($criterionResultB, new CriterionEvaluationResultStatusCollection(), [])
                    ))
                    ->add(new CriterionEvaluation(
                        $criterionC,
                        ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed'),
                        new \DateTimeImmutable(),
                        CriterionEvaluationStatus::done(),
                        new CriterionEvaluationResult($criterionResultC, new CriterionEvaluationResultStatusCollection(), [])
                    ))
        ;
        $this->criteriaEvaluationRegistry->method('getCriterionCoefficient')->with($criterionA)->willReturn(2);
        $this->criteriaEvaluationRegistry->method('getCriterionCoefficient')->with($criterionB)->willReturn(1);
        $this->criteriaEvaluationRegistry->method('getCriterionCoefficient')->with($criterionC)->willReturn(1);
        $this->assertEquals((new ChannelLocaleRateCollection())->addRate($channelMobile, $localeEn, new Rate(93))->addRate($channelMobile, $localeFr, new Rate(87))->addRate($channelPrint, $localeEn, new Rate(72)), $this->sut->fromCriteriaEvaluations($criteriaEvaluations));
    }
}
