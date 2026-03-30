<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\AppRoleWithScopesFactoryInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateConnectedAppWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateConnectedAppWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectedAppInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Application\User\CreateUserGroupInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Event\AppUserGroupCreated;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateConnectedAppWithAuthorizationHandlerTest extends TestCase
{
    private ValidatorInterface|MockObject $validator;
    private AppAuthorizationSessionInterface|MockObject $appAuthorizationSession;
    private GetAppQueryInterface|MockObject $getAppQuery;
    private CreateUserInterface|MockObject $createUser;
    private CreateUserGroupInterface|MockObject $createUserGroup;
    private CreateConnectionInterface|MockObject $createConnection;
    private AppRoleWithScopesFactoryInterface|MockObject $appRoleWithScopesFactory;
    private ClientProviderInterface|MockObject $clientProvider;
    private CreateConnectedAppInterface|MockObject $createApp;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private CreateConnectedAppWithAuthorizationHandler $sut;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->appAuthorizationSession = $this->createMock(AppAuthorizationSessionInterface::class);
        $this->getAppQuery = $this->createMock(GetAppQueryInterface::class);
        $this->createUser = $this->createMock(CreateUserInterface::class);
        $this->createUserGroup = $this->createMock(CreateUserGroupInterface::class);
        $this->createConnection = $this->createMock(CreateConnectionInterface::class);
        $this->appRoleWithScopesFactory = $this->createMock(AppRoleWithScopesFactoryInterface::class);
        $this->clientProvider = $this->createMock(ClientProviderInterface::class);
        $this->createApp = $this->createMock(CreateConnectedAppInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->sut = new CreateConnectedAppWithAuthorizationHandler(
            $this->validator,
            $this->appAuthorizationSession,
            $this->getAppQuery,
            $this->createUser,
            $this->createUserGroup,
            $this->createConnection,
            $this->appRoleWithScopesFactory,
            $this->clientProvider,
            $this->createApp,
            $this->eventDispatcher,
        );
    }

    public function test_it_is_instantiable(): void
    {
        $this->assertInstanceOf(CreateConnectedAppWithAuthorizationHandler::class, $this->sut);
    }

    public function test_it_throws_when_the_command_is_not_valid(): void
    {
        $command = new CreateConnectedAppWithAuthorizationCommand('');
        $this->validator->method('validate')->with($command)->willReturn(new ConstraintViolationList([
                            new ConstraintViolation('Not Blank', '', [], '', 'clientId', ''),
                        ]));
        $this->expectException(InvalidAppAuthorizationRequestException::class);
        $this->sut->handle($command);
    }

    public function test_it_throws_when_the_app_was_not_found_despite_validation(): void
    {
        $command = new CreateConnectedAppWithAuthorizationCommand('an_app_id');
        $this->validator->method('validate')->with($command)->willReturn(new ConstraintViolationList([]));
        $this->getAppQuery->method('execute')->with('an_app_id')->willReturn(null);
        $this->expectException(\LogicException::class);
        $this->sut->handle($command);
    }

    public function test_it_throws_when_the_app_authorization_was_not_found_despite_validation(): void
    {
        $app = $this->createMock(App::class);

        $command = new CreateConnectedAppWithAuthorizationCommand('an_app_id');
        $this->validator->method('validate')->with($command)->willReturn(new ConstraintViolationList([]));
        $this->getAppQuery->method('execute')->with('an_app_id')->willReturn($app);
        $this->appAuthorizationSession->method('getAppAuthorization')->with('an_app_id')->willReturn(null);
        $this->expectException(\LogicException::class);
        $this->sut->handle($command);
    }

    public function test_it_throws_when_the_client_was_not_found_despite_validation(): void
    {
        $app = $this->createMock(App::class);
        $appAuthorization = $this->createMock(AppAuthorization::class);

        $command = new CreateConnectedAppWithAuthorizationCommand('an_app_id');
        $this->validator->method('validate')->with($command)->willReturn(new ConstraintViolationList([]));
        $this->getAppQuery->method('execute')->with('an_app_id')->willReturn($app);
        $this->appAuthorizationSession->method('getAppAuthorization')->with('an_app_id')->willReturn($appAuthorization);
        $this->clientProvider->method('findClientByAppId')->with('an_app_id')->willReturn(null);
        $this->expectException(\LogicException::class);
        $this->sut->handle($command);
    }

    public function test_it_throws_when_the_created_group_is_invalid(): void
    {
        $app = $this->createMock(App::class);
        $appAuthorization = $this->createMock(AppAuthorization::class);
        $client = $this->createMock(Client::class);
        $userGroup = $this->createMock(GroupInterface::class);

        $command = new CreateConnectedAppWithAuthorizationCommand('an_app_id');
        $this->validator->method('validate')->with($command)->willReturn(new ConstraintViolationList([]));
        $app->method('isCustomApp')->willReturn(false);
        $this->getAppQuery->method('execute')->with('an_app_id')->willReturn($app);
        $this->appAuthorizationSession->method('getAppAuthorization')->with('an_app_id')->willReturn($appAuthorization);
        $this->clientProvider->method('findClientByAppId')->with('an_app_id')->willReturn($client);
        $this->createUserGroup->method('execute')->with($this->anything())->willReturn($userGroup);
        $userGroup->method('getName')->willReturn(null);
        $this->expectException(\LogicException::class);
        $this->sut->handle($command);
    }

    public function test_it_throws_when_the_created_role_is_invalid(): void
    {
        $app = $this->createMock(App::class);
        $appAuthorization = $this->createMock(AppAuthorization::class);
        $client = $this->createMock(Client::class);
        $userGroup = $this->createMock(GroupInterface::class);
        $role = $this->createMock(RoleInterface::class);

        $command = new CreateConnectedAppWithAuthorizationCommand('an_app_id');
        $this->validator->method('validate')->with($command)->willReturn(new ConstraintViolationList([]));
        $this->getAppQuery->method('execute')->with('an_app_id')->willReturn($app);
        $this->appAuthorizationSession->method('getAppAuthorization')->with('an_app_id')->willReturn($appAuthorization);
        $appAuthorization->method('getAuthorizationScopes')->willReturn(ScopeList::fromScopes([]));
        $this->clientProvider->method('findClientByAppId')->with('an_app_id')->willReturn($client);
        $this->createUserGroup->method('execute')->with($this->anything())->willReturn($userGroup);
        $userGroup->method('getName')->willReturn('foo');
        $this->appRoleWithScopesFactory->method('createRole')->with('an_app_id', [])->willReturn($role);
        $role->method('getRole')->willReturn(null);
        $this->expectException(\LogicException::class);
        $this->sut->handle($command);
    }

    public function test_it_creates_a_connection_when_everything_is_valid(): void
    {
        $app = $this->createMock(App::class);
        $appAuthorization = $this->createMock(AppAuthorization::class);
        $client = $this->createMock(Client::class);
        $userGroup = $this->createMock(GroupInterface::class);
        $role = $this->createMock(RoleInterface::class);
        $connection = $this->createMock(ConnectionWithCredentials::class);
        $permissionFeatureFlag = $this->createMock(FeatureFlag::class);

        $command = new CreateConnectedAppWithAuthorizationCommand('an_app_id');
        $this->validator->method('validate')->with($command)->willReturn(new ConstraintViolationList([]));
        $this->getAppQuery->method('execute')->with('an_app_id')->willReturn($app);
        $this->appAuthorizationSession->method('getAppAuthorization')->with('an_app_id')->willReturn($appAuthorization);
        $appAuthorization->method('getAuthorizationScopes')->willReturn(ScopeList::fromScopes(['a_scope']));
        $this->clientProvider->method('findClientByAppId')->with('an_app_id')->willReturn($client);
        $this->createUserGroup->method('execute')->with($this->anything())->willReturn($userGroup);
        $userGroup->method('getName')->willReturn('a_group');
        $this->appRoleWithScopesFactory->method('createRole')->with('an_app_id', ['a_scope'])->willReturn($role);
        $role->method('getRole')->willReturn('ROLE_APP');
        $this->createUser->method('execute')->with($this->anything(), $this->anything(), ['a_group'], ['ROLE_APP'], 'an_app_id')->willReturn(43);
        $client->method('getId')->willReturn(42);
        $app->method('getName')->willReturn('My App');
        $this->createConnection->method('execute')->with($this->anything(), 'My App', 'other', 42, 43)->willReturn($connection);
        $connection->method('code')->willReturn('random_connection_code');
        $connectedApp = new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['a_scope'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
        );
        $this->createApp->expects($this->once())->method('execute')->with($app, ['a_scope'], 'random_connection_code', 'a_group', $this->anything())->willReturn($connectedApp);
        $permissionFeatureFlag->method('isEnabled')->willReturn(true);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with(new AppUserGroupCreated('a_group'), AppUserGroupCreated::class);
        $this->sut->handle($command);
    }
}
