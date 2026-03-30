<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Validation\Connection\FlowTypeMustBeValid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class FlowTypeMustBeValidTest extends TestCase
{
    private FlowTypeMustBeValid $sut;

    protected function setUp(): void
    {
        $this->sut = new FlowTypeMustBeValid();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FlowTypeMustBeValid::class, $this->sut);
    }

    public function test_it_does_not_build_violation_on_valid_flow_type(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);

        $context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(FlowType::DATA_DESTINATION, $context);
    }

    public function test_it_adds_a_violation_when_the_flow_type_is_invalid(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $context->method('buildViolation')->with('akeneo_connectivity.connection.connection.constraint.flow_type.invalid')->willReturn($builder);
        $builder->expects($this->once())->method('addViolation');
        $this->sut->validate('Unknown Flow Type', $context);
    }
}
