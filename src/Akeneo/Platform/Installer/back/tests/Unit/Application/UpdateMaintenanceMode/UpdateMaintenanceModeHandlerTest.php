<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Installer\Unit\Application\UpdateMaintenanceMode;

use Akeneo\Platform\Installer\Application\UpdateMaintenanceMode\UpdateMaintenanceModeCommand;
use Akeneo\Platform\Installer\Application\UpdateMaintenanceMode\UpdateMaintenanceModeHandler;
use Akeneo\Platform\Installer\Domain\Query\UpdateMaintenanceModeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateMaintenanceModeHandlerTest extends TestCase
{
    private UpdateMaintenanceModeInterface|MockObject $updateMaintenanceMode;
    private UpdateMaintenanceModeHandler $sut;

    protected function setUp(): void
    {
        $this->updateMaintenanceMode = $this->createMock(UpdateMaintenanceModeInterface::class);
        $this->sut = new UpdateMaintenanceModeHandler($this->updateMaintenanceMode);
    }

    public function test_it_is_instantiable(): void
    {
        $this->assertInstanceOf(UpdateMaintenanceModeHandler::class, $this->sut);
    }

    public function test_it_updates_maintenance_mode(): void
    {
        $this->updateMaintenanceMode->expects($this->once())->method('execute')->with(true);
        $this->sut->handle(new UpdateMaintenanceModeCommand(true));
    }
}
