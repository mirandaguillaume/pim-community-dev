<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimFilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\Product\EnabledFilter;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;

class EnabledFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $factory;
    private ProductFilterUtility|MockObject $utility;
    private EnabledFilter $sut;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->utility = $this->createMock(ProductFilterUtility::class);
        $this->sut = new EnabledFilter($this->factory, $this->utility);
    }

    public function test_it_is_an_oro_choice_filter(): void
    {
        $this->assertInstanceOf(ChoiceFilter::class, $this->sut);
    }

    public function test_it_applies_a_filter_on_enabled_field_value(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'enabled', '=', true);
        $this->assertSame(true, $this->sut->apply($datasource, ['type' => null, 'value' => [0 => true]]));
    }

    public function test_it_casts_value_to_bool(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'enabled', '=', false);
        $this->assertSame(true, $this->sut->apply($datasource, ['type' => null, 'value' => [0 => 0]]));
    }

    public function test_it_returns_false_when_value_is_not_set(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->never())->method('applyFilter');
        $this->assertSame(false, $this->sut->apply($datasource, 'invalid'));
    }
}
