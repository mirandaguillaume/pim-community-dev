<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PeriodEventCountTest extends TestCase
{
    private PeriodEventCount $sut;

    protected function setUp(): void
    {
        $this->sut = new PeriodEventCount(
            'magento',
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')),
            [
                new HourlyEventCount(
                    new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
                    1
                ),
            ]
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(PeriodEventCount::class, $this->sut);
    }

    public function test_it_returns_the_connection_code(): void
    {
        $this->assertSame('magento', $this->sut->connectionCode());
    }

    public function test_it_returns_the_from_date_time(): void
    {
        $this->assertEquals(new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')), $this->sut->fromDateTime());
    }

    public function test_it_returns_the_up_to_date_time(): void
    {
        $this->assertEquals(new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')), $this->sut->upToDateTime());
    }

    public function test_it_returns_the_hourly_event_counts(): void
    {
        $this->assertEquals([
                    new HourlyEventCount(
                        new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
                        1
                    ),
                ], $this->sut->hourlyEventCounts());
    }
}
