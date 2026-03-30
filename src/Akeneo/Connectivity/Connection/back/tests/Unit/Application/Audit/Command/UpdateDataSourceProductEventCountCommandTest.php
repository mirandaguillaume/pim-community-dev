<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Audit\Command;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataSourceProductEventCountCommand;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateDataSourceProductEventCountCommandTest extends TestCase
{
    private UpdateDataSourceProductEventCountCommand $sut;

    protected function setUp(): void
    {
        $this->sut = new UpdateDataSourceProductEventCountCommand(HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        ));
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(UpdateDataSourceProductEventCountCommand::class, $this->sut);
    }

    public function test_it_returns_the_hourly_interval(): void
    {
        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        );
        $this->sut = new UpdateDataSourceProductEventCountCommand($hourlyInterval);
        $this->assertSame($hourlyInterval, $this->sut->hourlyInterval());
    }
}
