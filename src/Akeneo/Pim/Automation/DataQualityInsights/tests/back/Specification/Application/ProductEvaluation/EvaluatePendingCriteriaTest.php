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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

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
        $this->sut = new EvaluatePendingCriteria($this->repository, $this->evaluationRegistry, $this->applicabilityRegistry, $this->getPendingCriteriaEvaluationsQuery, $this->getEvaluableProductValuesQuery, $this->synchronousCriterionEvaluationsFilter, $this->logger, $this->idFactory);
    }

    public function test_it_evaluates_criteria_for_a_set_of_products(): void
    {
        $evaluateNonRequiredAttributeCompleteness = $this->createMock(EvaluateCriterionInterface::class);
        $evaluateCompleteness = $this->createMock(EvaluateCriterionInterface::class);
        $productIdCollection = $this->createMock(ProductEntityIdCollection::class);
        $productId_fef37e64 = $this->createMock(ProductEntityIdInterface::class);
        $productIdB = $this->createMock(ProductEntityIdInterface::class);

        $productIdCollection->method('isEmpty')->willReturn(false);
        $productIdCollection->method('toArrayString')->willReturn(['fef37e64-a963-47a9-b087-2cc67968f0a2', 'df470d52-7723-4890-85a0-e79be625e2ed']);
        $criterionNonRequiredAttributesCompleteness = new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE);
        $criterionRequiredAttributesCompleteness = new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE);
        $criteria = [
                    'product_fef37e64_non_required_att_completeness' => new Write\CriterionEvaluation(
                        $criterionNonRequiredAttributesCompleteness,
                        ProductUuid::fromString('fef37e64-a963-47a9-b087-2cc67968f0a2'),
                        CriterionEvaluationStatus::pending()
                    ),
                    'product_fef37e64_completeness' => new Write\CriterionEvaluation(
                        $criterionRequiredAttributesCompleteness,
                        ProductUuid::fromString('fef37e64-a963-47a9-b087-2cc67968f0a2'),
                        CriterionEvaluationStatus::pending()
                    ),
                    'product_df470d52_non_required_att_completeness' => new Write\CriterionEvaluation(
                        $criterionNonRequiredAttributesCompleteness,
                        ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed'),
                        CriterionEvaluationStatus::pending()
                    ),
                ];
        $criteriaProduct_fef37e64 = (new Write\CriterionEvaluationCollection())
                    ->add($criteria['product_fef37e64_non_required_att_completeness'])
                    ->add($criteria['product_fef37e64_completeness']);
        $criteriaProduct_df470d52 = (new Write\CriterionEvaluationCollection())
                    ->add($criteria['product_df470d52_non_required_att_completeness']);
        $this->getPendingCriteriaEvaluationsQuery->method('execute')->with($productIdCollection);
        $productValues_fef37e64 = $this->givenRandomProductValues();
        $productValues_df470d52 = $this->givenRandomProductValues();
        $productId_fef37e64->method('__toString')->willReturn('fef37e64-a963-47a9-b087-2cc67968f0a2');
        $productIdB->method('__toString')->willReturn('df470d52-7723-4890-85a0-e79be625e2ed');
        $this->idFactory->method('create')->with('fef37e64-a963-47a9-b087-2cc67968f0a2')->willReturn($productId_fef37e64);
        $this->idFactory->method('create')->with('df470d52-7723-4890-85a0-e79be625e2ed')->willReturn($productIdB);
        $this->getEvaluableProductValuesQuery->method('byProductId')->with($productId_fef37e64)->willReturn($productValues_fef37e64);
        $this->getEvaluableProductValuesQuery->method('byProductId')->with($productIdB)->willReturn($productValues_df470d52);
        $this->evaluationRegistry->method('get')->with($criterionNonRequiredAttributesCompleteness)->willReturn($evaluateNonRequiredAttributeCompleteness);
        $this->evaluationRegistry->method('get')->with($criterionRequiredAttributesCompleteness)->willReturn($evaluateCompleteness);
        $evaluateNonRequiredAttributeCompleteness->method('evaluate')->with($criteria['product_fef37e64_non_required_att_completeness'], $productValues_fef37e64)->willReturn(new Write\CriterionEvaluationResult());
        $evaluateNonRequiredAttributeCompleteness->method('evaluate')->with($criteria['product_df470d52_non_required_att_completeness'], $productValues_df470d52)->willReturn(new Write\CriterionEvaluationResult());
        $evaluateCompleteness->method('evaluate')->with($criteria['product_fef37e64_completeness'], $productValues_fef37e64)->willReturn(new Write\CriterionEvaluationResult());
        $this->repository->expects($this->exactly(2))->method('update')->with($this->anything());
        $this->sut->evaluateAllCriteria($productIdCollection);
        foreach ($criteria as $criterionEvaluation) {
            Assert::eq(CriterionEvaluationStatus::done(), $criterionEvaluation->getStatus());
        }
    }

    public function test_it_continues_to_evaluate_if_an_evaluation_failed(): void
    {
        $evaluateCriterion = $this->createMock(EvaluateCriterionInterface::class);
        $productIdCollection = $this->createMock(ProductEntityIdCollection::class);
        $productIdA = $this->createMock(ProductEntityIdInterface::class);
        $productIdB = $this->createMock(ProductEntityIdInterface::class);

        $productIdCollection->method('isEmpty')->willReturn(false);
        $productIdCollection->method('toArrayString')->willReturn(['42', '123']);
        $this->idFactory->method('create')->with('42')->willReturn($productIdA);
        $this->idFactory->method('create')->with('123')->willReturn($productIdB);
        $productIdA->method('__toString')->willReturn('42');
        $productIdB->method('__toString')->willReturn('123');
        $criterionCode = new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE);
        $criterionA = new Write\CriterionEvaluation(
            $criterionCode,
            $productIdA,
            CriterionEvaluationStatus::pending()
        );
        $criterionB = new Write\CriterionEvaluation(
            $criterionCode,
            $productIdB,
            CriterionEvaluationStatus::pending()
        );
        $this->getPendingCriteriaEvaluationsQuery->method('execute')->with($productIdCollection);
        $product42Values = $this->givenRandomProductValues();
        $product123Values = $this->givenRandomProductValues();
        $this->getEvaluableProductValuesQuery->method('byProductId')->with($productIdA)->willReturn($product42Values);
        $this->getEvaluableProductValuesQuery->method('byProductId')->with($productIdB)->willReturn($product123Values);
        $this->evaluationRegistry->method('get')->with($criterionCode)->willReturn($evaluateCriterion);
        $evaluateCriterion->method('evaluate')->with($criterionA, $product42Values)->willThrowException(new \Exception('Evaluation failed'));
        $evaluateCriterion->method('evaluate')->with($criterionB, $product123Values)->willReturn(new Write\CriterionEvaluationResult());
        $this->repository->expects($this->exactly(2))->method('update')->with($this->anything());
        $this->sut->evaluateAllCriteria($productIdCollection);
        Assert::eq(CriterionEvaluationStatus::error(), $criterionA->getStatus());
        Assert::eq(CriterionEvaluationStatus::done(), $criterionB->getStatus());
    }

    public function test_it_evaluates_synchronous_criteria_for_a_set_of_products(): void
    {
        $evaluateSpelling = $this->createMock(EvaluateCriterionInterface::class);
        $productIdCollection = $this->createMock(ProductEntityIdCollection::class);
        $productIdA = $this->createMock(ProductEntityIdInterface::class);
        $productIdB = $this->createMock(ProductEntityIdInterface::class);

        $productIdCollection->method('isEmpty')->willReturn(false);
        $productIdCollection->method('toArrayString')->willReturn(['42', '123']);
        $this->idFactory->method('create')->with('42')->willReturn($productIdA);
        $this->idFactory->method('create')->with('123')->willReturn($productIdB);
        $productIdA->method('__toString')->willReturn('42');
        $productIdB->method('__toString')->willReturn('123');
        $criterionNonRequiredAttributeCompleteness = new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE);
        $criteria = [
                    'product_42_non_required_att_completeness' => new Write\CriterionEvaluation(
                        $criterionNonRequiredAttributeCompleteness,
                        $productIdA,
                        CriterionEvaluationStatus::pending()
                    ),
                    'product_123_non_required_att_completeness' => new Write\CriterionEvaluation(
                        $criterionNonRequiredAttributeCompleteness,
                        $productIdB,
                        CriterionEvaluationStatus::pending()
                    ),
                ];
        $product42CriteriaCollection = (new Write\CriterionEvaluationCollection())->add($criteria['product_42_non_required_att_completeness']);
        $product123CriteriaCollection = (new Write\CriterionEvaluationCollection())->add($criteria['product_123_non_required_att_completeness']);
        $this->getPendingCriteriaEvaluationsQuery->method('execute')->with($productIdCollection);
        $product42Values = $this->givenRandomProductValues();
        $product123Values = $this->givenRandomProductValues();
        $this->getEvaluableProductValuesQuery->method('byProductId')->with($productIdA)->willReturn($product42Values);
        $this->getEvaluableProductValuesQuery->method('byProductId')->with($productIdB)->willReturn($product123Values);
        $this->evaluationRegistry->method('get')->with($criterionNonRequiredAttributeCompleteness)->willReturn($evaluateSpelling);
        $evaluateSpelling->method('evaluate')->with($criteria['product_42_non_required_att_completeness'], $product42Values)->willReturn(new Write\CriterionEvaluationResult());
        $evaluateSpelling->method('evaluate')->with($criteria['product_123_non_required_att_completeness'], $product123Values)->willReturn(new Write\CriterionEvaluationResult());
        $this->repository->expects($this->exactly(2))->method('update')->with($this->anything());
        $this->synchronousCriterionEvaluationsFilter->method('filter')->with($product42CriteriaCollection->getIterator())->willReturn([
                    $criteria['product_42_non_required_att_completeness'],
                ]);
        $this->synchronousCriterionEvaluationsFilter->method('filter')->with($product123CriteriaCollection->getIterator())->willReturn([
                    $criteria['product_123_non_required_att_completeness'],
                ]);
        $this->sut->evaluateSynchronousCriteria($productIdCollection);
        Assert::eq($criteria['product_42_non_required_att_completeness']->getStatus(), CriterionEvaluationStatus::done());
        Assert::eq($criteria['product_123_non_required_att_completeness']->getStatus(), CriterionEvaluationStatus::done());
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
