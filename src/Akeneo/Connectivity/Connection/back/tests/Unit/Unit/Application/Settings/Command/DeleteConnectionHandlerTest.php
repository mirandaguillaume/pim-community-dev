<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\DeleteConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\DeleteConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteClientInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteUserInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteConnectionHandlerTest extends TestCase
{
    private ConnectionRepositoryInterface|MockObject $repository;
    private DeleteClientInterface|MockObject $deleteClient;
    private DeleteUserInterface|MockObject $deleteUser;
    private DeleteConnectionHandler $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ConnectionRepositoryInterface::class);
        $this->deleteClient = $this->createMock(DeleteClientInterface::class);
        $this->deleteUser = $this->createMock(DeleteUserInterface::class);
        $this->sut = new DeleteConnectionHandler($this->repository, $this->deleteClient, $this->deleteUser);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(DeleteConnectionHandler::class, $this->sut);
    }

    public function test_it_deletes_a_connection(): void
    {
        $magentoClientId = new ClientId(1);
        $magentoUserId = new UserId(1);
        $magentoConnection = new Connection(
            'magento',
            'Magento',
            FlowType::OTHER,
            $magentoClientId->id(),
            $magentoUserId->id(),
            null,
            false
        );
        $command = new DeleteConnectionCommand((string) $magentoConnection->code());
        $this->repository->method('findOneByCode')->with('magento')->willReturn($magentoConnection);
        $this->repository->expects($this->once())->method('delete')->with($magentoConnection);
        $this->deleteClient->expects($this->once())->method('execute')->with($magentoClientId);
        $this->deleteUser->expects($this->once())->method('execute')->with($magentoUserId);
        $this->sut->handle($command);
    }
}
