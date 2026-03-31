<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaApplicabilityRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaEvaluationRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\SynchronousCriterionEvaluationsFilterInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetEvaluableProductValuesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetPendingCriteriaEvaluationsByEntityIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluatePendingCriteriaTest extends TestCase
{
    private CriterionEvaluationRepositoryInterface|MockObject $repository;
    private CriteriaEvaluationRegistry|MockObject $evaluationRegistry;
    private CriteriaApplicabilityRegistry|MockObject $applicabilityRegistry;
    private GetPendingCriteriaEvaluationsByEntityIdsQueryInterface|MockObject $getPendingCriteriaEvaluationsQuery;
    private GetEvaluableProductValuesQueryInterface|MockObject $getEvaluableProductValuesQuery;
    private SynchronousCriterionEvaluationsFilterInterface|MockObject $synchronousCriterionEvaluationsFilter;
    private LoggerInterface|MockObject $logger;
    private ProductEntityIdFactoryInterface|MockObject $idFactory;
    private EvaluatePendingCriteria $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CriterionEvaluationRepositoryInterface::class);
        $this->evaluationRegistry = $this->createMock(CriteriaEvaluationRegistry::class);
        $this->applicabilityRegistry = $this->createMock(CriteriaApplicabilityRegistry::class);
        $this->getPendingCriteriaEvaluationsQuery = $this->createMock(GetPendingCriteriaEvaluationsByEntityIdsQueryInterface::class);
        $this->getEvaluableProductValuesQuery = $this->createMock(GetEvaluableProductValuesQueryInterface::class);
        $this->synchronousCriterionEvaluationsFilter = $this->createMock(SynchronousCriterionEvaluationsFilterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->idFactory = $this->createMock(ProductEntityIdFactoryInterface::class);
        $this->sut = new EvaluatePendingCriteria(
            $this->repository,
            $this->evaluationRegistry,
            $this->applicabilityRegistry,
            $this->getPendingCriteriaEvaluationsQuery,
            $this->getEvaluableProductValuesQuery,
            $this->synchronousCriterionEvaluationsFilter,
            $this->logger,
            $this->idFactory
        );
    }

    public function test_it_evaluates_criteria_for_a_set_of_products(): void
    {
        $evaluateNonRequiredAttributeCompleteness = $this->createMock(EvaluateCriterionInterface::class);
        $evaluateCompleteness = $this->createMock(EvaluateCriterionInterface::class);
        $productIdCollection = $this->createMock(ProductEntityIdCollection::class);

        $productIdCollection->method('isEmpty')->willReturn(false);

        $criterionNonRequired = new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE);
        $criterionRequired = new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE);

        $productId1 = ProductUuid::fromString('fef37e64-a963-47a9-b087-2cc67968f0a2');
        $productId2 = ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed');

        $criterion1 = new Write\CriterionEvaluation($criterionNonRequired, $productId1, CriterionEvaluationStatus::pending());
        $criterion2 = new Write\CriterionEvaluation($criterionRequired, $productId1, CriterionEvaluationStatus::pending());
        $criterion3 = new Write\CriterionEvaluation($criterionNonRequired, $productId2, CriterionEvaluationStatus::pending());

        $criteriaProduct1 = (new Write\CriterionEvaluationCollection())->add($criterion1)->add($criterion2);
        $criteriaProduct2 = (new Write\CriterionEvaluationCollection())->add($criterion3);

        $this->getPendingCriteriaEvaluationsQuery->method('execute')
            ->with($productIdCollection)
            ->willReturn([
                'fef37e64-a963-47a9-b087-2cc67968f0a2' => $criteriaProduct1,
                'df470d52-7723-4890-85a0-e79be625e2ed' => $criteriaProduct2,
            ]);

        $productValues1 = $this->givenRandomProductValues();
        $productValues2 = $this->givenRandomProductValues();

        $this->idFactory->method('create')
            ->willReturnCallback(fn (string $id) => ProductUuid::fromString($id));

        $this->getEvaluableProductValuesQuery->method('byProductId')
            ->willReturnCallback(function (ProductEntityIdInterface $id) use ($productValues1, $productValues2) {
                return match ((string) $id) {
                    'fef37e64-a963-47a9-b087-2cc67968f0a2' => $productValues1,
                    'df470d52-7723-4890-85a0-e79be625e2ed' => $productValues2,
                    default => new ProductValuesCollection(),
                };
            });

        $this->evaluationRegistry->method('get')
            ->willReturnCallback(function (CriterionCode $code) use ($evaluateNonRequiredAttributeCompleteness, $evaluateCompleteness) {
                return match ((string) $code) {
                    EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE => $evaluateNonRequiredAttributeCompleteness,
                    EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE => $evaluateCompleteness,
                };
            });

        $evaluateNonRequiredAttributeCompleteness->method('evaluate')->willReturn(new Write\CriterionEvaluationResult());
        $evaluateCompleteness->method('evaluate')->willReturn(new Write\CriterionEvaluationResult());

        $this->repository->expects($this->exactly(2))->method('update');

        $this->sut->evaluateAllCriteria($productIdCollection);

        $this->assertEquals(CriterionEvaluationStatus::done(), $criterion1->getStatus());
        $this->assertEquals(CriterionEvaluationStatus::done(), $criterion2->getStatus());
        $this->assertEquals(CriterionEvaluationStatus::done(), $criterion3->getStatus());
    }

    public function test_it_continues_to_evaluate_if_an_evaluation_failed(): void
    {
        $evaluateCriterion = $this->createMock(EvaluateCriterionInterface::class);
        $productIdCollection = $this->createMock(ProductEntityIdCollection::class);

        $productIdCollection->method('isEmpty')->willReturn(false);

        $criterionCode = new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE);
        $productIdA = ProductUuid::fromString('fef37e64-a963-47a9-b087-2cc67968f0a2');
        $productIdB = ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed');

        $criterionA = new Write\CriterionEvaluation($criterionCode, $productIdA, CriterionEvaluationStatus::pending());
        $criterionB = new Write\CriterionEvaluation($criterionCode, $productIdB, CriterionEvaluationStatus::pending());

        $criteriaA = (new Write\CriterionEvaluationCollection())->add($criterionA);
        $criteriaB = (new Write\CriterionEvaluationCollection())->add($criterionB);

        $this->getPendingCriteriaEvaluationsQuery->method('execute')
            ->with($productIdCollection)
            ->willReturn([
                'fef37e64-a963-47a9-b087-2cc67968f0a2' => $criteriaA,
                'df470d52-7723-4890-85a0-e79be625e2ed' => $criteriaB,
            ]);

        $product42Values = $this->givenRandomProductValues();
        $product123Values = $this->givenRandomProductValues();

        $this->idFactory->method('create')
            ->willReturnCallback(fn (string $id) => ProductUuid::fromString($id));

        $this->getEvaluableProductValuesQuery->method('byProductId')
            ->willReturnCallback(function (ProductEntityIdInterface $id) use ($product42Values, $product123Values) {
                return match ((string) $id) {
                    'fef37e64-a963-47a9-b087-2cc67968f0a2' => $product42Values,
                    'df470d52-7723-4890-85a0-e79be625e2ed' => $product123Values,
                    default => new ProductValuesCollection(),
                };
            });

        $this->evaluationRegistry->method('get')->willReturn($evaluateCriterion);

        $callCount = 0;
        $evaluateCriterion->method('evaluate')
            ->willReturnCallback(function () use (&$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    throw new \Exception('Evaluation failed');
                }
                return new Write\CriterionEvaluationResult();
            });

        $this->repository->expects($this->exactly(2))->method('update');

        $this->sut->evaluateAllCriteria($productIdCollection);

        $this->assertEquals(CriterionEvaluationStatus::error(), $criterionA->getStatus());
        $this->assertEquals(CriterionEvaluationStatus::done(), $criterionB->getStatus());
    }

    public function test_it_evaluates_synchronous_criteria_for_a_set_of_products(): void
    {
        $evaluateSpelling = $this->createMock(EvaluateCriterionInterface::class);
        $productIdCollection = $this->createMock(ProductEntityIdCollection::class);

        $productIdCollection->method('isEmpty')->willReturn(false);

        $criterionCode = new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE);
        $productIdA = ProductUuid::fromString('fef37e64-a963-47a9-b087-2cc67968f0a2');
        $productIdB = ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed');

        $criterionA = new Write\CriterionEvaluation($criterionCode, $productIdA, CriterionEvaluationStatus::pending());
        $criterionB = new Write\CriterionEvaluation($criterionCode, $productIdB, CriterionEvaluationStatus::pending());

        $criteriaA = (new Write\CriterionEvaluationCollection())->add($criterionA);
        $criteriaB = (new Write\CriterionEvaluationCollection())->add($criterionB);

        $this->getPendingCriteriaEvaluationsQuery->method('execute')
            ->with($productIdCollection)
            ->willReturn([
                'fef37e64-a963-47a9-b087-2cc67968f0a2' => $criteriaA,
                'df470d52-7723-4890-85a0-e79be625e2ed' => $criteriaB,
            ]);

        $product42Values = $this->givenRandomProductValues();
        $product123Values = $this->givenRandomProductValues();

        $this->idFactory->method('create')
            ->willReturnCallback(fn (string $id) => ProductUuid::fromString($id));

        $this->getEvaluableProductValuesQuery->method('byProductId')
            ->willReturnCallback(function (ProductEntityIdInterface $id) use ($product42Values, $product123Values) {
                return match ((string) $id) {
                    'fef37e64-a963-47a9-b087-2cc67968f0a2' => $product42Values,
                    'df470d52-7723-4890-85a0-e79be625e2ed' => $product123Values,
                    default => new ProductValuesCollection(),
                };
            });

        $this->evaluationRegistry->method('get')->willReturn($evaluateSpelling);
        $evaluateSpelling->method('evaluate')->willReturn(new Write\CriterionEvaluationResult());

        $this->synchronousCriterionEvaluationsFilter->method('filter')
            ->willReturnCallback(function (\Iterator $iterator) {
                return iterator_to_array($iterator);
            });

        $this->repository->expects($this->exactly(2))->method('update');

        $this->sut->evaluateSynchronousCriteria($productIdCollection);

        $this->assertEquals(CriterionEvaluationStatus::done(), $criterionA->getStatus());
        $this->assertEquals(CriterionEvaluationStatus::done(), $criterionB->getStatus());
    }

    private function givenRandomProductValues(): ProductValuesCollection
    {
        $attribute = new Attribute(new AttributeCode(strval(Uuid::uuid4())), AttributeType::text(), true);
        $values = (new ChannelLocaleDataCollection())
            ->addToChannelAndLocale(new ChannelCode('mobile'), new LocaleCode('en_US'), strval(Uuid::uuid4()))
            ->addToChannelAndLocale(new ChannelCode('print'), new LocaleCode('fr_FR'), strval(Uuid::uuid4()));

        return (new ProductValuesCollection())->add(new ProductValues($attribute, $values));
    }
}
