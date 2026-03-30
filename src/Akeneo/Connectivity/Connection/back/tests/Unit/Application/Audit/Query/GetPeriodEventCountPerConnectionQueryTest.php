<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Application\Audit\Query\GetPeriodEventCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetPeriodEventCountPerConnectionQueryTest extends TestCase
{
    private GetPeriodEventCountPerConnectionQuery $sut;

    protected function setUp(): void
    {
        $this->sut = new GetPeriodEventCountPerConnectionQuery(
            EventTypes::PRODUCT_CREATED,
            new DateTimePeriod(
                new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
                new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC'))
            )
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetPeriodEventCountPerConnectionQuery::class, $this->sut);
    }

    public function test_it_returns_the_event_type(): void
    {
        $this->assertSame(EventTypes::PRODUCT_CREATED, $this->sut->eventType());
    }

    public function test_it_returns_the_period(): void
    {
        $this->assertEquals(new DateTimePeriod(
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC'))
        ), $this->sut->period());
    }
}
