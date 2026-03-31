<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Extension\Sorter\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductDatasource;
use Oro\Bundle\PimDataGridBundle\Extension\Sorter\Product\InGroupSorter;
use Oro\Bundle\PimDataGridBundle\Extension\Sorter\SorterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InGroupSorterTest extends TestCase
{
    private RequestParameters|MockObject $params;
    private InGroupSorter $sut;

    protected function setUp(): void
    {
        $this->params = $this->createMock(RequestParameters::class);
        $this->sut = new InGroupSorter($this->params);
    }

    public function test_it_is_a_sorter(): void
    {
        $this->assertInstanceOf(SorterInterface::class, $this->sut);
    }

    public function test_it_applies_a_sort_on_in_group_products(): void
    {
        $datasource = $this->createMock(ProductDatasource::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);

        $datasource->method('getProductQueryBuilder')->willReturn($pqb);
        $this->params->method('get')->with('currentGroup', null)->willReturn(12);
        $pqb->expects($this->once())->method('addSorter')->with('in_group_12', 'ASC');
        $this->sut->apply($datasource, 'in_group', 'ASC');
    }
}
