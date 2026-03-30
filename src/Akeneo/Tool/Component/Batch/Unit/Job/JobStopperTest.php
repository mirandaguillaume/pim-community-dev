<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Query\GetJobExecutionStatusInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use spec\Akeneo\Tool\Component\Batch\Job\JobStopper;

class JobStopperTest extends TestCase
{
    private JobRepositoryInterface|MockObject $jobRepository;
    private GetJobExecutionStatusInterface|MockObject $getJobExecutionStatus;
    private LoggerInterface|MockObject $logger;
    private JobStopper $sut;

    protected function setUp(): void
    {
        $this->jobRepository = $this->createMock(JobRepositoryInterface::class);
        $this->getJobExecutionStatus = $this->createMock(GetJobExecutionStatusInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sut = new JobStopper(
            $this->jobRepository,
            $this->getJobExecutionStatus,
            $this->logger,
        );
    }

    public function test_it_tells_if_a_job_is_stopping(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $batchStatus = $this->createMock(BatchStatus::class);

        $jobExecution->method('getId')->willReturn(3);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $batchStatus->method('isStopping')->willReturn(true);
        $this->getJobExecutionStatus->method('getByJobExecutionId')->with(3)->willReturn($batchStatus);
        $this->assertSame(true, $this->sut->isStopping($stepExecution));
    }

    public function test_it_tells_if_a_job_is_not_stopping(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $batchStatus = $this->createMock(BatchStatus::class);

        $jobExecution->method('getId')->willReturn(5);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $batchStatus->method('isStopping')->willReturn(false);
        $this->getJobExecutionStatus->method('getByJobExecutionId')->with(5)->willReturn($batchStatus);
        $this->assertSame(false, $this->sut->isStopping($stepExecution));
    }

    public function test_it_stops_a_job(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);

        $stepExecution->expects($this->once())->method('setExitStatus')->with(new ExitStatus(ExitStatus::STOPPED));
        $stepExecution->expects($this->once())->method('setStatus')->with(new BatchStatus(BatchStatus::STOPPED));
        $this->sut->stop($stepExecution);
    }

    public function test_it_tells_if_a_job_is_pausing(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $batchStatus = $this->createMock(BatchStatus::class);

        $jobExecution->method('getId')->willReturn(3);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $batchStatus->method('isPausing')->willReturn(true);
        $this->getJobExecutionStatus->method('getByJobExecutionId')->with(3)->willReturn($batchStatus);
        $this->assertSame(true, $this->sut->isPausing($stepExecution));
    }

    public function test_it_pauses_a_job(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);

        $jobInstance->method('getCode')->willReturn('my_job');
        $jobExecution->method('getId')->willReturn(99);
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getId')->willReturn(98);
        $stepExecution->expects($this->once())->method('getStepName');
        $stepExecution->expects($this->once())->method('setStatus')->with(new BatchStatus(BatchStatus::PAUSED));
        $stepExecution->method('getCurrentState')->willReturn(['file_path' => 'file.csv']);
        $stepExecution->expects($this->once())->method('setCurrentState')->with(['file_path' => 'file.csv', 'position' => 1]);
        $this->sut->pause($stepExecution, ['position' => 1]);
    }
}
