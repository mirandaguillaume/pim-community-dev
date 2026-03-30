<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Extension\Sorter\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductDatasource;
use Oro\Bundle\PimDataGridBundle\Extension\Sorter\SorterInterface;
use PhpSpec\ObjectBehavior;

class FamilySorterSpec extends ObjectBehavior
{
    public function it_is_a_sorter()
    {
        $this->shouldImplement(SorterInterface::class);
    }

    public function it_applies_a_sort_on_product_family(
        ProductDatasource $datasource,
        ProductQueryBuilderInterface $pqb
    ) {
        $datasource->getProductQueryBuilder()->willReturn($pqb);
        $pqb->addSorter('family', 'ASC')->shouldBeCalled();

        $this->apply($datasource, 'family', 'ASC');
    }
}
