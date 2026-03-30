<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthenticationException;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\CreateUserConsentQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\ClockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConsentAppAuthenticationHandlerTest extends TestCase
{
    private GetAppConfirmationQueryInterface|MockObject $getAppConfirmationQuery;
    private AppAuthorizationSessionInterface|MockObject $appAuthorizationSession;
    private CreateUserConsentQueryInterface|MockObject $createUserConsentQuery;
    private ClockInterface|MockObject $clock;
    private ValidatorInterface|MockObject $validator;
    private ConsentAppAuthenticationHandler $sut;

    protected function setUp(): void
    {
        $this->getAppConfirmationQuery = $this->createMock(GetAppConfirmationQueryInterface::class);
        $this->appAuthorizationSession = $this->createMock(AppAuthorizationSessionInterface::class);
        $this->createUserConsentQuery = $this->createMock(CreateUserConsentQueryInterface::class);
        $this->clock = $this->createMock(ClockInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->sut = new ConsentAppAuthenticationHandler(
            $this->getAppConfirmationQuery,
            $this->appAuthorizationSession,
            $this->createUserConsentQuery,
            $this->clock,
            $this->validator
        );
        $this->clock->method('now')->willReturn(\DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2021-02-03T00:00:00Z'));
    }

    public function test_it_is_instantiable(): void
    {
        $this->assertInstanceOf(ConsentAppAuthenticationHandler::class, $this->sut);
    }

    public function test_it_creates_the_user_consent(): void
    {
        $constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);

        $userGroup = 'a_user_group';
        $clientId = 'a_client_id';
        $pimUserId = 1;
        $fosClientId = 2;
        $consentAppAuthenticationCommand = new ConsentAppAuthenticationCommand($clientId, $pimUserId);
        $appAuthorization = AppAuthorization::createFromNormalized([
                    'client_id' => $consentAppAuthenticationCommand->getClientId(),
                    'authorization_scope' => ScopeList::fromScopes([])->toScopeString(),
                    'authentication_scope' => ScopeList::fromScopes([AuthenticationScope::SCOPE_OPENID])->toScopeString(),
                    'redirect_uri' => 'a_redirect_uri',
                    'state' => 'a_state',
                ]);
        $appConfirmation = AppConfirmation::create(
            $consentAppAuthenticationCommand->getClientId(),
            $pimUserId,
            $userGroup,
            $fosClientId
        );
        $constraintViolationList->method('count')->willReturn(0);
        $this->appAuthorizationSession->method('getAppAuthorization')->with($consentAppAuthenticationCommand->getClientId())->willReturn($appAuthorization);
        $this->getAppConfirmationQuery->method('execute')->with($consentAppAuthenticationCommand->getClientId())->willReturn($appConfirmation);
        $this->validator->method('validate')->with($consentAppAuthenticationCommand)->willReturn($constraintViolationList);
        $this->createUserConsentQuery->expects($this->once())->method('execute')->with(
            $consentAppAuthenticationCommand->getPimUserId(),
            $appConfirmation->getAppId(),
            $appAuthorization->getAuthenticationScopes()->getScopes(),
            $this->anything()
        );
        $this->sut->handle($consentAppAuthenticationCommand);
    }

    public function test_it_throws_when_the_command_is_not_valid(): void
    {
        $constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);

        $clientId = 'a_client_id';
        $pimUserId = 1;
        $consentAppAuthenticationCommand = new ConsentAppAuthenticationCommand($clientId, $pimUserId);
        $constraintViolation = new ConstraintViolation('a_violated_constraint', '', [], '', '', '');
        $constraintViolationList->method('count')->willReturn(1);
        $constraintViolationList->method('get')->with(0)->willReturn($constraintViolation);
        $this->validator->method('validate')->with($consentAppAuthenticationCommand)->willReturn($constraintViolationList);
        $this->expectException(InvalidAppAuthenticationException::class);
        $this->sut->handle($consentAppAuthenticationCommand);
    }

    public function test_it_throws_when_the_app_authorization_is_not_found(): void
    {
        $constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);

        $clientId = 'a_client_id';
        $pimUserId = 1;
        $consentAppAuthenticationCommand = new ConsentAppAuthenticationCommand($clientId, $pimUserId);
        $constraintViolationList->method('count')->willReturn(0);
        $this->appAuthorizationSession->method('getAppAuthorization')->with($consentAppAuthenticationCommand->getClientId())->willReturn(null);
        $this->validator->method('validate')->with($consentAppAuthenticationCommand)->willReturn($constraintViolationList);
        $this->expectException(\LogicException::class);

        $this->expectExceptionMessage('There is no active app authorization in session');
        $this->sut->handle($consentAppAuthenticationCommand);
    }

    public function test_it_throws_when_the_app_confirmation_is_not_found(): void
    {
        $constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);

        $clientId = 'a_client_id';
        $pimUserId = 1;
        $consentAppAuthenticationCommand = new ConsentAppAuthenticationCommand($clientId, $pimUserId);
        $appAuthorization = AppAuthorization::createFromNormalized([
                    'client_id' => $consentAppAuthenticationCommand->getClientId(),
                    'authorization_scope' => ScopeList::fromScopes([])->toScopeString(),
                    'authentication_scope' => ScopeList::fromScopes([AuthenticationScope::SCOPE_OPENID])->toScopeString(),
                    'redirect_uri' => 'a_redirect_uri',
                    'state' => 'a_state',
                ]);
        $constraintViolationList->method('count')->willReturn(0);
        $this->appAuthorizationSession->method('getAppAuthorization')->with($consentAppAuthenticationCommand->getClientId())->willReturn($appAuthorization);
        $this->getAppConfirmationQuery->method('execute')->with($consentAppAuthenticationCommand->getClientId())->willReturn(null);
        $this->validator->method('validate')->with($consentAppAuthenticationCommand)->willReturn($constraintViolationList);
        $this->expectException(\LogicException::class);

        $this->expectExceptionMessage('The connected app should have been created');
        $this->sut->handle($consentAppAuthenticationCommand);
    }
}
