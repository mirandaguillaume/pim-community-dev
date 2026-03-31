<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Bundle\BatchBundle\EventListener\LoadJobParametersListener;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoadJobParametersListenerTest extends TestCase
{
    private JobParametersFactory|MockObject $jobParametersFactory;
    private LoadJobParametersListener $sut;

    protected function setUp(): void
    {
        $this->jobParametersFactory = $this->createMock(JobParametersFactory::class);
        $this->sut = new LoadJobParametersListener($this->jobParametersFactory);
    }

    public function test_it_sets_job_parameters_into_job_execution(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $event = $this->createMock(LifecycleEventArgs::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->jobParametersFactory->method('createFromRawParameters')->with($jobExecution)->willReturn($jobParameters);
        $jobExecution->expects($this->once())->method('setJobParameters')->with($jobParameters);
        $this->sut->postLoad($jobExecution, $event);
    }
}
