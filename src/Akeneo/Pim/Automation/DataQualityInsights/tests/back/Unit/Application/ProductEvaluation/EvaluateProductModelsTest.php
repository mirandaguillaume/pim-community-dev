<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateProductModelScores;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\ProductModelsEvaluated;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluateProductModelsTest extends TestCase
{
    private EvaluatePendingCriteria|MockObject $evaluatePendingProductModelCriteria;
    private ConsolidateProductModelScores|MockObject $consolidateProductScores;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private LoggerInterface|MockObject $logger;
    private EvaluateProductModels $sut;

    protected function setUp(): void
    {
        $this->evaluatePendingProductModelCriteria = $this->createMock(EvaluatePendingCriteria::class);
        $this->consolidateProductScores = $this->createMock(ConsolidateProductModelScores::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sut = new EvaluateProductModels($this->evaluatePendingProductModelCriteria, $this->consolidateProductScores, $this->eventDispatcher, $this->logger);
    }

    public function test_it_evaluates_product_models(): void
    {
        $productModelIdCollection = ProductModelIdCollection::fromStrings(['123', '321']);
        $this->evaluatePendingProductModelCriteria->expects($this->once())->method('evaluateAllCriteria')->with($productModelIdCollection);
        $this->consolidateProductScores->expects($this->once())->method('consolidate')->with($productModelIdCollection);
        $this->eventDispatcher->method('dispatch')->with($this->callback(static fn ($event) => $event instanceof ProductModelsEvaluated && $event->getProductModelIds() === $productModelIdCollection));
        $this->sut->__invoke($productModelIdCollection);
    }
}
