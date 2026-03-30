<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Redirect\EditMassAction;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvent;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvents;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler\EditMassActionHandler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EditMassActionHandlerTest extends TestCase
{
    private HydratorInterface|MockObject $hydrator;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private EditMassActionHandler $sut;

    protected function setUp(): void
    {
        $this->hydrator = $this->createMock(HydratorInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->sut = new EditMassActionHandler($this->hydrator, $this->eventDispatcher);
    }

    public function test_it_handles_edit_mass_action(): void
    {
        $datagrid = $this->createMock(DatagridInterface::class);
        $datasource = $this->createMock(DatasourceInterface::class);
        $massAction = $this->createMock(EditMassAction::class);

        $objectIds = ['foo', 'bar', 'baz'];
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with(
            $this->isInstanceOf(MassActionEvent::class),
            MassActionEvents::MASS_EDIT_PRE_HANDLER
        );
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with(
            $this->isInstanceOf(MassActionEvent::class),
            MassActionEvents::MASS_EDIT_POST_HANDLER
        );
        $datagrid->method('getDatasource')->willReturn($datasource);
        $datasource->expects($this->once())->method('setHydrator')->with($this->hydrator);
        $datasource->method('getResults')->willReturn($objectIds);
        $this->assertSame($objectIds, $this->sut->handle($datagrid, $massAction));
    }
}
