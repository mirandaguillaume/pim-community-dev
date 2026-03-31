<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimFilterBundle\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\PimFilterBundle\Filter\ProductAndProductModelCompletenessFilter;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;

class ProductAndProductModelCompletenessFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $factory;
    private ProductFilterUtility|MockObject $utility;
    private ProductAndProductModelCompletenessFilter $sut;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->utility = $this->createMock(ProductFilterUtility::class);
        $this->sut = new ProductAndProductModelCompletenessFilter($this->factory, $this->utility);
    }

    public function test_it_is_an_oro_choice_filter(): void
    {
        $this->assertInstanceOf(\Oro\Bundle\FilterBundle\Filter\ChoiceFilter::class, $this->sut);
    }

    public function test_it_applies_a_filter_on_complete_products_and_product_models(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'completeness', Operators::AT_LEAST_COMPLETE, null);
        $this->sut->apply($datasource, ['type' => null, 'value' => BooleanFilterType::TYPE_YES]);
    }

    public function test_it_applies_a_filter_on_not_complete_products_and_product_models(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'completeness', Operators::AT_LEAST_INCOMPLETE, null);
        $this->sut->apply($datasource, ['type' => null, 'value' => BooleanFilterType::TYPE_NO]);
    }
}
