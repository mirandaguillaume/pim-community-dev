<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\UserIdMustBeValid;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\UserIdMustBeValidValidator;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UserIdMustBeValidValidatorTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private ExecutionContextInterface|MockObject $context;
    private UserIdMustBeValidValidator $sut;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new UserIdMustBeValidValidator($this->userRepository);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_instantiable(): void
    {
        $this->assertInstanceOf(UserIdMustBeValidValidator::class, $this->sut);
    }

    public function test_it_throw_if_not_the_excepted_constraint(): void
    {
        $constraint = $this->createMock(Constraint::class);

        $this->expectException(UnexpectedTypeException::class);
        $this->sut->validate(
            null,
            $constraint,
        );
    }

    public function test_it_validates_that_the_user_exists(): void
    {
        $constraint = $this->createMock(UserIdMustBeValid::class);
        $user = $this->createMock(UserInterface::class);

        $this->userRepository->method('find')->with(1)->willReturn($user);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(1, $constraint);
    }

    public function test_it_adds_a_violation_when_the_user_doesnt_exist(): void
    {
        $constraint = $this->createMock(UserIdMustBeValid::class);
        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->userRepository->method('find')->with(1)->willReturn(null);
        $this->context->method('buildViolation')->with($this->anything())->willReturn($violation);
        $violation->expects($this->once())->method('addViolation');
        $this->sut->validate(1, $constraint);
    }
}
