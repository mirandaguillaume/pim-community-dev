<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderRegistry;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JobParametersFactoryTest extends TestCase
{
    private DefaultValuesProviderRegistry|MockObject $registry;
    private JobParametersFactory $sut;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(DefaultValuesProviderRegistry::class);
        $this->sut = new JobParametersFactory($this->registry, JobParameters::class);
    }

    public function test_it_creates_a_job_parameters_with_default_values(): void
    {
        $provider = $this->createMock(DefaultValuesProviderInterface::class);
        $job = $this->createMock(JobInterface::class);

        $job->method('getName')->willReturn('foo');
        $this->registry->method('get')->with($job)->willReturn($provider);
        $provider->method('getDefaultValues')->willReturn(['my_default_field' => 'my default value']);
        $jobParameters = $this->sut->create($job, ['my_defined_field' => 'my defined value']);
        $this->assertInstanceOf(JobParameters::class, $jobParameters);
        $this->assertSame(
            [
                'my_default_field' => 'my default value',
                'my_defined_field' => 'my defined value',
            ],
            $jobParameters->all()
        );
    }

    public function test_it_creates_a_job_parameters_from_raw_parameters_of_a_job_execution(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);

        $jobExecution->method('getRawParameters')->willReturn(['foo' => 'baz']);
        $jobParameters = $this->sut->createFromRawParameters($jobExecution);
        $this->assertInstanceOf(JobParameters::class, $jobParameters);
        $this->assertSame(['foo' => 'baz'], $jobParameters->all());
    }
}
