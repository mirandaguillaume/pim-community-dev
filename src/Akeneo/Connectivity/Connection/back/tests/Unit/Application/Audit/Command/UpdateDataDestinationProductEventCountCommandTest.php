<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Audit\Command;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountCommand;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateDataDestinationProductEventCountCommandTest extends TestCase
{
    private UpdateDataDestinationProductEventCountCommand $sut;

    protected function setUp(): void
    {
        $this->sut = new UpdateDataDestinationProductEventCountCommand(
            'SAP',
            HourlyInterval::createFromDateTime(new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))),
            104
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(UpdateDataDestinationProductEventCountCommand::class, $this->sut);
    }

    public function test_it_returns_the_connection_code(): void
    {
        $this->assertSame('SAP', $this->sut->connectionCode());
    }

    public function test_it_returns_the_hourly_interval(): void
    {
        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        );
        $this->sut = new UpdateDataDestinationProductEventCountCommand('SAP', $hourlyInterval, 102);
        $this->assertSame($hourlyInterval, $this->sut->hourlyInterval());
    }

    public function test_it_returns_the_product_event_count(): void
    {
        $this->assertSame(104, $this->sut->productEventCount());
    }
}
