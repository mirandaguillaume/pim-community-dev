<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\ClientIdMustBeValid;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\ClientIdMustBeValidValidator;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientInterface;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ClientIdMustBeValidValidatorTest extends TestCase
{
    private ClientManagerInterface|MockObject $clientManager;
    private ExecutionContextInterface|MockObject $context;
    private ClientIdMustBeValidValidator $sut;

    protected function setUp(): void
    {
        $this->clientManager = $this->createMock(ClientManagerInterface::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new ClientIdMustBeValidValidator($this->clientManager);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_an_app_authorization_session(): void
    {
        $this->assertInstanceOf(ClientIdMustBeValidValidator::class, $this->sut);
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

    public function test_it_validate_that_the_client_id_exists(): void
    {
        $constraint = $this->createMock(ClientIdMustBeValid::class);
        $client = $this->createMock(ClientInterface::class);

        $this->clientManager->method('findClientBy')->with(['marketplacePublicAppId' => 'app_id'])->willReturn($client);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate('app_id', $constraint);
    }

    public function test_it_adds_a_violation_when_client_id_was_not_found(): void
    {
        $constraint = $this->createMock(ClientIdMustBeValid::class);
        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->clientManager->method('findClientBy')->with(['marketplacePublicAppId' => 'app_id'])->willReturn(null);
        $this->context->method('buildViolation')->with($this->anything())->willReturn($violation);
        $violation->expects($this->once())->method('addViolation');
        $this->sut->validate('app_id', $constraint);
    }
}
