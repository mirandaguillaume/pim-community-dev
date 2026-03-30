<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimFilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Filter\FilterInterface;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimFilterBundle\Filter\Product\LabelOrIdentifierFilter;
use Symfony\Component\Form\FormFactoryInterface;

class LabelOrIdentifierFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $factory;
    private ProductFilterUtility|MockObject $utility;
    private LabelOrIdentifierFilter $sut;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->utility = $this->createMock(ProductFilterUtility::class);
        $this->sut = new LabelOrIdentifierFilter($this->factory, $this->utility);
    }

    public function test_it_is_a_filter(): void
    {
        $this->assertInstanceOf(FilterInterface::class, $this->sut);
    }

    public function test_it_applies_a_filter_on_product_when_its_in_an_expected_group(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'label_or_identifier', 'CONTAINS', 'mylabel');
        $this->sut->apply($datasource, ['type' => null, 'value' => 'mylabel']);
    }

    public function test_it_applies_a_filter_on_product_when_value_contains_underscore(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'label_or_identifier', 'CONTAINS', 'mylabel_');
        $this->sut->apply($datasource, ['type' => null, 'value' => 'mylabel_']);
    }
}
