<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Audit\Model\Write;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre.holly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class HourlyEventCountTest extends TestCase
{
    private HourlyEventCount $sut;

    protected function setUp(): void
    {
        $this->sut = new HourlyEventCount(
            'magento',
            HourlyInterval::createFromDateTime(
                new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
            ),
            329,
            EventTypes::PRODUCT_CREATED
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(HourlyEventCount::class, $this->sut);
    }

    public function test_it_returns_the_connection_code(): void
    {
        $this->assertSame('magento', $this->sut->connectionCode());
    }

    public function test_it_returns_the_hourly_interval(): void
    {
        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        );
        $this->sut = new HourlyEventCount(
            'magento',
            $hourlyInterval,
            329,
            EventTypes::PRODUCT_CREATED
        );
        $this->assertSame($hourlyInterval, $this->sut->hourlyInterval());
    }

    public function test_it_returns_the_event_count(): void
    {
        $this->assertSame(329, $this->sut->eventCount());
    }

    public function test_it_returns_the_event_type(): void
    {
        $this->assertSame(EventTypes::PRODUCT_CREATED, $this->sut->eventType());
    }
}
