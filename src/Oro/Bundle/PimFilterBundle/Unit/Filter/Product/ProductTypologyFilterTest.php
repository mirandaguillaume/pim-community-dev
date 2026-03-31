<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimFilterBundle\Filter\Product;

use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\Product\ProductTypologyFilter;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;

class ProductTypologyFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $factory;
    private ProductFilterUtility|MockObject $utility;
    private ProductTypologyFilter $sut;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->utility = $this->createMock(ProductFilterUtility::class);
        $this->sut = new ProductTypologyFilter($this->factory, $this->utility);
    }

    public function test_it_is_a_product_typology_filter(): void
    {
        $this->assertInstanceOf(ProductTypologyFilter::class, $this->sut);
    }

    public function test_it_does_not_apply_filter_on_unexpected_value(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->never())->method('applyFilter');
        $this->assertSame(false, $this->sut->apply($datasource, ['type' => null, 'value' => 'toto']));
    }

    public function test_it_applies_filter_for_simple_product_typology(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'family_variant', 'EMPTY', null);
        $this->assertSame(true, $this->sut->apply($datasource, ['type' => null, 'value' => 'simple']));
    }

    public function test_it_applies_filter_for_variant_product_typology(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'family_variant', 'NOT EMPTY', null);
        $this->assertSame(true, $this->sut->apply($datasource, ['type' => null, 'value' => 'variant']));
    }
}
