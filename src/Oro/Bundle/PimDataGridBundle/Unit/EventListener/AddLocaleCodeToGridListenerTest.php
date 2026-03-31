<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\EventListener;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use Oro\Bundle\PimDataGridBundle\Datasource\Datasource;
use Oro\Bundle\PimDataGridBundle\EventListener\AddLocaleCodeToGridListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddLocaleCodeToGridListenerTest extends TestCase
{
    private RequestParameters|MockObject $requestParams;
    private AddLocaleCodeToGridListener $sut;

    protected function setUp(): void
    {
        $this->requestParams = $this->createMock(RequestParameters::class);
        $this->sut = new AddLocaleCodeToGridListener($this->requestParams);
    }

    public function test_it_adds_locale_parameter_to_query_builder(): void
    {
        $event = $this->createMock(BuildAfter::class);
        $datagrid = $this->createMock(DatagridInterface::class);
        $acceptor = $this->createMock(Acceptor::class);
        $config = $this->createMock(DatagridConfiguration::class);
        $datasource = $this->createMock(Datasource::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $event->method('getDatagrid')->willReturn($datagrid);
        $datagrid->method('getDatasource')->willReturn($datasource);
        $datagrid->method('getAcceptor')->willReturn($acceptor);
        $acceptor->method('getConfig')->willReturn($config);
        $config->method('offsetGetByPath')->with('[options][locale_parameter]')->willReturn('dataLocale');
        $datasource->method('getQueryBuilder')->willReturn($queryBuilder);
        $this->requestParams->method('get')->with('dataLocale', null)->willReturn('fr_FR');
        $queryBuilder->method('setParameter')->with('dataLocale', 'fr_FR')->willReturn($queryBuilder);
        $this->sut->onBuildAfter($event);
    }

    public function test_it_does_nothing_when_locale_parameter_is_not_set(): void
    {
        $event = $this->createMock(BuildAfter::class);
        $datagrid = $this->createMock(DatagridInterface::class);
        $acceptor = $this->createMock(Acceptor::class);
        $config = $this->createMock(DatagridConfiguration::class);
        $datasource = $this->createMock(Datasource::class);

        $event->method('getDatagrid')->willReturn($datagrid);
        $datagrid->method('getDatasource')->willReturn($datasource);
        $datagrid->method('getAcceptor')->willReturn($acceptor);
        $acceptor->method('getConfig')->willReturn($config);
        $config->method('offsetGetByPath')->with('[options][locale_parameter]')->willReturn(null);
        $this->requestParams->expects($this->never())->method('get');
        $this->sut->onBuildAfter($event);
    }

    public function test_it_does_nothing_when_datasource_is_not_an_orm_datasource(): void
    {
        $event = $this->createMock(BuildAfter::class);
        $datagrid = $this->createMock(DatagridInterface::class);
        $acceptor = $this->createMock(Acceptor::class);
        $config = $this->createMock(DatagridConfiguration::class);
        $datasource = $this->createMock(DatasourceInterface::class);

        $event->method('getDatagrid')->willReturn($datagrid);
        $datagrid->method('getDatasource')->willReturn($datasource);
        $datagrid->method('getAcceptor')->willReturn($acceptor);
        $acceptor->method('getConfig')->willReturn($config);
        $config->method('offsetGetByPath')->with('[options][locale_parameter]')->willReturn(null);
        $this->requestParams->expects($this->never())->method('get');
        $this->sut->onBuildAfter($event);
    }
}
