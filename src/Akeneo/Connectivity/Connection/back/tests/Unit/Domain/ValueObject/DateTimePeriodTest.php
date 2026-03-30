<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\ValueObject;

use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DateTimePeriodTest extends TestCase
{
    private DateTimePeriod $sut;

    protected function setUp(): void
    {
        $this->sut = new DateTimePeriod(
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')),
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(DateTimePeriod::class, $this->sut);
    }

    public function test_it_returns_start_datetime(): void
    {
        $this->assertEquals(new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')), $this->sut->start());
    }

    public function test_it_returns_end_datetime(): void
    {
        $this->assertEquals(new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')), $this->sut->end());
    }

    public function test_it_throws_when_the_start_datetime_timezone_is_not_utc(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DateTimePeriod(
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('Europe/Paris')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')),
        );
    }

    public function test_it_throws_when_the_end_datetime_timezone_is_not_utc(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DateTimePeriod(
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('Europe/Paris')),
        );
    }
}
