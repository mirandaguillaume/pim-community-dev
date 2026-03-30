<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionSecretCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionSecretHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Service\RegenerateClientSecretInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RegenerateConnectionSecretHandlerTest extends TestCase
{
    private ConnectionRepositoryInterface|MockObject $repository;
    private RegenerateClientSecretInterface|MockObject $regenerateClientSecret;
    private RegenerateConnectionSecretHandler $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ConnectionRepositoryInterface::class);
        $this->regenerateClientSecret = $this->createMock(RegenerateClientSecretInterface::class);
        $this->sut = new RegenerateConnectionSecretHandler($this->repository, $this->regenerateClientSecret);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(RegenerateConnectionSecretHandler::class, $this->sut);
    }

    public function test_it_regenerates_a_client_secret(): void
    {
        $userId = new UserId(72);
        $connection = new Connection(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            42,
            $userId->id(),
            null,
            false
        );
        $this->repository->method('findOneByCode')->with('magento')->willReturn($connection);
        $this->regenerateClientSecret->expects($this->once())->method('execute')->with(new ClientId(42));
        $command = new RegenerateConnectionSecretCommand('magento');
        $this->sut->handle($command);
    }

    public function test_it_throws_an_exception_when_the_connection_does_not_exist(): void
    {
        $this->repository->method('findOneByCode')->with('magento')->willReturn(null);
        $this->regenerateClientSecret->expects($this->never())->method('execute')->with($this->anything());
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->handle(new RegenerateConnectionSecretCommand('magento'));
    }
}
