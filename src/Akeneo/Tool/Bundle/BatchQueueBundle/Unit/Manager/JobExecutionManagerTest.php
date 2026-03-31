<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchQueueBundle\Manager;

use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use DateInterval;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JobExecutionManagerTest extends TestCase
{
    private Connection|MockObject $connection;
    private JobExecutionManager $sut;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->sut = new JobExecutionManager($this->connection);
    }

    public function test_it_does_not_modify_status_when_a_job_execution_has_not_been_launched(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $status = $this->createMock(BatchStatus::class);
        $exitStatus = $this->createMock(ExitStatus::class);

        $jobExecution->method('getStatus')->willReturn($status);
        $jobExecution->method('getExitStatus')->willReturn($exitStatus);
        $status->method('getValue')->willReturn(BatchStatus::STARTING);
        $exitStatus->method('isRunning')->willReturn(false);
        $jobExecution->expects($this->never())->method('setStatus')->with($this->anything());
        $jobExecution->expects($this->never())->method('setExitStatus')->with($this->anything());
        $this->sut->resolveJobExecutionStatus($jobExecution);
    }

    public function test_it_does_not_modify_status_when_a_job_execution_is_completed(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $status = $this->createMock(BatchStatus::class);
        $exitStatus = $this->createMock(ExitStatus::class);

        $jobExecution->method('getStatus')->willReturn($status);
        $jobExecution->method('getExitStatus')->willReturn($exitStatus);
        $jobExecution->method('isStopping')->willReturn(false);
        $status->method('getValue')->willReturn(BatchStatus::COMPLETED);
        $exitStatus->method('isRunning')->willReturn(false);
        $jobExecution->expects($this->never())->method('setStatus')->with($this->anything());
        $jobExecution->expects($this->never())->method('setExitStatus')->with($this->anything());
        $this->sut->resolveJobExecutionStatus($jobExecution);
    }

    public function test_it_resolves_job_execution_status_when_job_execution_failed_but_has_still_a_running_status(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $status = $this->createMock(BatchStatus::class);
        $exitStatus = $this->createMock(ExitStatus::class);

        $healthCheck = new \DateTime('now', new \DateTimeZone('UTC'));
        $healthCheck->add(DateInterval::createFromDateString('-100 seconds'));
        $jobExecution->method('getStatus')->willReturn($status);
        $jobExecution->method('isStopping')->willReturn(false);
        $jobExecution->method('getExitStatus')->willReturn($exitStatus);
        $jobExecution->method('getHealthCheckTime')->willReturn($healthCheck);
        $status->method('getValue')->willReturn(BatchStatus::STARTED);
        $exitStatus->method('isRunning')->willReturn(true);
        $jobExecution->expects($this->once())->method('setStatus')->with(new BatchStatus(BatchStatus::FAILED));
        $jobExecution->expects($this->once())->method('setExitStatus')->with(new ExitStatus(ExitStatus::FAILED));
        $this->sut->resolveJobExecutionStatus($jobExecution);
    }

    public function test_it_resolves_job_execution_status_when_job_execution_failed_but_has_still_a_stopping_status(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $status = $this->createMock(BatchStatus::class);
        $exitStatus = $this->createMock(ExitStatus::class);

        $healthCheck = new \DateTime('now', new \DateTimeZone('UTC'));
        $healthCheck->add(DateInterval::createFromDateString('-100 seconds'));
        $jobExecution->method('getStatus')->willReturn($status);
        $jobExecution->method('isStopping')->willReturn(true);
        $jobExecution->method('getExitStatus')->willReturn($exitStatus);
        $jobExecution->method('getHealthCheckTime')->willReturn($healthCheck);
        $status->method('getValue')->willReturn(BatchStatus::STOPPING);
        $exitStatus->method('isRunning')->willReturn(false);
        $jobExecution->expects($this->once())->method('setStatus')->with(new BatchStatus(BatchStatus::FAILED));
        $jobExecution->expects($this->once())->method('setExitStatus')->with(new ExitStatus(ExitStatus::FAILED));
        $this->sut->resolveJobExecutionStatus($jobExecution);
    }

    public function test_it_does_not_modify_status_when_job_execution_health_check_is_null(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $status = $this->createMock(BatchStatus::class);
        $exitStatus = $this->createMock(ExitStatus::class);

        $jobExecution->method('getStatus')->willReturn($status);
        $jobExecution->method('isStopping')->willReturn(false);
        $jobExecution->method('getExitStatus')->willReturn($exitStatus);
        $jobExecution->method('getHealthCheckTime')->willReturn(null);
        $status->method('getValue')->willReturn(BatchStatus::STARTED);
        $exitStatus->method('isRunning')->willReturn(true);
        $jobExecution->expects($this->never())->method('setStatus')->with($this->isInstanceOf(BatchStatus::class));
        $jobExecution->expects($this->never())->method('setExitStatus')->with($this->isInstanceOf(ExitStatus::class));
        $this->sut->resolveJobExecutionStatus($jobExecution);
    }

    public function test_it_gets_exit_status(): void
    {
        $stmt = $this->createMock(Statement::class);
        $result = $this->createMock(Result::class);

        $this->connection->method('prepare')->with($this->isType('string'))->willReturn($stmt);
        $stmt->expects($this->once())->method('bindValue')->with('id', 1);
        $stmt->expects($this->once())->method('executeQuery')->willReturn($result);
        $result->method('fetchAssociative')->willReturn(['exit_code' => 'COMPLETED']);
        $this->assertEquals(new ExitStatus('COMPLETED'), $this->sut->getExitStatus(1));
    }

    public function test_it_marks_as_failed(): void
    {
        $stmt = $this->createMock(Statement::class);

        $this->connection->method('prepare')->with($this->isType('string'))->willReturn($stmt);
        $stmt->expects($this->once())->method('bindValue')->with('id', 1);
        $stmt->expects($this->once())->method('bindValue')->with('status', BatchStatus::FAILED);
        $stmt->expects($this->once())->method('bindValue')->with('exit_code', ExitStatus::FAILED);
        $stmt->expects($this->once())->method('bindValue')->with(/* TODO: convert Argument matcher */ 'updated_time', Argument::type(\DateTime::class), Types::DATETIME_MUTABLE);
        $stmt->expects($this->once())->method('executeStatement');
        $this->sut->markAsFailed(1);
    }

    public function test_it_updates_healthcheck(): void
    {
        $stmt = $this->createMock(Statement::class);

        $this->connection->method('prepare')->with($this->isType('string'))->willReturn($stmt);
        $stmt->expects($this->once())->method('bindValue')->with('id', 1);
        $stmt->expects($this->once())->method('bindValue')->with(/* TODO: convert Argument matcher */ 'health_check_time', Argument::type(\DateTime::class), Types::DATETIME_MUTABLE);
        $stmt->expects($this->once())->method('bindValue')->with(/* TODO: convert Argument matcher */ 'updated_time', Argument::type(\DateTime::class), Types::DATETIME_MUTABLE);
        $stmt->expects($this->once())->method('executeStatement');
        $this->sut->updateHealthCheck(1);
    }
}
