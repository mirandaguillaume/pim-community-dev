<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\AuthorizationCodeMustBeValid;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\AuthorizationCodeMustBeValidValidator;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\IOAuth2AuthCode;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\IOAuth2GrantCode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AuthorizationCodeMustBeValidValidatorTest extends TestCase
{
    private IOAuth2GrantCode|MockObject $storage;
    private ExecutionContextInterface|MockObject $context;
    private AuthorizationCodeMustBeValidValidator $sut;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(IOAuth2GrantCode::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new AuthorizationCodeMustBeValidValidator($this->storage);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_an_authorization_code_validator(): void
    {
        $this->assertInstanceOf(AuthorizationCodeMustBeValidValidator::class, $this->sut);
        $this->assertInstanceOf(ConstraintValidator::class, $this->sut);
    }

    public function test_it_validates_the_authorization_code(): void
    {
        $authCode = $this->createMock(IOAuth2AuthCode::class);

        $constraint = new AuthorizationCodeMustBeValid();
        $authCode->method('getClientId')->willReturn('client_id');
        $authCode->method('hasExpired')->willReturn(false);
        $this->storage->method('getAuthCode')->with('auth_code_1234')->willReturn($authCode);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate('auth_code_1234', $constraint);
    }

    public function test_it_builds_a_violation_if_the_authorization_code_is_invalid(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new AuthorizationCodeMustBeValid();
        $this->storage->method('getAuthCode')->with('auth_code_1234')->willReturn(null);
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message)->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('setCause')->with($constraint->cause)->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate('auth_code_1234', $constraint);
    }

    public function test_it_processes_only_string(): void
    {
        $this->expectException(new \InvalidArgumentException('The value to validate must be a string'));
        $this->sut->validate(12345, new AuthorizationCodeMustBeValid());
    }

    public function test_it_validates_the_value_only_if_the_provided_constraint_is_matching_the_validator(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->sut->validate('auth_code_1234', new class extends Constraint {});
    }
}
