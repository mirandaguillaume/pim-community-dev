<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Service\UpdateConnectedAppRoleWithScopesInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\UpdateConnectedAppScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateConnectedAppScopesWithAuthorizationHandlerTest extends TestCase
{
    private ValidatorInterface|MockObject $validator;
    private AppAuthorizationSessionInterface|MockObject $appAuthorizationSession;
    private UpdateConnectedAppScopesQueryInterface|MockObject $updateConnectedAppScopesQuery;
    private UpdateConnectedAppRoleWithScopesInterface|MockObject $updateConnectedAppRoleWithScopes;
    private UpdateConnectedAppScopesWithAuthorizationHandler $sut;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->appAuthorizationSession = $this->createMock(AppAuthorizationSessionInterface::class);
        $this->updateConnectedAppScopesQuery = $this->createMock(UpdateConnectedAppScopesQueryInterface::class);
        $this->updateConnectedAppRoleWithScopes = $this->createMock(UpdateConnectedAppRoleWithScopesInterface::class);
        $this->sut = new UpdateConnectedAppScopesWithAuthorizationHandler(
            $this->validator,
            $this->appAuthorizationSession,
            $this->updateConnectedAppScopesQuery,
            $this->updateConnectedAppRoleWithScopes,
        );
    }

    public function test_it_is_instantiable(): void
    {
        $this->assertInstanceOf(UpdateConnectedAppScopesWithAuthorizationHandler::class, $this->sut);
    }

    public function test_it_throws_when_the_command_is_not_valid(): void
    {
        $command = new UpdateConnectedAppScopesWithAuthorizationCommand('');
        $this->validator->method('validate')->with($command)->willReturn(new ConstraintViolationList([
                            new ConstraintViolation('Not Blank', '', [], '', 'clientId', ''),
                        ]));
        $this->expectException(InvalidAppAuthorizationRequestException::class);
        $this->sut->handle($command);
    }

    public function test_it_throws_when_the_app_authorization_was_not_found_despite_validation(): void
    {
        $command = new UpdateConnectedAppScopesWithAuthorizationCommand('an_app_id');
        $this->validator->method('validate')->with($command)->willReturn(new ConstraintViolationList([]));
        $this->appAuthorizationSession->method('getAppAuthorization')->with('an_app_id')->willReturn(null);
        $this->expectException(\LogicException::class);
        $this->sut->handle($command);
    }

    public function test_it_updates_a_connected_app_when_everything_is_valid(): void
    {
        $appAuthorization = $this->createMock(AppAuthorization::class);

        $command = new UpdateConnectedAppScopesWithAuthorizationCommand('an_app_id');
        $this->validator->method('validate')->with($command)->willReturn(new ConstraintViolationList([]));
        $this->appAuthorizationSession->method('getAppAuthorization')->with('an_app_id')->willReturn($appAuthorization);
        $appAuthorization->method('getAuthorizationScopes')->willReturn(ScopeList::fromScopes(['a_scope']));
        $this->updateConnectedAppScopesQuery->expects($this->once())->method('execute')->with(['a_scope'], 'an_app_id');
        $this->updateConnectedAppRoleWithScopes->expects($this->once())->method('execute')->with('an_app_id', ['a_scope']);
        $this->sut->handle($command);
    }
}
