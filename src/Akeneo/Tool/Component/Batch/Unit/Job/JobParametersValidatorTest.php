<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Batch\Job\JobParametersValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class JobParametersValidatorTest extends TestCase
{
    private ValidatorInterface|MockObject $validator;
    private ConstraintCollectionProviderRegistry|MockObject $registry;
    private JobParametersValidator $sut;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->registry = $this->createMock(ConstraintCollectionProviderRegistry::class);
        $this->sut = new JobParametersValidator($this->validator, $this->registry);
    }

    public function test_it_validates_a_job_parameters(): void
    {
        $provider = $this->createMock(ConstraintCollectionProviderInterface::class);
        $job = $this->createMock(JobInterface::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->registry->method('get')->with($job)->willReturn($provider);
        $provider->method('getConstraintCollection')->willReturn(['fields' => 'my constraints']);
        $jobParameters->method('all')->willReturn(['my params']);
        $this->validator->expects($this->once())->method('validate')->with(['my params'], ['fields' => 'my constraints'], ['MyValidationGroup', 'Default']);
        $this->sut->validate($job, $jobParameters, ['MyValidationGroup', 'Default']);
    }
}
