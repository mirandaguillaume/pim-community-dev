<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Extension\Sorter\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductDatasource;
use Oro\Bundle\PimDataGridBundle\Extension\Sorter\Product\FieldSorter;
use Oro\Bundle\PimDataGridBundle\Extension\Sorter\SorterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FieldSorterTest extends TestCase
{
    private FieldSorter $sut;

    protected function setUp(): void
    {
        $this->sut = new FieldSorter();
    }

    public function test_it_is_a_sorter(): void
    {
        $this->assertInstanceOf(SorterInterface::class, $this->sut);
    }

    public function test_it_applies_a_sort_on_product_updated_at(): void
    {
        $datasource = $this->createMock(ProductDatasource::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);

        $datasource->method('getProductQueryBuilder')->willReturn($pqb);
        $pqb->expects($this->once())->method('addSorter')->with('updated', 'ASC');
        $this->sut->apply($datasource, 'updated', 'ASC');
    }
}
