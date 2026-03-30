<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Domain\Settings\Validation\Connection\ConnectionLabelMustBeValid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ConnectionLabelMustBeValidTest extends TestCase
{
    private ConnectionLabelMustBeValid $sut;

    protected function setUp(): void
    {
        $this->sut = new ConnectionLabelMustBeValid();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ConnectionLabelMustBeValid::class, $this->sut);
    }

    public function test_it_does_not_build_violation_on_valid_label(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);

        $context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate('Sylius Connector', $context);
    }

    public function test_it_adds_a_violation_when_the_label_is_invalid(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $context->method('buildViolation')->with('akeneo_connectivity.connection.connection.constraint.label.too_long')->willReturn($builder);
        $builder->expects($this->once())->method('addViolation');
        $this->sut->validate(\str_repeat('A', 103), $context);
    }

    public function test_it_adds_a_violation_when_the_label_is_empty(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $context->method('buildViolation')->with('akeneo_connectivity.connection.connection.constraint.label.required')->willReturn($builder);
        $builder->expects($this->once())->method('addViolation');
        $this->sut->validate('', $context);
    }
}
