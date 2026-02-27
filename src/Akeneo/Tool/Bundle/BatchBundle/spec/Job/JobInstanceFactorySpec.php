<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\Job;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;

class JobInstanceFactorySpec extends ObjectBehavior
{
    final public const TESTED_CLASS = JobInstance::class;

    public function let()
    {
        $this->beConstructedWith(self::TESTED_CLASS);
    }

    public function it_creates_job_instances()
    {
        $this->createJobInstance()->shouldReturnAnInstanceOf(self::TESTED_CLASS);
    }

    public function it_creates_job_instances_with_defined_type()
    {
        $jobInstance = $this->createJobInstance('foo');
        $jobInstance->shouldBeAnInstanceOf(self::TESTED_CLASS);
        $jobInstance->getType()->shouldReturn('foo');
        $jobInstance->getJobName()->shouldReturn(null);
        $jobInstance->getConnector()->shouldReturn(null);
    }
}
