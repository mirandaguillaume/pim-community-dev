<?php

namespace spec\Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class ResetProcessedItemsBatchSubscriberSpec extends ObjectBehavior
{
    public function it_resets_processed_items_batch_saved_in_the_execution_context(
        StepExecutionEvent $event,
        StepExecution $stepExecution,
        ExecutionContext $executionContext
    ) {
        $event->getStepExecution()->willReturn($stepExecution);
        $stepExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->remove('processed_items_batch')->shouldBeCalled();

        $this->resetProcessedItemsBatch($event);
    }
}
