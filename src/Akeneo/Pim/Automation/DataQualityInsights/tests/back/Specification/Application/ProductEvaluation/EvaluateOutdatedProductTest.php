<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateOutdatedProduct;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductUuidFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class EvaluateOutdatedProductTest extends TestCase
{
    private HasUpToDateEvaluationQueryInterface|MockObject $hasUpToDateEvaluationQuery;
    private EvaluateProducts|MockObject $evaluateProducts;
    private ProductUuidFactory|MockObject $uuidFactory;
    private EvaluateOutdatedProduct $sut;

    protected function setUp(): void
    {
        $this->hasUpToDateEvaluationQuery = $this->createMock(HasUpToDateEvaluationQueryInterface::class);
        $this->evaluateProducts = $this->createMock(EvaluateProducts::class);
        $this->uuidFactory = $this->createMock(ProductUuidFactory::class);
        $this->sut = new EvaluateOutdatedProduct($this->hasUpToDateEvaluationQuery, $this->evaluateProducts, $this->uuidFactory);
    }

    public function test_it_evaluate_a_product_if_it_has_outdated_evaluation(): void
    {
        $productUuid = ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed'));
        $collection = ProductUuidCollection::fromString('df470d52-7723-4890-85a0-e79be625e2ed');
        $this->hasUpToDateEvaluationQuery->method('forEntityId')->with($productUuid)->willReturn(false);
        $this->uuidFactory->method('createCollection')->with(['df470d52-7723-4890-85a0-e79be625e2ed'])->willReturn($collection);
        $this->evaluateProducts->expects($this->once())->method('__invoke')->with($collection);
        $this->sut->__invoke($productUuid);
    }

    public function test_it_does_not_evaluate_a_product_with_up_to_date_evaluation(): void
    {
        $productUuid = ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed'));
        $collection = ProductUuidCollection::fromString('df470d52-7723-4890-85a0-e79be625e2ed');
        $this->hasUpToDateEvaluationQuery->method('forEntityId')->with($productUuid)->willReturn(true);
        $this->uuidFactory->method('createCollection')->with(['df470d52-7723-4890-85a0-e79be625e2ed'])->willReturn($collection);
        $this->evaluateProducts->expects($this->never())->method('__invoke')->with($collection);
        $this->sut->__invoke($productUuid);
    }
}
