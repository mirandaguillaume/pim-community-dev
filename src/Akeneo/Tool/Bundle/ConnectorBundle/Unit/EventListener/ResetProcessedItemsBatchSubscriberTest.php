<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\ResetProcessedItemsBatchSubscriber;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResetProcessedItemsBatchSubscriberTest extends TestCase
{
    private ResetProcessedItemsBatchSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new ResetProcessedItemsBatchSubscriber();
    }

    public function test_it_resets_processed_items_batch_saved_in_the_execution_context(): void
    {
        $event = $this->createMock(StepExecutionEvent::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $executionContext = $this->createMock(ExecutionContext::class);

        $event->method('getStepExecution')->willReturn($stepExecution);
        $stepExecution->method('getExecutionContext')->willReturn($executionContext);
        $executionContext->expects($this->once())->method('remove')->with('processed_items_batch');
        $this->sut->resetProcessedItemsBatch($event);
    }
}
