<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchBundle\Job;

use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceFactory;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PHPUnit\Framework\TestCase;

class JobInstanceFactoryTest extends TestCase
{
    private JobInstanceFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new JobInstanceFactory(JobInstance::class);
    }

    public function test_it_creates_job_instances(): void
    {
        $this->assertInstanceOf(JobInstance::class, $this->sut->createJobInstance());
    }

    public function test_it_creates_job_instances_with_defined_type(): void
    {
        $jobInstance = $this->sut->createJobInstance('foo');
        $this->assertInstanceOf(JobInstance::class, $jobInstance);
        $this->assertSame('foo', $jobInstance->getType());
    }
}
