<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Domain\Settings\Validation\Connection\ConnectionImageMustBeValid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ConnectionImageMustBeValidTest extends TestCase
{
    private ConnectionImageMustBeValid $sut;

    protected function setUp(): void
    {
        $this->sut = new ConnectionImageMustBeValid();
    }

    public function test_it_is_a_connection_image_validator(): void
    {
        $this->assertInstanceOf(ConnectionImageMustBeValid::class, $this->sut);
    }

    public function test_it_does_not_build_a_violation_if_the_image_is_valid(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);

        $context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate('a/b/c/path.jpg', $context);
    }

    public function test_it_does_not_build_a_violation_if_the_image_is_null(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);

        $context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(null, $context);
    }

    public function test_it_builds_a_violation_if_image_is_not_valid(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $context->method('buildViolation')->with('akeneo_connectivity.connection.connection.constraint.image.not_empty')->willReturn($builder);
        $builder->expects($this->once())->method('addViolation');
        $this->sut->validate('', $context);
    }
}
