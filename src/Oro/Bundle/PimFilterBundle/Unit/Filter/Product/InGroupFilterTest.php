<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimFilterBundle\Filter\Product;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Filter\ObjectCodeResolver;
use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use Oro\Bundle\PimDataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimFilterBundle\Filter\Product\InGroupFilter;
use Symfony\Component\Form\FormFactoryInterface;

class InGroupFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $factory;
    private ProductFilterUtility|MockObject $utility;
    private RequestParametersExtractorInterface|MockObject $extractor;
    private ObjectCodeResolver|MockObject $codeResolver;
    private InGroupFilter $sut;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->utility = $this->createMock(ProductFilterUtility::class);
        $this->extractor = $this->createMock(RequestParametersExtractorInterface::class);
        $this->codeResolver = $this->createMock(ObjectCodeResolver::class);
        $this->sut = new InGroupFilter($this->factory, $this->utility, $this->extractor, $this->codeResolver);
    }

    public function test_it_is_an_oro_choice_filter(): void
    {
        $this->assertInstanceOf(BooleanFilter::class, $this->sut);
    }

    public function test_it_applies_a_filter_on_product_when_its_in_an_expected_group(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->extractor->method('getDatagridParameter')->with('currentGroup')->willReturn(12);
        $this->codeResolver->method('getCodesFromIds')->with('group', [12])->willReturn(['foo']);
        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'groups', 'IN', ['foo']);
        $this->sut->apply($datasource, ['type' => null, 'value' => 1]);
    }
}
