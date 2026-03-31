<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductMassActionRepositoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponseInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Ajax\DeleteMassAction;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvent;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvents;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler\DeleteMassActionHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeleteMassActionHandlerTest extends TestCase
{
    private HydratorInterface|MockObject $hydrator;
    private TranslatorInterface|MockObject $translator;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private DatagridInterface|MockObject $datagrid;
    private DatasourceInterface|MockObject $datasource;
    private DeleteMassAction|MockObject $massAction;
    private ActionConfiguration|MockObject $options;
    private ProductMassActionRepositoryInterface|MockObject $massActionRepo;
    private DeleteMassActionHandler $sut;

    protected function setUp(): void
    {
        $this->hydrator = $this->createMock(HydratorInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->datagrid = $this->createMock(DatagridInterface::class);
        $this->datasource = $this->createMock(DatasourceInterface::class);
        $this->massAction = $this->createMock(DeleteMassAction::class);
        $this->options = $this->createMock(ActionConfiguration::class);
        $this->massActionRepo = $this->createMock(ProductMassActionRepositoryInterface::class);
        $this->sut = new DeleteMassActionHandler($this->hydrator, $this->translator, $this->eventDispatcher);
        $this->translator->method('trans')->willReturnArgument(0);
        $this->datagrid->method('getDatasource')->willReturn($this->datasource);
        $this->datasource->expects($this->once())->method('setHydrator')->with($this->hydrator);
        $this->datasource->method('getMassActionRepository')->willReturn($this->massActionRepo);
        $this->massAction->method('getOptions')->willReturn($this->options);
        $this->options->method('offsetGetByPath')->willReturn('qux');
    }

    public function test_it_handles_delete_mass_action(): void
    {
        $objectIds = ['foo', 'bar', 'baz'];
        $countRemoved = count($objectIds);
        $this->datasource->method('getResults')->willReturn($objectIds);
        $this->massActionRepo->method('deleteFromIds')->with($objectIds)->willReturn($countRemoved);
        $this->eventDispatcher->expects($this->exactly(2))->method('dispatch')->with(
            $this->isInstanceOf(MassActionEvent::class),
            $this->anything()
        );
        $this->sut->handle($this->datagrid, $this->massAction);
    }

    public function test_it_dispatches_events(): void
    {
        $objectIds = ['foo', 'bar', 'baz'];
        $countRemoved = count($objectIds);
        $this->datasource->method('getResults')->willReturn($objectIds);
        $this->massActionRepo->method('deleteFromIds')->with($objectIds)->willReturn($countRemoved);
        $dispatched = [];
        $this->eventDispatcher->expects($this->exactly(2))->method('dispatch')->willReturnCallback(
            function ($event, $eventName) use (&$dispatched) {
                $dispatched[] = $eventName;
                return $event;
            }
        );
        $this->sut->handle($this->datagrid, $this->massAction);
        $this->assertSame(MassActionEvents::MASS_DELETE_PRE_HANDLER, $dispatched[0]);
        $this->assertSame(MassActionEvents::MASS_DELETE_POST_HANDLER, $dispatched[1]);
    }

    public function test_it_returns_successful_response(): void
    {
        $objectIds = ['foo', 'bar', 'baz'];
        $countRemoved = count($objectIds);
        $this->datasource->method('getResults')->willReturn($objectIds);
        $this->massActionRepo->method('deleteFromIds')->with($objectIds)->willReturn($countRemoved);
        $this->eventDispatcher->expects($this->exactly(2))->method('dispatch')->with(
            $this->isInstanceOf(MassActionEvent::class),
            $this->anything()
        );
        $result = $this->sut->handle($this->datagrid, $this->massAction);
        $this->assertInstanceOf(MassActionResponseInterface::class, $result);
    }

    public function test_it_returns_failed_message_if_an_exception_occurs(): void
    {
        $objectIds = ['foo', 'bar', 'baz'];
        $errorMessage = 'Error';
        $e = new \Exception($errorMessage);
        $this->datasource->method('getResults')->willReturn($objectIds);
        $this->massActionRepo->method('deleteFromIds')->with($objectIds)->willThrowException($e);
        // Only the pre_handler event should be dispatched (exception interrupts before post_handler)
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with(
            $this->isInstanceOf(MassActionEvent::class),
            MassActionEvents::MASS_DELETE_PRE_HANDLER
        );
        $result = $this->sut->handle($this->datagrid, $this->massAction);
        $this->assertInstanceOf(MassActionResponseInterface::class, $result);
    }
}
