<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchBundle\Manager;

use Akeneo\Tool\Bundle\BatchBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JobExecutionManagerTest extends TestCase
{
    private EntityManager|MockObject $entityManager;
    private JobExecutionManager $sut;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->sut = new JobExecutionManager($this->entityManager);
    }

    public function test_it_checks_a_job_execution_is_running(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $status = $this->createMock(BatchStatus::class);
        $exitStatus = $this->createMock(ExitStatus::class);

        $jobExecution->method('getStatus')->willReturn($status);
        $jobExecution->method('getExitStatus')->willReturn($exitStatus);
        $status->method('getValue')->willReturn(BatchStatus::STARTING);
        $exitStatus->method('getExitCode')->willReturn(ExitStatus::EXECUTING);
        $this->assertSame(true, $this->sut->checkRunningStatus($jobExecution));
    }

    public function test_it_checks_a_job_execution_is_not_running(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $status = $this->createMock(BatchStatus::class);
        $exitStatus = $this->createMock(ExitStatus::class);

        $jobExecution->method('getStatus')->willReturn($status);
        $jobExecution->method('getExitStatus')->willReturn($exitStatus);
        $status->method('getValue')->willReturn(BatchStatus::STARTING);
        $exitStatus->method('getExitCode')->willReturn(ExitStatus::STOPPED);
        $this->assertSame(true, $this->sut->checkRunningStatus($jobExecution));
    }

    public function test_it_marks_a_job_execution_as_failed(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);

        $jobExecution->expects($this->once())->method('setStatus')->with($this->anything());
        $jobExecution->expects($this->once())->method('setExitStatus')->with($this->anything());
        $jobExecution->expects($this->once())->method('setEndTime')->with($this->anything());
        $jobExecution->expects($this->once())->method('addFailureException')->with($this->anything());
        $this->entityManager->expects($this->once())->method('persist')->with($jobExecution);
        $this->entityManager->expects($this->once())->method('flush');
        $this->sut->markAsFailed($jobExecution);
    }
}
