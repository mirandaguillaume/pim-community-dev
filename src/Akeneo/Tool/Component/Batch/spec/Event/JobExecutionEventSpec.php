<?php

namespace spec\Akeneo\Tool\Component\Batch\Event;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PhpSpec\ObjectBehavior;

class JobExecutionEventSpec extends ObjectBehavior
{
    public function let(JobExecution $jobExecution)
    {
        $this->beConstructedWith($jobExecution);
    }

    public function it_provides_the_job_execution($jobExecution)
    {
        $this->getJobExecution()->shouldReturn($jobExecution);
    }
}
