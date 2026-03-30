<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Audit\Command;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\UpsertEventCountQueryInterface;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateDataDestinationProductEventCountHandlerTest extends TestCase
{
    private UpsertEventCountQueryInterface|MockObject $upsertEventCountQuery;
    private UpdateDataDestinationProductEventCountHandler $sut;

    protected function setUp(): void
    {
        $this->upsertEventCountQuery = $this->createMock(UpsertEventCountQueryInterface::class);
        $this->sut = new UpdateDataDestinationProductEventCountHandler($this->upsertEventCountQuery);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(UpdateDataDestinationProductEventCountHandler::class, $this->sut);
    }

    public function test_it_saves_data_destination_product_event_count(): void
    {
        $command = new UpdateDataDestinationProductEventCountCommand(
            'ecommerce',
            HourlyInterval::createFromDateTime(new \DateTimeImmutable('now', new \DateTimeZone('UTC'))),
            3
        );
        $hourlyEventCount = new HourlyEventCount(
            $command->connectionCode(),
            $command->hourlyInterval(),
            $command->productEventCount(),
            EventTypes::PRODUCT_READ
        );
        $this->upsertEventCountQuery->expects($this->once())->method('execute')->with($hourlyEventCount);
        $this->sut->handle($command);
    }
}
