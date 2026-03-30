<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints;

use Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints\JobInstance as JobInstanceConstraint;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Job\UndefinedJobException;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints\JobInstanceValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class JobInstanceValidatorTest extends TestCase
{
    private JobRegistry|MockObject $jobRegistry;
    private ExecutionContextInterface|MockObject $context;
    private JobInstanceValidator $sut;

    protected function setUp(): void
    {
        $this->jobRegistry = $this->createMock(JobRegistry::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new JobInstanceValidator($this->jobRegistry);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_a_constraint_validator(): void
    {
        $this->assertInstanceOf('\\' . \Symfony\Component\Validator\ConstraintValidator::class, $this->sut);
    }

    public function test_it_validates_only_job_instance(): void
    {
        $constraint = $this->createMock(Constraint::class);

        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate($object, $constraint);
    }

    public function test_it_validates_that_a_job_instance_has_a_known_type(): void
    {
        $constraint = $this->createMock(Constraint::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(JobInterface::class);

        $jobInstance->method('getJobName')->willReturn('my_job_name');
        $this->jobRegistry->method('get')->with('my_job_name')->willReturn($job);
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate($jobInstance, $constraint);
    }

    public function test_it_adds_a_violation_if_job_instance_has_an_unknown_type(): void
    {
        $constraint = $this->createMock(JobInstance::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);

        $jobInstance->method('getJobName')->willReturn(null);
        $this->jobRegistry->method('get')->with(null)->willThrowException(new UndefinedJobException('The job "" is not registered'));
        $jobInstance->method('getType')->willReturn('import');
        $this->context->expects($this->once())->method('buildViolation')->with(
            $constraint->message,
            ['%job_type%' => 'import']
        )->willReturn($violation);
        $violation->expects($this->once())->method('atPath')->with($constraint->property)->willReturn($violation);
        $violation->expects($this->once())->method('addViolation');
        $this->sut->validate($jobInstance, $constraint);
    }
}
