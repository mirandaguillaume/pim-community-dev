<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchBundle\Job;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceFactory;

class JobInstanceFactoryTest extends TestCase
{
    private JobInstanceFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new JobInstanceFactory(self::TESTED_CLASS);
    }

    public function test_it_creates_job_instances(): void
    {
        $this->sut->createJobInstance()->shouldReturnAnInstanceOf(self::TESTED_CLASS);
    }

    public function test_it_creates_job_instances_with_defined_type(): void
    {
        $jobInstance = $this->createJobInstance('foo');
        $jobInstance->shouldBeAnInstanceOf(self::TESTED_CLASS);
        $jobInstance->getType()->shouldReturn('foo');
        $jobInstance->getJobName()->shouldReturn(null);
        $jobInstance->getConnector()->shouldReturn(null);
    }
}
