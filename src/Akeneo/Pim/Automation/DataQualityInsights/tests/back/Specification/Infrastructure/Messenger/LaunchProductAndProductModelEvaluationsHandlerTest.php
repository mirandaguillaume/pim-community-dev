<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaByFeatureRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetOutdatedProductModelIdsByDateAndCriteriaQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetOutdatedProductUuidsByDateAndCriteriaQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger\LaunchProductAndProductModelEvaluationsHandler;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger\LaunchProductAndProductModelEvaluationsMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class LaunchProductAndProductModelEvaluationsHandlerTest extends TestCase
{
    private CriteriaByFeatureRegistry|MockObject $productCriteriaRegistry;
    private CriteriaByFeatureRegistry|MockObject $productModelCriteriaRegistry;
    private CreateCriteriaEvaluations|MockObject $createProductCriteriaEvaluations;
    private CreateCriteriaEvaluations|MockObject $createProductModelCriteriaEvaluations;
    private EvaluateProducts|MockObject $evaluateProducts;
    private EvaluateProductModels|MockObject $evaluateProductModels;
    private GetOutdatedProductUuidsByDateAndCriteriaQueryInterface|MockObject $getOutdatedProductUuids;
    private GetOutdatedProductModelIdsByDateAndCriteriaQueryInterface|MockObject $getOutdatedProductModelIds;
    private LoggerInterface|MockObject $logger;
    private LaunchProductAndProductModelEvaluationsHandler $sut;

    protected function setUp(): void
    {
        $this->productCriteriaRegistry = $this->createMock(CriteriaByFeatureRegistry::class);
        $this->productModelCriteriaRegistry = $this->createMock(CriteriaByFeatureRegistry::class);
        $this->createProductCriteriaEvaluations = $this->createMock(CreateCriteriaEvaluations::class);
        $this->createProductModelCriteriaEvaluations = $this->createMock(CreateCriteriaEvaluations::class);
        $this->evaluateProducts = $this->createMock(EvaluateProducts::class);
        $this->evaluateProductModels = $this->createMock(EvaluateProductModels::class);
        $this->getOutdatedProductUuids = $this->createMock(GetOutdatedProductUuidsByDateAndCriteriaQueryInterface::class);
        $this->getOutdatedProductModelIds = $this->createMock(GetOutdatedProductModelIdsByDateAndCriteriaQueryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sut = new LaunchProductAndProductModelEvaluationsHandler(
            $this->productCriteriaRegistry,
            $this->productModelCriteriaRegistry,
            $this->createProductCriteriaEvaluations,
            $this->createProductModelCriteriaEvaluations,
            $this->evaluateProducts,
            $this->evaluateProductModels,
            $this->getOutdatedProductUuids,
            $this->getOutdatedProductModelIds,
            $this->logger,
        );
    }

    public function test_it_is_a_launch_products_and_product_models_evaluation_handler(): void
    {
        $this->assertInstanceOf(LaunchProductAndProductModelEvaluationsHandler::class, $this->sut);
    }

    public function test_it_launches_products_and_product_models_evaluations_for_all_criteria_only_for_outdated_products_and_product_models(): void
    {
        $completenessCriterionCode = new CriterionCode('enrichment_completeness');
        $imageCriterionCode = new CriterionCode('enrichment_image');
        $spellcheckCriterionCode = new CriterionCode('consistency_spellcheck');
        $productCriteria = [$completenessCriterionCode, $imageCriterionCode, $spellcheckCriterionCode];
        $this->productCriteriaRegistry->method('getAllCriterionCodes')->willReturn($productCriteria);
        $productModelCriteria = [$completenessCriterionCode, $spellcheckCriterionCode];
        $this->productModelCriteriaRegistry->method('getAllCriterionCodes')->willReturn($productModelCriteria);
        $productUuid1 = ProductUuid::fromUuid(Uuid::uuid4());
        $productUuid2 = ProductUuid::fromUuid(Uuid::uuid4());
        $productUuid3 = ProductUuid::fromUuid(Uuid::uuid4());
        $productUuids = ProductUuidCollection::fromProductUuids([$productUuid1, $productUuid2, $productUuid3]);
        $outDatedProductUuids = ProductUuidCollection::fromProductUuids([$productUuid1, $productUuid2]);
        $productModelId1 = new ProductModelId(42);
        $productModelId2 = new ProductModelId(123);
        $productModelIds = ProductModelIdCollection::fromProductModelIds([$productModelId1, $productModelId2]);
        $outDatedProductModelIds = ProductModelIdCollection::fromProductModelIds([$productModelId1]);
        $message = new LaunchProductAndProductModelEvaluationsMessage(
            new \DateTimeImmutable('2023-03-16 14:46:32'),
            $productUuids,
            $productModelIds,
            []
        );
        $this->getOutdatedProductUuids->method('__invoke')->with($productUuids, $message->datetime, [])->willReturn($outDatedProductUuids);
        $this->getOutdatedProductModelIds->method('__invoke')->with($productModelIds, $message->datetime, [])->willReturn($outDatedProductModelIds);
        $this->createProductCriteriaEvaluations->expects($this->once())->method('create')->with($productCriteria, $outDatedProductUuids);
        $this->evaluateProducts->expects($this->once())->method('__invoke')->with($outDatedProductUuids);
        $this->createProductModelCriteriaEvaluations->expects($this->once())->method('create')->with($productModelCriteria, $outDatedProductModelIds);
        $this->evaluateProductModels->expects($this->once())->method('__invoke')->with($outDatedProductModelIds);
        $this->sut->__invoke($message);
    }

    public function test_it_launches_products_and_product_models_evaluations_for_only_given_criteria(): void
    {
        $criteriaToEvaluate = [
                    new CriterionCode('enrichment_completeness'),
                    new CriterionCode('enrichment_image'),
                ];
        $this->productCriteriaRegistry->expects($this->never())->method('getAllCriterionCodes');
        $this->productModelCriteriaRegistry->expects($this->never())->method('getAllCriterionCodes');
        $productUuid1 = ProductUuid::fromUuid(Uuid::uuid4());
        $productUuid2 = ProductUuid::fromUuid(Uuid::uuid4());
        $productUuids = ProductUuidCollection::fromProductUuids([$productUuid1, $productUuid2]);
        $productModelId1 = new ProductModelId(42);
        $productModelId2 = new ProductModelId(123);
        $productModelIds = ProductModelIdCollection::fromProductModelIds([$productModelId1, $productModelId2]);
        $message = new LaunchProductAndProductModelEvaluationsMessage(
            new \DateTimeImmutable('2023-03-16 14:46:32'),
            $productUuids,
            $productModelIds,
            ['enrichment_completeness', 'enrichment_image']
        );
        $this->getOutdatedProductUuids->method('__invoke')->with($productUuids, $message->datetime, $message->criteriaToEvaluate)->willReturn($productUuids);
        $this->getOutdatedProductModelIds->method('__invoke')->with($productModelIds, $message->datetime, $message->criteriaToEvaluate)->willReturn($productModelIds);
        $this->createProductCriteriaEvaluations->expects($this->once())->method('create')->with($criteriaToEvaluate, $productUuids);
        $this->evaluateProducts->expects($this->once())->method('__invoke')->with($productUuids);
        $this->createProductModelCriteriaEvaluations->expects($this->once())->method('create')->with($criteriaToEvaluate, $productModelIds);
        $this->evaluateProductModels->expects($this->once())->method('__invoke')->with($productModelIds);
        $this->sut->__invoke($message);
    }

    public function test_it_does_not_launch_evaluations_if_there_are_no_outdated_products_and_product_models(): void
    {
        $this->productCriteriaRegistry->expects($this->never())->method('getAllCriterionCodes');
        $this->productModelCriteriaRegistry->expects($this->never())->method('getAllCriterionCodes');
        $productUuid1 = ProductUuid::fromUuid(Uuid::uuid4());
        $productUuid2 = ProductUuid::fromUuid(Uuid::uuid4());
        $productUuids = ProductUuidCollection::fromProductUuids([$productUuid1, $productUuid2]);
        $productModelId1 = new ProductModelId(42);
        $productModelId2 = new ProductModelId(123);
        $productModelIds = ProductModelIdCollection::fromProductModelIds([$productModelId1, $productModelId2]);
        $message = new LaunchProductAndProductModelEvaluationsMessage(
            new \DateTimeImmutable('2023-03-16 14:46:32'),
            $productUuids,
            $productModelIds,
            ['enrichment_completeness', 'enrichment_image']
        );
        $this->getOutdatedProductUuids->method('__invoke')->with($productUuids, $message->datetime, $message->criteriaToEvaluate)->willReturn(ProductUuidCollection::fromProductUuids([]));
        $this->getOutdatedProductModelIds->method('__invoke')->with($productModelIds, $message->datetime, $message->criteriaToEvaluate)->willReturn(ProductModelIdCollection::fromProductModelIds([]));
        $this->createProductCriteriaEvaluations->expects($this->never())->method('create');
        $this->evaluateProducts->expects($this->never())->method('__invoke')->with($this->anything());
        $this->createProductModelCriteriaEvaluations->expects($this->never())->method('create');
        $this->evaluateProductModels->expects($this->never())->method('__invoke')->with($this->anything());
        $this->sut->__invoke($message);
    }
}
