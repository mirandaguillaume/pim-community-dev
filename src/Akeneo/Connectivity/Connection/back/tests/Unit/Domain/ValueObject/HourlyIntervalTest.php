<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\ValueObject;

use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class HourlyIntervalTest extends TestCase
{
    private HourlyInterval $sut;

    protected function setUp(): void
    {
        $this->sut = HourlyInterval::createFromDateTime(new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC')), );
    }

    public function test_it_returns_from_datetime(): void
    {
        $this->sut = HourlyInterval::createFromDateTime(new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC')), );
        $this->assertEquals(new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC')), $this->sut->fromDateTime());
    }

    public function test_it_returns_up_to_datetime(): void
    {
        $this->sut = HourlyInterval::createFromDateTime(new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC')), );
        $this->assertEquals(new \DateTimeImmutable('2020-01-01 11:00:00', new \DateTimeZone('UTC')), $this->sut->upToDateTime());
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(HourlyInterval::class, $this->sut);
    }

    public function test_it_throws_when_the_timezone_is_not_utc(): void
    {
        $this->expectException('\InvalidArgumentException');
        HourlyInterval::createFromDateTime(new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('europe/paris')), );
    }

    public function test_it_compares_two_equals_hourly_intervals_created_with_the_same_hours(): void
    {
        $this->sut = HourlyInterval::createFromDateTime(new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC')), );
        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        );
        $this->assertSame(true, $this->sut->equals($hourlyInterval));
    }

    public function test_it_compares_two_equals_hourly_intervals_created_with_differents_hours(): void
    {
        $this->sut = HourlyInterval::createFromDateTime(new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC')), );
        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:59:59', new \DateTimeZone('UTC'))
        );
        $this->assertSame(true, $this->sut->equals($hourlyInterval));
    }

    public function test_it_compares_two_differents_hourly_intervals(): void
    {
        $this->sut = HourlyInterval::createFromDateTime(new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC')), );
        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 11:00:00', new \DateTimeZone('UTC'))
        );
        $this->assertSame(false, $this->sut->equals($hourlyInterval));
    }
}
