<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\UserConsentRequiredException;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\CreateUserConsentQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetUserConsentedAuthenticationScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\ClockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequestAppAuthenticationHandlerTest extends TestCase
{
    private GetUserConsentedAuthenticationScopesQueryInterface|MockObject $getUserConsentedAuthenticationScopesQuery;
    private CreateUserConsentQueryInterface|MockObject $createUserConsentQuery;
    private ClockInterface|MockObject $clock;
    private ValidatorInterface|MockObject $validator;
    private RequestAppAuthenticationHandler $sut;

    protected function setUp(): void
    {
        $this->getUserConsentedAuthenticationScopesQuery = $this->createMock(GetUserConsentedAuthenticationScopesQueryInterface::class);
        $this->createUserConsentQuery = $this->createMock(CreateUserConsentQueryInterface::class);
        $this->clock = $this->createMock(ClockInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->sut = new RequestAppAuthenticationHandler(
            $this->getUserConsentedAuthenticationScopesQuery,
            $this->createUserConsentQuery,
            $this->clock,
            $this->validator
        );
    }

    public function test_it_is_instantiable(): void
    {
        $this->assertInstanceOf(RequestAppAuthenticationHandler::class, $this->sut);
    }

    public function test_it_throws_when_the_command_is_invalid(): void
    {
        $constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);
        $constraintViolation = $this->createMock(ConstraintViolationInterface::class);

        $command = new RequestAppAuthenticationCommand('a_app_id', 1, ScopeList::fromScopeString(''));
        $this->validator->method('validate')->with($command)->willReturn($constraintViolationList);
        $constraintViolationList->method('count')->willReturn(1);
        $constraintViolationList->method('get')->with(0)->willReturn($constraintViolation);
        $constraintViolation->method('getMessage')->willReturn('a_validation_error');
        $this->expectException(new \InvalidArgumentException('a_validation_error'));
        $this->sut->handle($command);
    }

    public function test_it_clears_consented_scopes_when_openid_is_not_requested(): void
    {
        $command = new RequestAppAuthenticationCommand('a_app_id', 1, ScopeList::fromScopeString('a_scope'));
        $this->validator->method('validate')->with($command)->willReturn(new ConstraintViolationList());
        $dateTime = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2020-01-01T00:00:00Z');
        $this->clock->method('now')->willReturn($dateTime);
        $this->createUserConsentQuery->expects($this->once())->method('execute')->with(1, 'a_app_id', [], $dateTime);
        $this->sut->handle($command);
    }

    public function test_it_removes_previously_consented_scopes_that_are_not_requested_anymore(): void
    {
        $command = new RequestAppAuthenticationCommand('a_app_id', 1, ScopeList::fromScopeString('openid a_scope'));
        $this->validator->method('validate')->with($command)->willReturn(new ConstraintViolationList());
        $dateTime = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2020-01-01T00:00:00Z');
        $this->clock->method('now')->willReturn($dateTime);
        $this->getUserConsentedAuthenticationScopesQuery->method('execute')->with(1, 'a_app_id')->willReturn(['openid', 'a_scope', 'a_scope_not_requested']);
        $this->createUserConsentQuery->expects($this->once())->method('execute')->with(1, 'a_app_id', ['openid', 'a_scope'], $dateTime);
        $this->sut->handle($command);
    }

    public function test_it_consents_automatically_when_openid_is_the_only_scope_requested(): void
    {
        $command = new RequestAppAuthenticationCommand('a_app_id', 1, ScopeList::fromScopeString('openid'));
        $this->validator->method('validate')->with($command)->willReturn(new ConstraintViolationList());
        $dateTime = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2020-01-01T00:00:00Z');
        $this->clock->method('now')->willReturn($dateTime);
        $this->getUserConsentedAuthenticationScopesQuery->method('execute')->with(1, 'a_app_id')->willReturn([]);
        $this->createUserConsentQuery->expects($this->once())->method('execute')->with(1, 'a_app_id', ['openid'], $dateTime);
        $this->sut->handle($command);
    }

    public function test_it_throws_when_new_scopes_are_requiring_consent(): void
    {
        $command = new RequestAppAuthenticationCommand('a_app_id', 1, ScopeList::fromScopeString('openid a_new_scope'));
        $this->validator->method('validate')->with($command)->willReturn(new ConstraintViolationList());
        $dateTime = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2020-01-01T00:00:00Z');
        $this->clock->method('now')->willReturn($dateTime);
        $this->getUserConsentedAuthenticationScopesQuery->method('execute')->with(1, 'a_app_id')->willReturn(['openid']);
        $exception = new UserConsentRequiredException('a_app_id', 1);
        $this->expectException($exception);
        $this->sut->handle($command);
    }
}
