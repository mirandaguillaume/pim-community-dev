<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Application\Audit\Query\GetPeriodErrorCountPerConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\GetPeriodErrorCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\SelectPeriodErrorCountPerConnectionQueryInterface;
use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetPeriodErrorCountPerConnectionHandlerTest extends TestCase
{
    private SelectPeriodErrorCountPerConnectionQueryInterface|MockObject $selectPeriodErrorCountPerConnectionQuery;
    private GetPeriodErrorCountPerConnectionHandler $sut;

    protected function setUp(): void
    {
        $this->selectPeriodErrorCountPerConnectionQuery = $this->createMock(SelectPeriodErrorCountPerConnectionQueryInterface::class);
        $this->sut = new GetPeriodErrorCountPerConnectionHandler($this->selectPeriodErrorCountPerConnectionQuery);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetPeriodErrorCountPerConnectionHandler::class, $this->sut);
    }

    public function test_it_handles_the_query(): void
    {
        $period = new DateTimePeriod(
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC'))
        );
        $periodErrorCountPerConnection = [];
        $this->selectPeriodErrorCountPerConnectionQuery->method('execute')->with($period)->willReturn($periodErrorCountPerConnection);
        $query = new GetPeriodErrorCountPerConnectionQuery($period);
        $this->assertSame($periodErrorCountPerConnection, $this->sut->handle($query));
    }
}
