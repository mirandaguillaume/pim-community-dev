<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Extension\Sorter\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductDatasource;
use Oro\Bundle\PimDataGridBundle\Extension\Sorter\Product\ReferenceDataSorter;
use Oro\Bundle\PimDataGridBundle\Extension\Sorter\SorterInterface;
use PHPUnit\Framework\TestCase;

class ReferenceDataSorterTest extends TestCase
{
    private ReferenceDataSorter $sut;

    protected function setUp(): void
    {
        $this->sut = new ReferenceDataSorter();
    }

    public function test_it_is_a_sorter(): void
    {
        $this->assertInstanceOf(SorterInterface::class, $this->sut);
    }

    public function test_it_applies_a_sort_on_the_given_field_and_direction(): void
    {
        $datasource = $this->createMock(ProductDatasource::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);
        $datasource->method('getProductQueryBuilder')->willReturn($pqb);

        $pqb->expects($this->once())->method('addSorter')->with('a_reference_data_field', 'DESC');

        $this->sut->apply($datasource, 'a_reference_data_field', 'DESC');
    }
}
