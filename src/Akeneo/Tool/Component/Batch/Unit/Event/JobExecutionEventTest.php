<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Event;

use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JobExecutionEventTest extends TestCase
{
    private JobExecution|MockObject $jobExecution;
    private JobExecutionEvent $sut;

    protected function setUp(): void
    {
        $this->jobExecution = $this->createMock(JobExecution::class);
        $this->sut = new JobExecutionEvent($this->jobExecution);
    }

    public function test_it_provides_the_job_execution(): void
    {
        $this->assertSame($this->jobExecution, $this->sut->getJobExecution());
    }
}
