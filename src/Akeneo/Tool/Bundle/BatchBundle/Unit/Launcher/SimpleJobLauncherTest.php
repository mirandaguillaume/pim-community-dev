<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchBundle\Launcher;

use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobParametersValidator;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\BatchBundle\Launcher\SimpleJobLauncher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class SimpleJobLauncherTest extends TestCase
{
    private JobRepositoryInterface|MockObject $jobRepository;
    private JobParametersFactory|MockObject $jobParametersFactory;
    private JobRegistry|MockObject $jobRegistry;
    private JobParametersValidator|MockObject $jobParametersValidator;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private SimpleJobLauncher $sut;

    protected function setUp(): void
    {
        $this->jobRepository = $this->createMock(JobRepositoryInterface::class);
        $this->jobParametersFactory = $this->createMock(JobParametersFactory::class);
        $this->jobRegistry = $this->createMock(JobRegistry::class);
        $this->jobParametersValidator = $this->createMock(JobParametersValidator::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->sut = new SimpleJobLauncher($this->jobRepository, $this->jobParametersFactory, $this->jobRegistry, $this->jobParametersValidator, $this->eventDispatcher, '/', 'prod', 'var/logs');
    }

    public function test_it_is_a_job_launcher(): void
    {
        $this->assertInstanceOf(JobLauncherInterface::class, $this->sut);
    }

    public function test_it_launches_a_job(): void
    {
        $jobInstance = $this->createMock(JobInstance::class);
        $user = $this->createMock(UserInterface::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $job = $this->createMock(Job::class);
        $jobParameters = $this->createMock(JobParameters::class);
        $constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);

        $jobInstance->method('getJobName')->willReturn('job_instance_name');
        $jobInstance->method('getCode')->willReturn('job_instance_code');
        $jobInstance->method('getRawParameters')->willReturn(['foo' => 'bar']);
        $user->method('getUserIdentifier')->willReturn('julia');
        $jobExecution->method('getId')->willReturn(1);
        $constraintViolationList->method('count')->willReturn(0);
        $this->jobRegistry->method('get')->with('job_instance_name')->willReturn($job);
        $this->jobParametersFactory->method('create')->with($job, ['foo' => 'bar', 'baz' => 'foz'])->willReturn($jobParameters);
        $this->jobParametersValidator->method('validate')->with($job, $jobParameters, ['Default', 'Execution'])->willReturn($constraintViolationList);
        $this->jobRepository->method('createJobExecution')->with($job, $jobInstance, $jobParameters)->willReturn($jobExecution);
        $jobExecution->expects($this->once())->method('setUser')->with('julia');
        $this->jobRepository->expects($this->once())->method('updateJobExecution')->with($jobExecution);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($this->isInstanceOf(JobExecutionEvent::class), EventInterface::JOB_EXECUTION_CREATED);
        $this->assertSame($jobExecution, $this->sut->launch($jobInstance, $user, ['baz' => 'foz']));
    }

    public function test_it_throws_an_exception_if_job_parameters_are_invalid(): void
    {
        $jobInstance = $this->createMock(JobInstance::class);
        $user = $this->createMock(UserInterface::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $job = $this->createMock(Job::class);
        $jobParameters = $this->createMock(JobParameters::class);
        $constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);
        $constraintViolation = $this->createMock(ConstraintViolation::class);

        $jobInstance->method('getJobName')->willReturn('job_instance_name');
        $jobInstance->method('getCode')->willReturn('job_instance_code');
        $jobInstance->method('getRawParameters')->willReturn(['foo' => 'bar']);
        $user->method('getUserIdentifier')->willReturn('julia');
        $jobExecution->method('getId')->willReturn(1);
        $constraintViolationList->method('count')->willReturn(1);
        $constraintViolationList->expects($this->once())->method('rewind');
        $constraintViolationList->method('valid')->willReturn(true, false);
        $constraintViolationList->expects($this->once())->method('next');
        $constraintViolationList->method('current')->willReturn($constraintViolation);
        $constraintViolation->method('__toString')->willReturn('error');
        $this->jobRegistry->method('get')->with('job_instance_name')->willReturn($job);
        $job->method('getName')->willReturn('job_name');
        $this->jobParametersFactory->method('create')->with($job, ['foo' => 'bar'])->willReturn($jobParameters);
        $this->jobParametersValidator->method('validate')->with($job, $jobParameters, ['Default', 'Execution'])->willReturn($constraintViolationList);
        $jobParameters->method('all')->willReturn([]);
        $this->eventDispatcher->expects($this->never())->method('dispatch')->with($this->isInstanceOf(JobExecutionEvent::class), EventInterface::JOB_EXECUTION_CREATED);
        $this->expectException(new \RuntimeException('Job instance "job_instance_code" running the job "job_name" with parameters "[]" is invalid because of "' . PHP_EOL . '  - error"'));
        $this->sut->launch($jobInstance, $user, []);
    }
}
