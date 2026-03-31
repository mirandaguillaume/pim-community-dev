<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ComputeScores;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateProductModelScores;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\FilterPartialCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByEntityIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductModelScoreRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConsolidateProductModelScoresTest extends TestCase
{
    private GetCriteriaEvaluationsByEntityIdQueryInterface|MockObject $getCriteriaEvaluationsQuery;
    private ComputeScores|MockObject $computeScores;
    private ProductModelScoreRepositoryInterface|MockObject $productModelScoreRepository;
    private Clock|MockObject $clock;
    private FilterPartialCriteriaEvaluations|MockObject $filterCriteriaEvaluationsForPartialScore;
    private ConsolidateProductModelScores $sut;

    protected function setUp(): void
    {
        $this->getCriteriaEvaluationsQuery = $this->createMock(GetCriteriaEvaluationsByEntityIdQueryInterface::class);
        $this->computeScores = $this->createMock(ComputeScores::class);
        $this->productModelScoreRepository = $this->createMock(ProductModelScoreRepositoryInterface::class);
        $this->clock = $this->createMock(Clock::class);
        $this->filterCriteriaEvaluationsForPartialScore = $this->createMock(FilterPartialCriteriaEvaluations::class);
        $this->sut = new ConsolidateProductModelScores($this->getCriteriaEvaluationsQuery, $this->computeScores, $this->productModelScoreRepository, $this->clock, $this->filterCriteriaEvaluationsForPartialScore);
    }

    public function test_it_consolidates_product_model_scores(): void
    {
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $productModelId1 = new ProductModelId(42);
        $productModelId2 = new ProductModelId(56);
        $this->clock->method('getCurrentTime')->willReturn(new \DateTimeImmutable());
        $scores1 = (new ChannelLocaleRateCollection())->addRate($channelMobile, $localeEn, new Rate(93));
        $productModelId1Evaluations = $this->givenACriterionEvaluationCollection($productModelId1);
        $scores2 = (new ChannelLocaleRateCollection())->addRate($channelMobile, $localeEn, new Rate(65));
        $productModelId2Evaluations = $this->givenACriterionEvaluationCollection($productModelId2);
        $this->filterCriteriaEvaluationsForPartialScore->method('__invoke')->willReturnArgument(0);
        $this->getCriteriaEvaluationsQuery->method('execute')->willReturnCallback(fn (ProductEntityIdInterface $id) => match ((string) $id) {
            '42' => $productModelId1Evaluations,
            '56' => $productModelId2Evaluations,
            default => new Read\CriterionEvaluationCollection(),
        });
        $this->computeScores->method('fromCriteriaEvaluations')->willReturnCallback(fn (Read\CriterionEvaluationCollection $evals) => match (true) {
            $evals === $productModelId1Evaluations => $scores1,
            $evals === $productModelId2Evaluations => $scores2,
            default => new ChannelLocaleRateCollection(),
        });
        $this->productModelScoreRepository->expects($this->once())->method('saveAll')->with($this->callback(fn (array $productModelScores) => 2 === count($productModelScores)
                    && $productModelScores[0] instanceof Write\ProductScores && (string) $productModelId1 === (string) $productModelScores[0]->getEntityId() && $scores1 === $productModelScores[0]->getScores()
                    && $productModelScores[1] instanceof Write\ProductScores && (string) $productModelId2 === (string) $productModelScores[1]->getEntityId() && $scores2 === $productModelScores[1]->getScores()));
        $this->sut->consolidate(ProductModelIdCollection::fromStrings([$productModelId1, $productModelId2]));
    }

    private function givenACriterionEvaluationCollection(ProductEntityIdInterface $entityId): Read\CriterionEvaluationCollection
    {
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
    
        $criterionResultA = (new ChannelLocaleRateCollection())->addRate($channelMobile, $localeEn, new Rate(100));
        $criterionResultB = (new ChannelLocaleRateCollection())->addRate($channelMobile, $localeEn, new Rate(90));
    
        $criterionA = new CriterionCode('criterion_A');
        $criterionB = new CriterionCode('criterion_B');
    
        return (new Read\CriterionEvaluationCollection())
            ->add(new Read\CriterionEvaluation(
                $criterionA,
                $entityId,
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::done(),
                new Read\CriterionEvaluationResult($criterionResultA, new CriterionEvaluationResultStatusCollection(), [])
            ))
            ->add(new Read\CriterionEvaluation(
                $criterionB,
                $entityId,
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::done(),
                new Read\CriterionEvaluationResult($criterionResultB, new CriterionEvaluationResultStatusCollection(), [])
            ));
    }
}
