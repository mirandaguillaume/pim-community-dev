<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints;

use Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints\Scheduling;
use PHPUnit\Framework\TestCase;

class SchedulingTest extends TestCase
{
    private Scheduling $sut;

    protected function setUp(): void
    {
        $this->sut = new Scheduling();
    }

    public function test_it_is_a_constraint(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Validator\Constraint::class, $this->sut);
    }

    public function test_it_returns_the_name_of_the_class_that_validates_this_constraint(): void
    {
        $this->assertSame('akeneo_job_instance_scheduling_validator', $this->sut->validatedBy());
    }
}
