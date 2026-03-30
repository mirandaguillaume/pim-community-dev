<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Service\UpdateUserPermissionsInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateConnectionHandlerTest extends TestCase
{
    private ValidatorInterface|MockObject $validator;
    private ConnectionRepositoryInterface|MockObject $repository;
    private UpdateUserPermissionsInterface|MockObject $updateUserPermissions;
    private UpdateConnectionHandler $sut;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->repository = $this->createMock(ConnectionRepositoryInterface::class);
        $this->updateUserPermissions = $this->createMock(UpdateUserPermissionsInterface::class);
        $this->sut = new UpdateConnectionHandler($this->validator, $this->repository, $this->updateUserPermissions);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(UpdateConnectionHandler::class, $this->sut);
    }

    public function test_it_updates_a_connection(): void
    {
        $command = new UpdateConnectionCommand(
            'magento',
            'Pimgento',
            FlowType::DATA_DESTINATION,
            null,
            '1',
            '2',
            true
        );
        $violations = new ConstraintViolationList([]);
        $this->validator->method('validate')->with($command)->willReturn($violations);
        $connection = new Connection(
            'magento',
            'Magento Connector',
            FlowType::OTHER,
            42,
            50,
            null,
            false
        );
        $this->repository->method('findOneByCode')->with('magento')->willReturn($connection);
        $this->repository->expects($this->once())->method('update')->with($this->isInstanceOf(Connection::class));
        $this->sut->handle($command);
    }

    public function test_it_throws_a_constraint_exception_when_something_is_invalid(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);

        $command = new UpdateConnectionCommand(
            'magento',
            'Pimgento',
            'Wrong flow type',
            null,
            '1',
            '2',
            true
        );
        $violations = new ConstraintViolationList([$violation]);
        $this->validator->method('validate')->with($command)->willReturn($violations);
        $this->repository->expects($this->never())->method('findOneByCode')->with('magento');
        $this->repository->expects($this->never())->method('update')->with($this->isInstanceOf(Connection::class));
        $this->expectException(ConstraintViolationListException::class);
        $this->sut->handle($command);
    }
}
