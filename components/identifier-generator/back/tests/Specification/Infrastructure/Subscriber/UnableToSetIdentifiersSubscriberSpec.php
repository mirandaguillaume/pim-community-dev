<?php

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Subscriber;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToSetIdentifierException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\Error;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\ErrorList;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Event\UnableToSetIdentifierEvent;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Model\Warning;
use PhpSpec\ObjectBehavior;

final class UnableToSetIdentifiersSubscriberSpec extends ObjectBehavior
{
    function let(JobRepositoryInterface $jobRepository)
    {
        $this->beConstructedWith($jobRepository);
    }

    public function it_should_write_warnings(
        JobRepositoryInterface $jobRepository,
        StepExecutionEvent $stepExecutionEvent,
        StepExecution $stepExecution,
    ): void {
        $stepExecutionEvent->getStepExecution()->shouldBeCalled()->willReturn($stepExecution);
        $jobRepository->addWarnings($stepExecution, [
            new Warning(
                $stepExecution->getWrappedObject(),
                "Your product has been saved but your identifier could not be generated:\nError 1",
                [],
                ['sku' => 'AKN-2000']
            ),
            new Warning(
                $stepExecution->getWrappedObject(),
                "Your product has been saved but your identifier could not be generated:\nError 2",
                [],
                ['ean' => 'TOTO-2012']
            )
        ])->shouldBeCalled();

        $this->storeEvent(new UnableToSetIdentifierEvent(new UnableToSetIdentifierException(
            'AKN-2000',
            'sku', new ErrorList([new Error('Error 1')])
        )));
        $this->storeEvent(new UnableToSetIdentifierEvent(new UnableToSetIdentifierException(
            'TOTO-2012',
            'ean', new ErrorList([new Error('Error 2')])
        )));
        $this->writeWarnings($stepExecutionEvent);
    }
}
