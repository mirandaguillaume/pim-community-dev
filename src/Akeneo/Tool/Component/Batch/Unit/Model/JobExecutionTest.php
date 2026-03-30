<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Model;

use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JobExecutionTest extends TestCase
{
    private JobExecution $sut;

    protected function setUp(): void
    {
        $this->sut = new JobExecution();
    }

    public function test_it_is_properly_instanciated(): void
    {
        $this->assertInstanceOf(BatchStatus::class, $this->sut->getStatus());
        $this->assertSame(BatchStatus::STARTING, $this->sut->getStatus()->getValue());
        $this->assertInstanceOf(ExitStatus::class, $this->sut->getExitStatus());
        $this->assertSame(ExitStatus::UNKNOWN, $this->sut->getExitStatus()->getExitCode());
        $this->assertInstanceOf(ExecutionContext::class, $this->sut->getExecutionContext());
        $this->assertInstanceOf(\Doctrine\Common\Collections\ArrayCollection::class, $this->sut->getStepExecutions());
        $this->sut->getStepExecutions()->shouldBeEmpty();
        $this->assertInstanceOf('\DateTime', $this->sut->getCreateTime());
        $this->assertCount(0, $this->sut->getFailureExceptions());
        $this->assertCount(0, $this->sut->getRawParameters());
        $this->assertNull($this->sut->getJobParameters());
        $this->assertNull($this->sut->getHealthCheckTime());
        $this->assertSame(false, $this->sut->isStoppable());
        $this->assertSame(1, $this->sut->getStepCount());
    }

    public function test_it_is_cloneable(): void
    {
        $executionContext = $this->createMock(ExecutionContext::class);
        $stepExecution1 = $this->createMock(StepExecution::class);
        $stepExecution2 = $this->createMock(StepExecution::class);

        $this->sut->setExecutionContext($executionContext);
        $this->sut->addStepExecution($stepExecution1);
        $this->sut->addStepExecution($stepExecution2);
        $clone = clone $this;
        $clone->shouldBeAnInstanceOf(JobExecution::class);
        $clone->getExecutionContext()->shouldBeAnInstanceOf(ExecutionContext::class);
        $clone->getStepExecutions()->shouldBeAnInstanceOf(ArrayCollection::class);
        $clone->getStepExecutions()->shouldHaveCount(2);
    }

    public function test_it_upgrades_status(): void
    {
        $this->assertInstanceOf(BatchStatus::class, $this->sut->getStatus());
        $this->assertSame(BatchStatus::STARTING, $this->sut->getStatus()->getValue());
        $this->assertInstanceOf(JobExecution::class, $this->sut->upgradeStatus(BatchStatus::COMPLETED));
        $this->assertInstanceOf(BatchStatus::class, $this->sut->getStatus());
        $this->assertSame(BatchStatus::COMPLETED, $this->sut->getStatus()->getValue());
    }

    public function test_it_sets_exist_status(): void
    {
        $exitStatus = new ExitStatus(ExitStatus::NOOP, "My description");
        $this->assertSame($this, $this->sut->setExitStatus($exitStatus));
    }

    public function test_it_creates_step_execution(): void
    {
        $newStep = $this->createStepExecution('myStepName');
        $newStep->shouldBeAnInstanceOf(StepExecution::class);
        $newStep->getStepName()->shouldReturn('myStepName');
    }

    public function test_it_adds_step_execution(): void
    {
        $stepExecution1 = $this->createMock(StepExecution::class);

        $this->assertCount(0, $this->sut->getStepExecutions());
        $this->sut->addStepExecution($stepExecution1);
        $this->assertCount(1, $this->sut->getStepExecutions());
    }

    public function test_it_indicates_if_running(): void
    {
        $completedStatus = $this->createMock(BatchStatus::class);

        $this->assertSame(true, $this->sut->isRunning());
        $this->sut->setStatus($completedStatus);
        $completedStatus->method('getValue')->willReturn(BatchStatus::COMPLETED);
        $this->assertSame(false, $this->sut->isRunning());
    }

    public function test_it_indicates_if_stopping(): void
    {
        $stoppingStatus = $this->createMock(BatchStatus::class);

        $this->assertSame(false, $this->sut->isStopping());
        $stoppingStatus->method('getValue')->willReturn(BatchStatus::STOPPING);
        $this->sut->setStatus($stoppingStatus);
        $this->assertSame(true, $this->sut->isStopping());
    }

    public function test_it_stops(): void
    {
        $stepExecution1 = $this->createMock(StepExecution::class);

        $this->sut->addStepExecution($stepExecution1);
        $stepExecution1->expects($this->once())->method('setTerminateOnly');
        $this->assertInstanceOf(JobExecution::class, $this->sut->stop());
    }

    public function test_it_adds_a_failure_exception(): void
    {
        $exception = new \Exception('my msg');
        $this->assertSame($this, $this->sut->addFailureException($exception));
        $this->assertCount(1, $this->sut->getFailureExceptions());
    }

    public function test_it_provides_aggregated_step_failure_exceptions(): void
    {
        $stepExecution1 = $this->createMock(StepExecution::class);

        $stepExecution1->method('getFailureExceptions')->willReturn(['one structured exception']);
        $this->sut->addStepExecution($stepExecution1);
        $this->assertCount(1, $this->sut->getAllFailureExceptions());
    }

    public function test_it_sets_job_instance(): void
    {
        $jobInstance = $this->createMock(JobInstance::class);

        $jobInstance->expects($this->once())->method('addJobExecution')->with($this);
        $this->sut->setJobInstance($jobInstance);
    }

    public function test_it_provides_the_job_instance_label(): void
    {
        $jobInstance = $this->createMock(JobInstance::class);

        $this->sut->setJobInstance($jobInstance);
        $jobInstance->method('getLabel')->willReturn('my label');
        $this->assertSame('my label', $this->sut->getLabel());
    }

    public function test_it_sets_raw_parameters_when_setting_job_parameters(): void
    {
        $jobParameters = $this->createMock(JobParameters::class);

        $jobParameters->method('all')->willReturn(['foo' => 'baz']);
        $this->sut->setJobParameters($jobParameters);
        $this->assertSame($jobParameters, $this->sut->getJobParameters());
        $this->assertSame(['foo' => 'baz'], $this->sut->getRawParameters());
    }

    public function test_it_sets_health_check_time(): void
    {
        $datetime = new \DateTime();
        $this->sut->setHealthCheckTime($datetime);
        $this->assertSame($datetime, $this->sut->getHealthCheckTime());
    }

    public function test_it_is_displayable(): void
    {
        $this->assertSame('startTime=, endTime=, updatedTime=, status=2, exitStatus=[UNKNOWN] , exitDescription=[], job=[]', $this->sut->__toString());
    }

    public function test_it_can_count_its_steps(): void
    {
        $this->sut->setStepCount(12);
        $this->assertSame(12, $this->sut->getStepCount());
    }
}
