<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\ErrorManagement\Command;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountCommand;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateConnectionErrorCountCommandTest extends TestCase
{
    private HourlyErrorCount|MockObject $firstCount;
    private HourlyErrorCount|MockObject $secondCount;
    private UpdateConnectionErrorCountCommand $sut;

    protected function setUp(): void
    {
        $this->firstCount = $this->createMock(HourlyErrorCount::class);
        $this->secondCount = $this->createMock(HourlyErrorCount::class);
        $this->sut = new UpdateConnectionErrorCountCommand([$this->firstCount, $this->secondCount]);
    }

    public function test_it_is_an_update_connection_error_count_command(): void
    {
        $this->assertInstanceOf(UpdateConnectionErrorCountCommand::class, $this->sut);
    }

    public function test_it_provides_error_counts(): void
    {
        $this->assertSame([$this->firstCount, $this->secondCount], $this->sut->errorCounts());
    }
}
