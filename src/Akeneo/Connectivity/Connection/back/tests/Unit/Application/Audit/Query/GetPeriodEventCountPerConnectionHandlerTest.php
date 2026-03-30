<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Application\Audit\Query\GetPeriodEventCountPerConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\GetPeriodEventCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\SelectPeriodEventCountPerConnectionQueryInterface;
use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetPeriodEventCountPerConnectionHandlerTest extends TestCase
{
    private SelectPeriodEventCountPerConnectionQueryInterface|MockObject $selectPeriodEventCountsQuery;
    private GetPeriodEventCountPerConnectionHandler $sut;

    protected function setUp(): void
    {
        $this->selectPeriodEventCountsQuery = $this->createMock(SelectPeriodEventCountPerConnectionQueryInterface::class);
        $this->sut = new GetPeriodEventCountPerConnectionHandler($this->selectPeriodEventCountsQuery);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetPeriodEventCountPerConnectionHandler::class, $this->sut);
    }

    public function test_it_handles_the_event_count(): void
    {
        $period = new DateTimePeriod(
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC'))
        );
        $periodEventCounts = [
                    new PeriodEventCount('erp', $period->start(), $period->end(), []),
                ];
        $this->selectPeriodEventCountsQuery->method('execute')->with(EventTypes::PRODUCT_CREATED, $period)->willReturn($periodEventCounts);
        $query = new GetPeriodEventCountPerConnectionQuery(EventTypes::PRODUCT_CREATED, $period);
        $this->assertSame($periodEventCounts, $this->sut->handle($query));
    }
}
