<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Apps\Service;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnection;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectionInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionWithCredentialsByCodeQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateConnectionTest extends TestCase
{
    private ConnectionRepositoryInterface|MockObject $repository;
    private SelectConnectionWithCredentialsByCodeQueryInterface|MockObject $selectConnectionWithCredentialsByCodeQuery;
    private CreateConnection $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ConnectionRepositoryInterface::class);
        $this->selectConnectionWithCredentialsByCodeQuery = $this->createMock(
            SelectConnectionWithCredentialsByCodeQueryInterface::class
        );
        $this->sut = new CreateConnection($this->repository, $this->selectConnectionWithCredentialsByCodeQuery);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CreateConnection::class, $this->sut);
        $this->assertInstanceOf(CreateConnectionInterface::class, $this->sut);
    }

    public function test_it_creates_a_connection_of_type_app_and_returns_it_with_its_credentials(): void
    {
        $createdConnection = null;
        $this->repository
            ->expects($this->once())
            ->method('create')
            ->willReturnCallback(function (Connection $connection) use (&$createdConnection): void {
                $createdConnection = $connection;
            });

        $expectedConnectionWithCredentials = $this->aConnectionWithCredentials('a_connection_code');
        $this->selectConnectionWithCredentialsByCodeQuery
            ->expects($this->once())
            ->method('execute')
            ->with('a_connection_code')
            ->willReturn($expectedConnectionWithCredentials);

        $result = $this->sut->execute(
            'a_connection_code',
            'App prototype',
            FlowType::OTHER,
            42,
            33,
        );

        $this->assertSame($expectedConnectionWithCredentials, $result);

        $this->assertInstanceOf(Connection::class, $createdConnection);
        $this->assertSame('a_connection_code', (string) $createdConnection->code());
        $this->assertSame('App prototype', (string) $createdConnection->label());
        $this->assertSame(FlowType::OTHER, (string) $createdConnection->flowType());
        $this->assertSame(42, $createdConnection->clientId()->id());
        $this->assertSame(33, $createdConnection->userId()->id());
        $this->assertSame(ConnectionType::APP_TYPE, (string) $createdConnection->type());
        $this->assertNull($createdConnection->image());
        $this->assertFalse($createdConnection->auditable());
    }

    public function test_it_throws_a_logic_exception_when_the_created_connection_cannot_be_read_back(): void
    {
        $this->repository->expects($this->once())->method('create');
        $this->selectConnectionWithCredentialsByCodeQuery
            ->expects($this->once())
            ->method('execute')
            ->with('a_connection_code')
            ->willReturn(null);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The connection just created should be available, it is not.');

        $this->sut->execute('a_connection_code', 'App prototype', FlowType::OTHER, 42, 33);
    }

    public function test_it_does_not_read_the_connection_back_when_its_creation_fails(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('create')
            ->willThrowException(new \RuntimeException('create failed'));
        $this->selectConnectionWithCredentialsByCodeQuery
            ->expects($this->never())
            ->method('execute')
            ->with($this->anything());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('create failed');

        $this->sut->execute('a_connection_code', 'App prototype', FlowType::OTHER, 42, 33);
    }

    public function test_it_rejects_a_flow_type_that_is_not_supported(): void
    {
        $this->repository->expects($this->never())->method('create')->with($this->anything());
        $this->selectConnectionWithCredentialsByCodeQuery
            ->expects($this->never())
            ->method('execute')
            ->with($this->anything());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('akeneo_connectivity.connection.connection.constraint.flow_type.invalid');

        $this->sut->execute('a_connection_code', 'App prototype', 'not_a_flow_type', 42, 33);
    }

    private function aConnectionWithCredentials(string $code): ConnectionWithCredentials
    {
        return new ConnectionWithCredentials(
            $code,
            'App prototype',
            FlowType::OTHER,
            null,
            'a_client_id',
            'a_secret',
            'a_connection_username',
            '1',
            '2',
            false,
            ConnectionType::APP_TYPE,
        );
    }
}
