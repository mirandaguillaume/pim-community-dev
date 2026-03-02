<?php

namespace spec\Akeneo\Tool\Component\Batch\Event;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class StepExecutionEventSpec extends ObjectBehavior
{
    public function let(StepExecution $stepExecution)
    {
        $this->beConstructedWith($stepExecution);
    }

    public function it_provides_the_step_execution($stepExecution)
    {
        $this->getStepExecution()->shouldReturn($stepExecution);
    }
}
