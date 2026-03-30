<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimFilterBundle\Filter\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimFilterBundle\Filter\Product\ProductCompletenessFilter;
use Symfony\Component\Form\FormFactoryInterface;

class ProductCompletenessFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $factory;
    private ProductFilterUtility|MockObject $utility;
    private ProductCompletenessFilter $sut;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->utility = $this->createMock(ProductFilterUtility::class);
        $this->sut = new ProductCompletenessFilter($this->factory, $this->utility);
    }

    public function test_it_is_an_oro_choice_filter(): void
    {
        $this->assertInstanceOf(ChoiceFilter::class, $this->sut);
    }

    public function test_it_applies_a_filter_on_complete_products(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'completeness', Operators::EQUALS_ON_AT_LEAST_ONE_LOCALE, 100);
        $this->sut->apply($datasource, ['type' => null, 'value' => BooleanFilterType::TYPE_YES]);
    }

    public function test_it_applies_a_filter_on_not_complete_products(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'completeness', Operators::LOWER_THAN_ON_AT_LEAST_ONE_LOCALE, 100);
        $this->sut->apply($datasource, ['type' => null, 'value' => BooleanFilterType::TYPE_NO]);
    }
}
