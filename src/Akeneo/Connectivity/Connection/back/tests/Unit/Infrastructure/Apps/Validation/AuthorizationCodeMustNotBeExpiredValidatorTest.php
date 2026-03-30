<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\AuthorizationCodeMustBeValid;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\AuthorizationCodeMustNotBeExpired;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\AuthorizationCodeMustNotBeExpiredValidator;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\IOAuth2AuthCode;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\IOAuth2GrantCode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthorizationCodeMustNotBeExpiredValidatorTest extends TestCase
{
    private IOAuth2GrantCode|MockObject $storage;
    private ExecutionContextInterface|MockObject $context;
    private AuthorizationCodeMustNotBeExpiredValidator $sut;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(IOAuth2GrantCode::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new AuthorizationCodeMustNotBeExpiredValidator($this->storage);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_an_expired_authorization_code_validator(): void
    {
        $this->assertInstanceOf(AuthorizationCodeMustNotBeExpiredValidator::class, $this->sut);
        $this->assertInstanceOf(ConstraintValidator::class, $this->sut);
    }

    public function test_it_validates_the_value_only_if_the_provided_constraint_is_matching_the_validator(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->sut->validate('auth_code_1234', new class extends Constraint {});
    }

    public function test_it_validates_only_string_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('The value to validate must be a string');
        $this->sut->validate(12345, new AuthorizationCodeMustNotBeExpired());
    }

    public function test_it_validates_the_authorization_code(): void
    {
        $authCode = $this->createMock(IOAuth2AuthCode::class);

        $authCode->method('getClientId')->willReturn('client_id');
        $authCode->method('hasExpired')->willReturn(false);
        $this->storage->method('getAuthCode')->with('auth_code_1234')->willReturn($authCode);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate('auth_code_1234', new AuthorizationCodeMustNotBeExpired());
    }

    public function test_it_builds_a_violation_if_the_authorization_code_is_expired(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $authCode = $this->createMock(IOAuth2AuthCode::class);

        $constraint = new AuthorizationCodeMustNotBeExpired();
        $authCode->method('getClientId')->willReturn('client_id');
        $authCode->method('hasExpired')->willReturn(true);
        $this->storage->method('getAuthCode')->with('auth_code_1234')->willReturn($authCode);
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message)->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('setCause')->with($constraint->cause)->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate('auth_code_1234', $constraint);
    }
}
