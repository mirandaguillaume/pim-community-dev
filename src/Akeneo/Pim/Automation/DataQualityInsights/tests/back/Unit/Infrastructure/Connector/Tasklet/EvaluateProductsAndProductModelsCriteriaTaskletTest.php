<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEntityIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\EvaluateProductsAndProductModelsCriteriaTasklet;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class EvaluateProductsAndProductModelsCriteriaTaskletTest extends TestCase
{
    private GetEntityIdsToEvaluateQueryInterface|MockObject $getProductUuidsToEvaluateQuery;
    private GetEntityIdsToEvaluateQueryInterface|MockObject $getProductModelsIdsToEvaluateQuery;
    private EvaluateProducts|MockObject $evaluateProducts;
    private EvaluateProductModels|MockObject $evaluateProductModels;
    private EvaluateProductsAndProductModelsCriteriaTasklet $sut;

    protected function setUp(): void
    {
        $this->getProductUuidsToEvaluateQuery = $this->createMock(GetEntityIdsToEvaluateQueryInterface::class);
        $this->getProductModelsIdsToEvaluateQuery = $this->createMock(GetEntityIdsToEvaluateQueryInterface::class);
        $this->evaluateProducts = $this->createMock(EvaluateProducts::class);
        $this->evaluateProductModels = $this->createMock(EvaluateProductModels::class);
        $this->sut = new EvaluateProductsAndProductModelsCriteriaTasklet($this->getProductUuidsToEvaluateQuery, $this->getProductModelsIdsToEvaluateQuery, $this->evaluateProducts, $this->evaluateProductModels, 1000, 2, 0, 0);
    }

    public function test_it_evaluates_products_and_product_models(): void
    {
        $stepExecution = new StepExecution('name', new JobExecution());
        $this->sut->setStepExecution($stepExecution);
        $productUuids = [ProductUuidCollection::fromStrings([
                    '6d125b99-d971-41d9-a264-b020cd486aee',
                    'fef37e64-a963-47a9-b087-2cc67968f0a2',
                ]), ProductUuidCollection::fromStrings([
                    'df470d52-7723-4890-85a0-e79be625e2ed',
                ])];
        $this->getProductUuidsToEvaluateQuery->method('execute')->with(1000, 2)->willReturn(new \ArrayIterator($productUuids));
        $this->evaluateProducts->expects($this->exactly(2))->method('__invoke');
        $productModelIds = [ProductModelIdCollection::fromStrings(['4', '5']), ProductModelIdCollection::fromStrings(['6', '7'])];
        $this->getProductModelsIdsToEvaluateQuery->method('execute')->with(1000, 2)->willReturn(new \ArrayIterator($productModelIds));
        $this->evaluateProductModels->expects($this->exactly(2))->method('__invoke');
        $this->sut->execute();
        $evaluationSummary = $stepExecution->getSummaryInfo('evaluations');
        Assert::same($evaluationSummary['products']['count'], 3);
        Assert::same($evaluationSummary['product_models']['count'], 4);
    }
}
