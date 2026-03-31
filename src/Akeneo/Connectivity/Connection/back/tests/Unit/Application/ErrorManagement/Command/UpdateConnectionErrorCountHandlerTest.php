<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\ErrorManagement\Command;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountCommand;
use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountHandler;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\ErrorCountRepositoryInterface;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateConnectionErrorCountHandlerTest extends TestCase
{
    private ErrorCountRepositoryInterface|MockObject $errorCountRepository;
    private UpdateConnectionErrorCountHandler $sut;

    protected function setUp(): void
    {
        $this->errorCountRepository = $this->createMock(ErrorCountRepositoryInterface::class);
        $this->sut = new UpdateConnectionErrorCountHandler($this->errorCountRepository);
    }

    public function test_it_is_an_update_connection_error_count_handler(): void
    {
        $this->assertInstanceOf(UpdateConnectionErrorCountHandler::class, $this->sut);
    }

    public function test_it_updates_error_counts(): void
    {
        $firstCount = new HourlyErrorCount(
            'erp',
            HourlyInterval::createFromDateTime(new \DateTime('now')),
            2,
            ErrorTypes::BUSINESS
        );
        $secondCount = new HourlyErrorCount(
            'erp',
            HourlyInterval::createFromDateTime(new \DateTime('now')),
            2,
            ErrorTypes::TECHNICAL
        );
        $command = new UpdateConnectionErrorCountCommand([$firstCount, $secondCount]);
        $upsertedCounts = [];
        $this->errorCountRepository->expects($this->exactly(2))->method('upsert')->willReturnCallback(
            function (HourlyErrorCount $count) use (&$upsertedCounts) {
                $upsertedCounts[] = $count;
            }
        );
        $this->sut->handle($command);
        $this->assertSame($firstCount, $upsertedCounts[0]);
        $this->assertSame($secondCount, $upsertedCounts[1]);
    }

    public function test_it_does_not_update_a_0_count(): void
    {
        $firstCount = new HourlyErrorCount(
            'erp',
            HourlyInterval::createFromDateTime(new \DateTime('now')),
            2,
            ErrorTypes::BUSINESS
        );
        $secondCount = new HourlyErrorCount(
            'erp',
            HourlyInterval::createFromDateTime(new \DateTime('now')),
            0,
            ErrorTypes::TECHNICAL
        );
        $command = new UpdateConnectionErrorCountCommand([$firstCount, $secondCount]);
        $this->errorCountRepository->expects($this->once())->method('upsert')->with($firstCount);
        $this->sut->handle($command);
    }
}
