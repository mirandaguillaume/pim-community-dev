<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\UserShouldExist;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\UserShouldExistValidator;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UserShouldExistValidatorTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private ExecutionContext|MockObject $context;
    private UserShouldExistValidator $sut;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new UserShouldExistValidator($this->userRepository);
        $this->userRepository->method('findOneBy')->with(['id' => 1])->willReturn(new User());
        $this->userRepository->method('findOneBy')->with(['id' => 2])->willReturn(null);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(UserShouldExistValidator::class, $this->sut);
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function test_it_throws_an_exception_with_a_wrong_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(1, new Type([]));
    }

    public function test_it_does_nothing_when_the_value_is_not_an_integer(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->context->expects($this->never())->method('addViolation')->with($this->anything());
        $this->sut->validate('1', new UserShouldExist());
        $this->sut->validate(null, new UserShouldExist());
    }

    public function test_it_adds_a_violation_when_user_is_unknown(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new UserShouldExist();
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message, ['{{ user_id }}' => 2])->willReturn($violationBuilder);
        $violationBuilder->method('setCode')->with((string) ViolationCode::PERMISSION)->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate(2, $constraint);
    }

    public function test_it_validates_when_user_exists(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->context->expects($this->never())->method('addViolation')->with($this->anything());
        $this->sut->validate(1, new UserShouldExist());
    }
}
