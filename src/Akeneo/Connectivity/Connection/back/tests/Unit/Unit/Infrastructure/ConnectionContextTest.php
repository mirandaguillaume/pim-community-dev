<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\AreCredentialsValidCombinationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\SelectConnectionCodeByClientIdQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionContextTest extends TestCase
{
    private AreCredentialsValidCombinationQueryInterface|MockObject $areCredentialsValidCombinationQuery;
    private SelectConnectionCodeByClientIdQueryInterface|MockObject $selectConnectionCode;
    private ConnectionRepositoryInterface|MockObject $connectionRepository;
    private ConnectionContext $sut;

    protected function setUp(): void
    {
        $this->areCredentialsValidCombinationQuery = $this->createMock(AreCredentialsValidCombinationQueryInterface::class);
        $this->selectConnectionCode = $this->createMock(SelectConnectionCodeByClientIdQueryInterface::class);
        $this->connectionRepository = $this->createMock(ConnectionRepositoryInterface::class);
        $this->sut = new ConnectionContext($this->areCredentialsValidCombinationQuery, $this->selectConnectionCode, $this->connectionRepository);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ConnectionContext::class, $this->sut);
    }

    public function test_it_returns_connection_when_client_id_is_defined(): void
    {
        $this->sut->setClientId('client_id');
        $connection = new Connection(
            'magento',
            'magento',
            FlowType::DATA_DESTINATION,
            42,
            10,
            null,
            false
        );
        $this->selectConnectionCode->method('execute')->with('client_id')->willReturn('12');
        $this->connectionRepository->method('findOneByCode')->with('12')->willReturn($connection);
        $this->assertSame($connection, $this->sut->getConnection());
    }

    public function test_it_returns_null_when_client_id_is_not_defined(): void
    {
        $this->assertNull($this->sut->getConnection());
    }

    public function test_it_returns_connection_as_not_collectable_when_connection_is_not_auditable(): void
    {
        $this->sut->setClientId('client_id');
        $this->sut->setUsername('test');
        $connection = new Connection(
            'magento',
            'magento',
            FlowType::DATA_DESTINATION,
            42,
            10,
            null,
            false
        );
        $this->areCredentialsValidCombinationQuery->method('execute')->with('client_id', 'test')->willReturn(true);
        $this->selectConnectionCode->method('execute')->with('client_id')->willReturn('12');
        $this->connectionRepository->method('findOneByCode')->with('12')->willReturn($connection);
        $this->assertSame(false, $this->sut->isCollectable());
    }

    public function test_it_returns_connection_as_not_collectable_when_credentials_are_not_valid_combination(): void
    {
        $this->sut->setClientId('client_id');
        $this->sut->setUsername('username');
        $connection = new Connection(
            'magento',
            'magento',
            FlowType::DATA_DESTINATION,
            42,
            10,
            null,
            true
        );
        $this->areCredentialsValidCombinationQuery->method('execute')->with('client_id', 'username')->willReturn(false);
        $this->selectConnectionCode->method('execute')->with('client_id')->willReturn('12');
        $this->connectionRepository->method('findOneByCode')->with('12')->willReturn($connection);
        $this->assertSame(false, $this->sut->isCollectable());
    }

    public function test_it_returns_are_credentials_valid_combination(): void
    {
        $this->sut->setClientId('client_id');
        $this->sut->setUsername('username');
        $this->areCredentialsValidCombinationQuery->method('execute')->with('client_id', 'username')->willReturn(true);
        $this->areCredentialsValidCombinationQuery->expects($this->once())->method('execute')->with('client_id', 'username');
        $this->assertSame(true, $this->sut->areCredentialsValidCombination());
    }

    public function test_it_throws_an_exception_during_is_collectable(): void
    {
        $this->sut->shouldThrow(\LogicException::class)
                    ->during('isCollectable');
    }

    public function test_it_throws_an_exception_during_are_credantials_valid_combination_(): void
    {
        $this->sut->shouldThrow(\LogicException::class)
                    ->during('areCredentialsValidCombination');
    }
}
