<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\ErrorManagement\Model\Write;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use PHPUnit\Framework\TestCase;

class HourlyErrorCountTest extends TestCase
{
    private HourlyErrorCount $sut;

    protected function setUp(): void
    {
        $this->sut = new HourlyErrorCount(
            'magento',
            HourlyInterval::createFromDateTime(
                new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
            ),
            329,
            ErrorTypes::BUSINESS
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(HourlyErrorCount::class, $this->sut);
    }

    public function test_it_returns_the_connection_code(): void
    {
        $connectionCode = $this->sut->connectionCode();
        $this->assertInstanceOf(ConnectionCode::class, $connectionCode);
        $this->assertSame('magento', (string) $connectionCode);
    }

    public function test_it_returns_the_hourly_interval(): void
    {
        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        );
        $this->sut = new HourlyErrorCount(
            'magento',
            $hourlyInterval,
            329,
            ErrorTypes::BUSINESS
        );
        $this->assertSame($hourlyInterval, $this->sut->hourlyInterval());
    }

    public function test_it_returns_the_error_count(): void
    {
        $this->assertSame(329, $this->sut->errorCount());
    }

    public function test_it_returns_the_error_type(): void
    {
        $errorType = $this->sut->errorType();
        $this->assertInstanceOf(ErrorType::class, $errorType);
        $this->assertSame('business', (string) $errorType);
    }

    public function test_it_validates_that_the_count_is_positive(): void
    {
        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        );
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('The error count must be positive. Negative number "-5" given.');
        new HourlyErrorCount('erp', $hourlyInterval, -5, ErrorTypes::BUSINESS);
    }

    public function test_it_validates_the_error_type(): void
    {
        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        );
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('The given error type "Error" is unknown.');
        new HourlyErrorCount('erp', $hourlyInterval, 12, 'Error');
    }
}
