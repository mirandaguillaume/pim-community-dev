<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Settings\Query;

use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionWithCredentialsByCodeQueryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FindAConnectionHandlerTest extends TestCase
{
    private SelectConnectionWithCredentialsByCodeQueryInterface|MockObject $selectConnectionWithCredentialsByCodeQuery;
    private FindAConnectionHandler $sut;

    protected function setUp(): void
    {
        $this->selectConnectionWithCredentialsByCodeQuery = $this->createMock(SelectConnectionWithCredentialsByCodeQueryInterface::class);
        $this->sut = new FindAConnectionHandler($this->selectConnectionWithCredentialsByCodeQuery);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FindAConnectionHandler::class, $this->sut);
    }

    public function test_it_returns_a_connection(): void
    {
        $connection = new ConnectionWithCredentials(
            'bynder',
            'Bynder DAM',
            FlowType::OTHER,
            null,
            'client_id',
            'secret',
            'username',
            'user_role_id',
            'user_group_id',
            true,
            'default'
        );
        $this->selectConnectionWithCredentialsByCodeQuery->method('execute')->with('bynder')->willReturn($connection);
        $query = new FindAConnectionQuery('bynder');
        $this->assertSame($connection, $this->sut->handle($query));
    }

    public function test_it_returns_null_when_the_connection_does_not_exists(): void
    {
        $this->selectConnectionWithCredentialsByCodeQuery->method('execute')->with('bynder')->willReturn(null);
        $query = new FindAConnectionQuery('bynder');
        $this->assertNull($this->sut->handle($query));
    }
}
