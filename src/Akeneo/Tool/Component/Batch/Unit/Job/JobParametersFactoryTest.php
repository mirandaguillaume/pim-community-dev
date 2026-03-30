<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Batch\Job\JobParametersFactory;

class JobParametersFactoryTest extends TestCase
{
    private DefaultValuesProviderRegistry|MockObject $registry;
    private JobParametersFactory $sut;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(DefaultValuesProviderRegistry::class);
        $this->sut = new JobParametersFactory($this->registry, self::INSTANCE_CLASS);
    }

    public function test_it_creates_a_job_parameters_with_default_values(): void
    {
        $provider = $this->createMock(DefaultValuesProviderInterface::class);
        $job = $this->createMock(JobInterface::class);

        $job->method('getName')->willReturn('foo');
        $this->registry->method('get')->with($job)->willReturn($provider);
        $provider->method('getDefaultValues')->willReturn(['my_default_field' => 'my default value']);
        $jobParameters = $this->create($job, ['my_defined_field' => 'my defined value']);
        $jobParameters->shouldReturnAnInstanceOf(JobParameters::class);
        $jobParameters->all()->shouldBe(
            [
                        'my_default_field' => 'my default value',
                        'my_defined_field' => 'my defined value',
                    ]
        );
    }

    public function test_it_creates_a_job_parameters_from_raw_parameters_of_a_job_execution(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);

        $jobExecution->method('getRawParameters');
        $jobParameters = $this->createFromRawParameters($jobExecution);
        $jobParameters->shouldReturnAnInstanceOf(JobParameters::class);
        $jobParameters->all()->shouldBe(['foo' => 'baz']);
    }
}
