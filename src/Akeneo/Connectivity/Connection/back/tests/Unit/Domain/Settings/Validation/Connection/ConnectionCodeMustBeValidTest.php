<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Domain\Settings\Validation\Connection\ConnectionCodeMustBeValid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ConnectionCodeMustBeValidTest extends TestCase
{
    private ConnectionCodeMustBeValid $sut;

    protected function setUp(): void
    {
        $this->sut = new ConnectionCodeMustBeValid();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ConnectionCodeMustBeValid::class, $this->sut);
    }

    public function test_it_does_not_build_violation_on_valid_code(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);

        $context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate('magento', $context);
    }

    public function test_it_adds_a_violation_when_the_code_is_empty(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $context->method('buildViolation')->with('akeneo_connectivity.connection.connection.constraint.code.required')->willReturn($builder);
        $builder->expects($this->once())->method('addViolation');
        $this->sut->validate('', $context);
    }
}
