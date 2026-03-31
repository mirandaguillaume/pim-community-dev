<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimFilterBundle\Filter\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\Product\EntityTypeFilter;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;

class EntityTypeFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $factory;
    private ProductFilterUtility|MockObject $utility;
    private EntityTypeFilter $sut;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->utility = $this->createMock(ProductFilterUtility::class);
        $this->sut = new EntityTypeFilter($this->factory, $this->utility);
    }

    public function test_it_is_a_product_typology_filter(): void
    {
        $this->assertInstanceOf(EntityTypeFilter::class, $this->sut);
    }

    public function test_it_does_not_apply_filter_on_unexpected_value(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->never())->method('applyFilter');
        $this->sut->apply($datasource, ['type' => null, 'value' => 'toto']);
    }

    public function test_it_applies_filter_for_products(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'entity_type', '=', ProductInterface::class);
        $this->sut->apply($datasource, ['type' => null, 'value' => 'product']);
    }
}
