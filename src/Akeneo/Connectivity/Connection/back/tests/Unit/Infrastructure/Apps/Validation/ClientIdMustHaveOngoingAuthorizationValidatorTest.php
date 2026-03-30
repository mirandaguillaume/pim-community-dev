<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\ClientIdMustHaveOngoingAuthorization;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\ClientIdMustHaveOngoingAuthorizationValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ClientIdMustHaveOngoingAuthorizationValidatorTest extends TestCase
{
    private AppAuthorizationSessionInterface|MockObject $session;
    private ExecutionContextInterface|MockObject $context;
    private ClientIdMustHaveOngoingAuthorizationValidator $sut;

    protected function setUp(): void
    {
        $this->session = $this->createMock(AppAuthorizationSessionInterface::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new ClientIdMustHaveOngoingAuthorizationValidator($this->session);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_an_app_authorization_session(): void
    {
        $this->assertInstanceOf(ClientIdMustHaveOngoingAuthorizationValidator::class, $this->sut);
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

    public function test_it_validate_that_the_client_id_has_an_authorization_session(): void
    {
        $constraint = $this->createMock(ClientIdMustHaveOngoingAuthorization::class);
        $appAuthorization = $this->createMock(AppAuthorization::class);

        $this->session->method('getAppAuthorization')->with('app_id')->willReturn($appAuthorization);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate('app_id', $constraint);
    }

    public function test_it_adds_a_violation_when_client_id_has_not_an_authorization_session(): void
    {
        $constraint = $this->createMock(ClientIdMustHaveOngoingAuthorization::class);
        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->session->method('getAppAuthorization')->with('app_id')->willReturn(null);
        $this->context->method('buildViolation')->with($this->anything())->willReturn($violation);
        $violation->expects($this->once())->method('addViolation');
        $this->sut->validate('app_id', $constraint);
    }
}
