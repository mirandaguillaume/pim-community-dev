<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateOutdatedProductModel;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EvaluateOutdatedProductModelTest extends TestCase
{
    private HasUpToDateEvaluationQueryInterface|MockObject $hasUpToDateEvaluationQuery;
    private EvaluateProductModels|MockObject $evaluateProductModels;
    private ProductEntityIdFactoryInterface|MockObject $idFactory;
    private EvaluateOutdatedProductModel $sut;

    protected function setUp(): void
    {
        $this->hasUpToDateEvaluationQuery = $this->createMock(HasUpToDateEvaluationQueryInterface::class);
        $this->evaluateProductModels = $this->createMock(EvaluateProductModels::class);
        $this->idFactory = $this->createMock(ProductEntityIdFactoryInterface::class);
        $this->sut = new EvaluateOutdatedProductModel($this->hasUpToDateEvaluationQuery, $this->evaluateProductModels, $this->idFactory);
    }

    public function test_it_evaluate_a_product_model_if_it_has_outdated_evaluation(): void
    {
        $productModelId = new ProductModelId(42);
        $collection = ProductModelIdCollection::fromStrings(['42']);
        $this->hasUpToDateEvaluationQuery->method('forEntityId')->with($productModelId)->willReturn(false);
        $this->idFactory->method('createCollection')->with(['42'])->willReturn($collection);
        $this->evaluateProductModels->expects($this->once())->method('__invoke')->with($collection);
        $this->sut->__invoke($productModelId);
    }

    public function test_it_does_not_evaluate_a_product_model_with_up_to_date_evaluation(): void
    {
        $productModelId = new ProductModelId(42);
        $collection = ProductModelIdCollection::fromStrings(['42']);
        $this->hasUpToDateEvaluationQuery->method('forEntityId')->with($productModelId)->willReturn(true);
        $this->idFactory->method('createCollection')->with(['42'])->willReturn($collection);
        $this->evaluateProductModels->expects($this->never())->method('__invoke')->with($collection);
        $this->sut->__invoke($productModelId);
    }
}
