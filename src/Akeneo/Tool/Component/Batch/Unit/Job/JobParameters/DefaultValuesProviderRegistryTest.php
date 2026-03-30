<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Job\JobParameters;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\NonExistingServiceException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderRegistry;

class DefaultValuesProviderRegistryTest extends TestCase
{
    private DefaultValuesProviderRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new DefaultValuesProviderRegistry();
    }

    public function test_it_gets_the_registered_provider_for_a_job(): void
    {
        $provider = $this->createMock(DefaultValuesProviderInterface::class);
        $job = $this->createMock(JobInterface::class);

        $this->sut->register($provider, $job);
        $provider->method('supports')->with($job)->willReturn(true);
        $this->assertSame($provider, $this->sut->get($job));
    }

    public function test_it_throws_an_exception_when_there_is_no_registered_provider(): void
    {
        $job = $this->createMock(JobInterface::class);

        $job->method('getName')->willReturn('myname');
        $this->expectException(NonExistingServiceException::class);

        $this->expectExceptionMessage('No default values provider has been defined for the Job "myname"');
        $this->sut->get($job);
    }
}
