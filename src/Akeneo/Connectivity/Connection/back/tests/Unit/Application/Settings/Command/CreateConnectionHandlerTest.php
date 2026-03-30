<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateClientInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Client;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\User;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateConnectionHandlerTest extends TestCase
{
    private ValidatorInterface|MockObject $validator;
    private ConnectionRepositoryInterface|MockObject $repository;
    private CreateClientInterface|MockObject $createClient;
    private CreateUserInterface|MockObject $createUser;
    private FindAConnectionHandler|MockObject $findAConnectionHandler;
    private CreateConnectionHandler $sut;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->repository = $this->createMock(ConnectionRepositoryInterface::class);
        $this->createClient = $this->createMock(CreateClientInterface::class);
        $this->createUser = $this->createMock(CreateUserInterface::class);
        $this->findAConnectionHandler = $this->createMock(FindAConnectionHandler::class);
        $this->sut = new CreateConnectionHandler($this->validator, $this->repository, $this->createClient, $this->createUser, $this->findAConnectionHandler);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CreateConnectionHandler::class, $this->sut);
    }

    public function test_it_creates_a_connection(): void
    {
        $connectionDTO = $this->createMock(ConnectionWithCredentials::class);

        $command = new CreateConnectionCommand('magento', 'Magento Connector', FlowType::DATA_DESTINATION, true);
        $violations = new ConstraintViolationList([]);
        $this->validator->method('validate')->with($command)->willReturn($violations);
        $client = new Client(42, '42_myclientId', 'secret');
        $this->createClient->expects($this->once())->method('execute')->with('Magento Connector')->willReturn($client);
        $user = new User(42, 'magento_app', 'my_client_pwd');
        $this->createUser->method('execute')->with($this->isType('string'), 'Magento Connector', ' ', null)->willReturn($user);
        $this->repository->expects($this->once())->method('create')->with($this->isInstanceOf(Connection::class));
        $this->findAConnectionHandler->expects($this->once())->method('handle')->with($this->isInstanceOf(FindAConnectionQuery::class))->willReturn($connectionDTO);
        $connectionDTO->expects($this->once())->method('setPassword')->with('my_client_pwd');
        $this->assertSame($connectionDTO, $this->sut->handle($command));
    }

    public function test_it_returns_a_connection_with_credentials(): void
    {
        $command = new CreateConnectionCommand('magento', 'Magento Connector', FlowType::DATA_DESTINATION, true, null, 'All');
        $violations = new ConstraintViolationList([]);
        $this->validator->method('validate')->with($command)->willReturn($violations);
        $client = new Client(42, '42_myclientId', 'secret');
        $this->createClient->expects($this->once())->method('execute')->with('Magento Connector')->willReturn($client);
        $user = new User(42, 'magento_app', 'my_client_pwd');
        $this->createUser->method('execute')->with($this->isType('string'), 'Magento Connector', ' ', ['All'])->willReturn($user);
        $this->repository->expects($this->once())->method('create')->with($this->isInstanceOf(Connection::class));
        $connection = new ConnectionWithCredentials(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            null,
            '42_myclientId',
            'secret',
            'magento_app',
            'user_role_id',
            'user_group_id',
            false,
            'default'
        );
        $this->findAConnectionHandler->expects($this->once())->method('handle')->with($this->isInstanceOf(FindAConnectionQuery::class))->willReturn($connection);
        $connectionWithPassword = $this->sut->handle($command);
        $this->assertInstanceOf(ConnectionWithCredentials::class, $connectionWithPassword);
        $this->assertSame('my_client_pwd', $connectionWithPassword->password());
    }

    public function test_it_throws_a_constraint_exception_when_something_is_invalid(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);

        $command = new CreateConnectionCommand('magento', 'Magento Connector', 'Wrong Flow Type', false);
        $violations = new ConstraintViolationList([$violation]);
        $this->validator->method('validate')->with($command)->willReturn($violations);
        $this->createClient->expects($this->never())->method('execute')->with($this->anything());
        $this->createUser->expects($this->never())->method('execute');
        $this->repository->expects($this->never())->method('create')->with($this->anything());
        $this->expectException(ConstraintViolationListException::class);
        $this->sut->handle($command);
    }
}
