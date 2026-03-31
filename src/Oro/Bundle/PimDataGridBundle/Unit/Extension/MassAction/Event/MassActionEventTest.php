<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

class MassActionEventTest extends TestCase
{
    private DatagridInterface|MockObject $datagrid;
    private MassActionInterface|MockObject $massAction;
    private MassActionEvent $sut;

    protected function setUp(): void
    {
        $this->datagrid = $this->createMock(DatagridInterface::class);
        $this->massAction = $this->createMock(MassActionInterface::class);
        $this->sut = new MassActionEvent($this->datagrid, $this->massAction, ['foo']);
    }

    public function test_it_is_an_event(): void
    {
        $this->assertInstanceOf(Event::class, $this->sut);
    }

    public function test_it_returns_datagrid(): void
    {
        $this->assertSame($this->datagrid, $this->sut->getDatagrid());
    }

    public function test_it_returns_mass_action(): void
    {
        $this->assertSame($this->massAction, $this->sut->getMassAction());
    }

    public function test_it_returns_objects(): void
    {
        $this->assertSame(['foo'], $this->sut->getObjects());
    }
}
