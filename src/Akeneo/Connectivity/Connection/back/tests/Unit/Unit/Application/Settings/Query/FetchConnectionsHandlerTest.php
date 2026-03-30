<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Settings\Query;

use Akeneo\Connectivity\Connection\Application\Settings\Query\FetchConnectionsHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FetchConnectionsQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionsQueryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FetchConnectionsHandlerTest extends TestCase
{
    private SelectConnectionsQueryInterface|MockObject $selectConnectionsQuery;
    private FetchConnectionsHandler $sut;

    protected function setUp(): void
    {
        $this->selectConnectionsQuery = $this->createMock(SelectConnectionsQueryInterface::class);
        $this->sut = new FetchConnectionsHandler($this->selectConnectionsQuery);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FetchConnectionsHandler::class, $this->sut);
    }

    public function test_it_fetches_connections(): void
    {
        $connections = [
                    new Connection('42', 'magento', 'Magento Connector', FlowType::DATA_DESTINATION, true),
                    new Connection('43', 'bynder', 'Bynder DAM', FlowType::OTHER, false),
                ];
        $this->selectConnectionsQuery->method('execute')->with([ConnectionType::DEFAULT_TYPE])->willReturn($connections);
        $query = new FetchConnectionsQuery(['types' => [ConnectionType::DEFAULT_TYPE]]);
        $this->assertSame($connections, $this->sut->handle($query));
    }
}
