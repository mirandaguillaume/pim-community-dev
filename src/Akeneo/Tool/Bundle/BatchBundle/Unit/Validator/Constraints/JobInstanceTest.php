<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints;

use Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints\JobInstance;
use PHPUnit\Framework\TestCase;

class JobInstanceTest extends TestCase
{
    private JobInstance $sut;

    protected function setUp(): void
    {
        $this->sut = new JobInstance();
    }

    public function test_it_is_a_constraint(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Validator\Constraint::class, $this->sut);
    }

    public function test_it_has_a_message(): void
    {
        $this->assertSame('akeneo_batch.job_instance.unknown_job_definition', $this->sut->message);
    }

    public function test_it_has_a_property(): void
    {
        $this->assertSame('jobName', $this->sut->property);
    }

    public function test_it_returns_the_name_of_the_class_that_validates_this_constraint(): void
    {
        $this->assertSame('akeneo_job_instance_validator', $this->sut->validatedBy());
    }

    public function test_it_returns_constraint_targets(): void
    {
        $this->assertSame('class', $this->sut->getTargets());
    }
}
