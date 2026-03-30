<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Service\DeleteUserGroupInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\DeleteUserRoleInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteClientInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteUserInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppDeletion;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\DeleteConnectedAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppDeletionQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\SaveRevokedAccessTokensOfDisconnectedAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteAppHandlerTest extends TestCase
{
    private GetAppDeletionQueryInterface|MockObject $getAppDeletionQuery;
    private DeleteConnectedAppQueryInterface|MockObject $deleteConnectedAppQuery;
    private ConnectionRepositoryInterface|MockObject $connectionRepository;
    private DeleteUserInterface|MockObject $deleteUser;
    private DeleteClientInterface|MockObject $deleteClient;
    private DeleteUserGroupInterface|MockObject $deleteUserGroup;
    private DeleteUserRoleInterface|MockObject $deleteUserRole;
    private SaveRevokedAccessTokensOfDisconnectedAppQueryInterface|MockObject $saveRevokedAccessTokensOfDisconnectedAppQuery;
    private DeleteAppHandler $sut;

    protected function setUp(): void
    {
        $this->getAppDeletionQuery = $this->createMock(GetAppDeletionQueryInterface::class);
        $this->deleteConnectedAppQuery = $this->createMock(DeleteConnectedAppQueryInterface::class);
        $this->connectionRepository = $this->createMock(ConnectionRepositoryInterface::class);
        $this->deleteUser = $this->createMock(DeleteUserInterface::class);
        $this->deleteClient = $this->createMock(DeleteClientInterface::class);
        $this->deleteUserGroup = $this->createMock(DeleteUserGroupInterface::class);
        $this->deleteUserRole = $this->createMock(DeleteUserRoleInterface::class);
        $this->saveRevokedAccessTokensOfDisconnectedAppQuery = $this->createMock(SaveRevokedAccessTokensOfDisconnectedAppQueryInterface::class);
        $this->sut = new DeleteAppHandler(
            $this->getAppDeletionQuery,
            $this->deleteConnectedAppQuery,
            $this->connectionRepository,
            $this->deleteUser,
            $this->deleteClient,
            $this->deleteUserGroup,
            $this->deleteUserRole,
            $this->saveRevokedAccessTokensOfDisconnectedAppQuery
        );
    }

    public function test_it_is_a_delete_app_handler(): void
    {
        $this->assertInstanceOf(DeleteAppHandler::class, $this->sut);
    }

    public function test_it_deletes_an_app(): void
    {
        $connection = $this->createMock(Connection::class);
        $clientId = $this->createMock(ClientId::class);
        $userId = $this->createMock(UserId::class);

        $command = new DeleteAppCommand('app_id');
        $appDeletion = new AppDeletion(
            'app_id',
            'connection_code',
            'app_user_group_name',
            'ROLE_APP'
        );
        $this->getAppDeletionQuery->method('execute')->with('app_id')->willReturn($appDeletion);
        $this->connectionRepository->method('findOneByCode')->with('connection_code')->willReturn($connection);
        $connection->method('clientId')->willReturn($clientId);
        $connection->method('userId')->willReturn($userId);
        $this->saveRevokedAccessTokensOfDisconnectedAppQuery->expects($this->once())->method('execute')->with('app_id');
        $this->connectionRepository->expects($this->once())->method('delete')->with($connection);
        $this->deleteClient->expects($this->once())->method('execute')->with($clientId);
        $this->deleteUser->expects($this->once())->method('execute')->with($userId);
        $this->deleteUserGroup->expects($this->once())->method('execute')->with('app_user_group_name');
        $this->deleteUserRole->expects($this->once())->method('execute')->with('ROLE_APP');
        $this->sut->handle($command);
    }
}
