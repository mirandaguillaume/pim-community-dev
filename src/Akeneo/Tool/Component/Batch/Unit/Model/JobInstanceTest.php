<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Model;

use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JobInstanceTest extends TestCase
{
    private JobInstance $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_properly_instanciated(): void
    {
        $this->sut = new JobInstance('connector', 'type', 'job_name');
        $this->assertSame('connector', $this->sut->getConnector());
        $this->assertSame('type', $this->sut->getType());
        $this->assertSame('job_name', $this->sut->getJobName());
    }

    public function test_it_is_cloneable(): void
    {
        $this->sut = new JobInstance('connector', 'type', 'job_name');
        $jobExecution = $this->createMock(JobExecution::class);

        $this->sut->addJobExecution($jobExecution);
        $clone = clone $this->sut;
        $this->assertInstanceOf(JobInstance::class, $clone);
        $this->assertCount(1, $clone->getJobExecutions());
        $this->assertNull($clone->getId());
    }

    public function test_it_throws_logic_exception_when_changes_job_name(): void
    {
        $this->sut = new JobInstance('connector', 'type', 'old_job_name');
        $this->expectException(\LogicException::class);

        $this->expectExceptionMessage('Job name already set in JobInstance');
        $this->sut->setJobName('new_job_name');
    }

    public function test_it_throws_logic_exception_when_changes_connector(): void
    {
        $this->sut = new JobInstance('oldconnector', 'type', 'job_name');
        $this->expectException(\LogicException::class);

        $this->expectExceptionMessage('Connector already set in JobInstance');
        $this->sut->setConnector('newconnector');
    }
}
