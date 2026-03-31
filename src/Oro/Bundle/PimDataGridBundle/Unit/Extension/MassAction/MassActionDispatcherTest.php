<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Extension\MassAction;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductMassActionRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\ManagerInterface;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionExtension;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductDatasource;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\MassActionDispatcher;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\MassActionHandlerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class MassActionDispatcherTest extends TestCase
{
    private MassActionHandlerRegistry|MockObject $handlerRegistry;
    private ManagerInterface|MockObject $manager;
    private RequestParameters|MockObject $requestParams;
    private MassActionParametersParser|MockObject $parametersParser;
    private DatagridInterface|MockObject $grid;
    private Acceptor|MockObject $acceptor;
    private DatasourceInterface|MockObject $acceptedDatasource;
    private ProductDatasource|MockObject $datasource;
    private QueryBuilder|MockObject $queryBuilder;
    private MassActionDispatcher $sut;

    protected function setUp(): void
    {
        $this->handlerRegistry = $this->createMock(MassActionHandlerRegistry::class);
        $this->manager = $this->createMock(ManagerInterface::class);
        $this->requestParams = $this->createMock(RequestParameters::class);
        $this->parametersParser = $this->createMock(MassActionParametersParser::class);
        $this->grid = $this->createMock(DatagridInterface::class);
        $this->acceptor = $this->createMock(Acceptor::class);
        $this->acceptedDatasource = $this->createMock(DatasourceInterface::class);
        $this->datasource = $this->createMock(ProductDatasource::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->sut = new MassActionDispatcher($this->handlerRegistry, $this->manager, $this->requestParams, $this->parametersParser, ['product-grid']);
        $this->acceptedDatasource->method('getQueryBuilder')->willReturn($this->queryBuilder);
        $this->grid->method('getAcceptor')->willReturn($this->acceptor);
        $this->grid->method('getAcceptedDatasource')->willReturn($this->acceptedDatasource);
        $this->grid->method('getDatasource')->willReturn($this->datasource);
        $this->manager->method('getDatagrid')->with('grid')->willReturn($this->grid);
    }

    public function test_it_returns_mass_action(): void
    {
        $massActionExtension = $this->createMock(MassActionExtension::class);
        $massActionInterface = $this->createMock(MassActionInterface::class);
        $massActionRepository = $this->createMock(ProductMassActionRepositoryInterface::class);
        $massActionHandler = $this->createMock(MassActionHandlerInterface::class);

        $request = new Request([
                    'inset'      => 'inset',
                    'values'     => [1],
                    'gridName'   => 'grid',
                    'massAction' => $massActionInterface,
                    'actionName' => 'mass_edit_action',
                ]);
        $this->parametersParser->method('parse')->with($request)->willReturn([
                    'inset' => 'inset',
                    'values' => [1],
                    'gridName'   => 'grid',
                    'massAction' => $massActionInterface,
                    'actionName' => 'mass_edit_action',
                ]);
        $this->datasource->method('getMassActionRepository')->willReturn($massActionRepository);
        $massActionRepository->method('applyMassActionParameters')->with($this->queryBuilder, 'inset', [1])->willReturn(null);
        $massActionExtension->method('getMassAction')->with('mass_edit_action', $this->grid)->willReturn($massActionInterface);
        $this->acceptor->method('getExtensions')->willReturn([$massActionExtension]);
        $alias = 'mass_action_alias';
        $options = new ArrayCollection();
        $options->offsetSet('handler', $alias);
        $massActionInterface->method('getOptions')->willReturn($options);
        $this->handlerRegistry->method('getHandler')->with($alias)->willReturn($massActionHandler);
        $massActionHandler->method('handle')->with($this->grid, $massActionInterface)->willReturn($massActionHandler);
        $result = $this->sut->dispatch([
                    'inset' => 'inset',
                    'values' => [1],
                    'gridName'   => 'grid',
                    'massAction' => $massActionInterface,
                    'actionName' => 'mass_edit_action',
                ]);
        $this->assertInstanceOf(MassActionHandlerInterface::class, $result);
    }

    public function test_it_throws_an_exception_without_extension(): void
    {
        $massActionRepository = $this->createMock(ProductMassActionRepositoryInterface::class);

        $request = new Request([
                    'inset'      => 'inset',
                    'values'     => [1],
                    'gridName'   => 'grid',
                    'actionName' => 'mass_edit_action',
                ]);
        $this->parametersParser->method('parse')->with($request)->willReturn([
                    'inset'      => 'inset',
                    'values'     => [1],
                    'gridName'   => 'grid',
                    'actionName' => 'mass_edit_action']);
        $this->acceptor->method('getExtensions')->willReturn([]);
        $this->datasource->method('getMassActionRepository')->willReturn($massActionRepository);
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('MassAction extension is not applied to datagrid.');
        $this->sut->dispatch([
                        'inset'      => 'inset',
                        'values'     => [1],
                        'gridName'   => 'grid',
                        'actionName' => 'mass_edit_action',
                    ]);
    }

    public function test_it_throws_an_exception_when_the_mass_action_does_not_exist(): void
    {
        $massActionExtension = $this->createMock(MassActionExtension::class);
        $massActionInterface = $this->createMock(MassActionInterface::class);
        $massActionRepository = $this->createMock(ProductMassActionRepositoryInterface::class);

        $massActionName = 'mass_edit_action';
        $request = new Request([
                    'inset'      => 'inset',
                    'values'     => [1],
                    'gridName'   => 'grid',
                    'massAction' => $massActionInterface,
                    'actionName' => $massActionName,
                ]);
        $this->parametersParser->method('parse')->with($request)->willReturn([
                    'inset'      => 'inset',
                    'values'     => [1],
                    'gridName'   => 'grid',
                    'massAction' => $massActionInterface,
                    'actionName' => $massActionName,
                ]);
        $this->datasource->method('getMassActionRepository')->willReturn($massActionRepository);
        $this->acceptor->method('getExtensions')->willReturn([$massActionExtension]);
        $massActionExtension->method('getMassAction')->with($massActionName, $this->grid)->willReturn(false);
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(sprintf('Can\'t find mass action "%s"', $massActionName));
        $this->sut->dispatch([
                        'inset'      => 'inset',
                        'values'     => [1],
                        'gridName'   => 'grid',
                        'massAction' => $massActionInterface,
                        'actionName' => $massActionName,
                    ]);
    }

    public function test_it_throws_an_exception_without_values(): void
    {
        $massActionName = 'mass_edit_action';
        $request = new Request([
                    'actionName' => $massActionName,
                ]);
        $this->parametersParser->method('parse')->with($request)->willReturn(['inset' => 'inset', 'values' => '']);
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(sprintf('There is nothing to do in mass action "%s"', $massActionName));
        $this->sut->dispatch(['inset' => 'inset', 'values' => '', 'actionName' => $massActionName]);
    }

    public function test_it_throws_an_exception_if_datasource_is_not_an_instance_of_productdatasource(): void
    {
        $massActionExtension = $this->createMock(MassActionExtension::class);
        $massActionInterface = $this->createMock(MassActionInterface::class);
        $massActionRepository = $this->createMock(ProductMassActionRepositoryInterface::class);
        // Create a separate dispatcher with a grid that uses a non-ProductDatasource
        $plainDatasource = $this->createMock(DatasourceInterface::class);
        $grid = $this->createMock(DatagridInterface::class);
        $acceptor = $this->createMock(Acceptor::class);
        $acceptedDatasource = $this->createMock(DatasourceInterface::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $grid->method('getAcceptor')->willReturn($acceptor);
        $grid->method('getAcceptedDatasource')->willReturn($acceptedDatasource);
        $grid->method('getDatasource')->willReturn($plainDatasource);
        $acceptedDatasource->method('getQueryBuilder')->willReturn($queryBuilder);
        $plainDatasource->method('getMassActionRepository')->willReturn($massActionRepository);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->method('getDatagrid')->with('grid')->willReturn($grid);
        $sut = new MassActionDispatcher($this->handlerRegistry, $manager, $this->requestParams, $this->parametersParser, ['product-grid']);

        $massActionName = 'mass_edit_action';
        $massActionExtension->method('getMassAction')->with($massActionName, $grid)->willReturn($massActionInterface);
        $acceptor->method('getExtensions')->willReturn([$massActionExtension]);
        $this->expectException(\LogicException::class);
        $sut->getRawFilters([
                        'inset'      => 'inset',
                        'values'     => [1],
                        'gridName'   => 'grid',
                        'massAction' => $massActionInterface,
                        'actionName' => $massActionName,
                    ]);
    }

    public function test_it_convert_parent_filter_when_all_rows_are_selected(): void
    {
        $massActionExtension = $this->createMock(MassActionExtension::class);
        $massActionInterface = $this->createMock(MassActionInterface::class);
        $massActionRepository = $this->createMock(ProductMassActionRepositoryInterface::class);
        $productQueryBuilder = $this->createMock(ProductQueryBuilderInterface::class);

        $massActionName = 'mass_edit_action';
        $request = new Request([
                    'inset'      => 'inset',
                    'values'     => [1],
                    'gridName'   => 'grid',
                    'massAction' => $massActionInterface,
                    'actionName' => 'mass_edit_action',
                ]);
        $this->parametersParser->method('parse')->with($request)->willReturn([
                    'inset' => 'inset',
                    'values' => [1],
                    'gridName'   => 'grid',
                    'massAction' => $massActionInterface,
                    'actionName' => 'mass_edit_action',
                ]);
        $this->datasource->method('getMassActionRepository')->willReturn($massActionRepository);
        $massActionRepository->method('applyMassActionParameters')->with($this->queryBuilder, '', [1])->willReturn(null);
        $massActionExtension->method('getMassAction')->with('mass_edit_action', $this->grid)->willReturn($massActionInterface);
        $this->acceptor->method('getExtensions')->willReturn([$massActionExtension]);
        $this->datasource->method('getProductQueryBuilder')->willReturn($productQueryBuilder);
        $productQueryBuilder->method('getRawFilters')->willReturn([
                    [
                        'field' => 'parent',
                        'operator' => 'IN',
                        'values' => ['CODE1', 'CODE2'],
                    ],
                ]);
        $this->datasource->method('getParameters')->willReturn(null);
        $this->assertSame([
                    [
                        'field' => 'ancestor.code',
                        'operator' => 'IN',
                        'values' => ['CODE1', 'CODE2'],
                        'context' => [],
                    ],
                ], $this->sut->getRawFilters([
                    'inset'      => '',
                    'values'     => [1],
                    'gridName'   => 'grid',
                    'massAction' => $massActionInterface,
                    'actionName' => $massActionName,
                ]));
    }

    public function test_it_does_not_convert_empty_parent_filter_when_all_rows_are_selected(): void
    {
        $massActionExtension = $this->createMock(MassActionExtension::class);
        $massActionInterface = $this->createMock(MassActionInterface::class);
        $massActionRepository = $this->createMock(ProductMassActionRepositoryInterface::class);
        $productQueryBuilder = $this->createMock(ProductQueryBuilderInterface::class);

        $massActionName = 'mass_edit_action';
        $request = new Request([
                    'inset'      => 'inset',
                    'values'     => [1],
                    'gridName'   => 'grid',
                    'massAction' => $massActionInterface,
                    'actionName' => 'mass_edit_action',
                ]);
        $this->parametersParser->method('parse')->with($request)->willReturn([
                    'inset' => 'inset',
                    'values' => [1],
                    'gridName'   => 'grid',
                    'massAction' => $massActionInterface,
                    'actionName' => 'mass_edit_action',
                ]);
        $this->datasource->method('getMassActionRepository')->willReturn($massActionRepository);
        $massActionRepository->method('applyMassActionParameters')->with($this->queryBuilder, '', [1])->willReturn(null);
        $massActionExtension->method('getMassAction')->with('mass_edit_action', $this->grid)->willReturn($massActionInterface);
        $this->acceptor->method('getExtensions')->willReturn([$massActionExtension]);
        $this->datasource->method('getProductQueryBuilder')->willReturn($productQueryBuilder);
        $productQueryBuilder->method('getRawFilters')->willReturn([
                    [
                        'field' => 'parent',
                        'operator' => 'EMPTY',
                        'values' => "",
                    ],
                ]);
        $this->datasource->method('getParameters')->willReturn(null);
        $this->assertSame([
                    [
                        'field' => 'parent',
                        'operator' => 'EMPTY',
                        'values' => "",
                        'context' => [],
                    ],
                ], $this->sut->getRawFilters([
                    'inset'      => '',
                    'values'     => [1],
                    'gridName'   => 'grid',
                    'massAction' => $massActionInterface,
                    'actionName' => $massActionName,
                ]));
    }

    public function test_it_does_not_convert_parent_filter_when_using_sequential_edit(): void
    {
        $massActionExtension = $this->createMock(MassActionExtension::class);
        $massActionInterface = $this->createMock(MassActionInterface::class);
        $massActionRepository = $this->createMock(ProductMassActionRepositoryInterface::class);
        $productQueryBuilder = $this->createMock(ProductQueryBuilderInterface::class);

        $massActionName = 'sequential_edit';
        $request = new Request([
                    'inset'      => 'inset',
                    'values'     => [1],
                    'gridName'   => 'grid',
                    'massAction' => $massActionInterface,
                    'actionName' => $massActionName,
                ]);
        $this->parametersParser->method('parse')->with($request)->willReturn([
                    'inset' => 'inset',
                    'values' => [1],
                    'gridName'   => 'grid',
                    'massAction' => $massActionInterface,
                    'actionName' => $massActionName,
                ]);
        $this->datasource->method('getMassActionRepository')->willReturn($massActionRepository);
        $massActionRepository->method('applyMassActionParameters')->with($this->queryBuilder, '', [1])->willReturn(null);
        $massActionExtension->method('getMassAction')->with('sequential_edit', $this->grid)->willReturn($massActionInterface);
        $this->acceptor->method('getExtensions')->willReturn([$massActionExtension]);
        $this->datasource->method('getProductQueryBuilder')->willReturn($productQueryBuilder);
        $productQueryBuilder->method('getRawFilters')->willReturn([
                    [
                        'field' => 'parent',
                        'operator' => 'IN',
                        'values' => ['CODE1', 'CODE2'],
                    ],
                ]);
        $this->datasource->method('getParameters')->willReturn(null);
        $this->assertSame([
                    [
                        'field' => 'parent',
                        'operator' => 'IN',
                        'values' => ['CODE1', 'CODE2'],
                        'context' => [],
                    ],
                ], $this->sut->getRawFilters([
                    'inset'      => '',
                    'values'     => [1],
                    'gridName'   => 'grid',
                    'massAction' => $massActionInterface,
                    'actionName' => $massActionName,
                ]));
    }
}
