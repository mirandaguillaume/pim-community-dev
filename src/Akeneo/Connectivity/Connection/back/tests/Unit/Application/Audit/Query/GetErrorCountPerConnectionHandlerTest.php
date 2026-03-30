<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Application\Audit\Query\GetErrorCountPerConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\GetErrorCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCountPerConnection;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\SelectErrorCountPerConnectionQueryInterface;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetErrorCountPerConnectionHandlerTest extends TestCase
{
    private SelectErrorCountPerConnectionQueryInterface|MockObject $selectErrorCountPerConnectionQuery;
    private GetErrorCountPerConnectionHandler $sut;

    protected function setUp(): void
    {
        $this->selectErrorCountPerConnectionQuery = $this->createMock(SelectErrorCountPerConnectionQueryInterface::class);
        $this->sut = new GetErrorCountPerConnectionHandler($this->selectErrorCountPerConnectionQuery);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetErrorCountPerConnectionHandler::class, $this->sut);
    }

    public function test_it_handles_the_get_error_count(): void
    {
        $fromDateTime = new \DateTimeImmutable('2020-05-10 00:00:00', new \DateTimeZone('UTC'));
        $upToDateTime = new \DateTimeImmutable('2020-05-12 00:00:00', new \DateTimeZone('UTC'));
        $errorCountPerConnection = new ErrorCountPerConnection([
                    new ErrorCount('erp', 11),
                    new ErrorCount('bynder', 21),
                ]);
        $this->selectErrorCountPerConnectionQuery->method('execute')->with(ErrorTypes::BUSINESS, $fromDateTime, $upToDateTime)->willReturn($errorCountPerConnection);
        $query = new GetErrorCountPerConnectionQuery(ErrorTypes::BUSINESS, $fromDateTime, $upToDateTime);
        $this->assertSame($errorCountPerConnection, $this->sut->handle($query));
    }
}
