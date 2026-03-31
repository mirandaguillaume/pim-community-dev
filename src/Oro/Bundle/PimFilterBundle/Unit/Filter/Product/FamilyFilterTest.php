<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimFilterBundle\Filter\Product;

use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\Product\FamilyFilter;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;

class FamilyFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $factory;
    private ProductFilterUtility|MockObject $utility;
    private FamilyFilter $sut;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->utility = $this->createMock(ProductFilterUtility::class);
        $this->sut = new FamilyFilter($this->factory, $this->utility);
    }

    public function test_it_is_an_oro_choice_filter(): void
    {
        $this->assertInstanceOf(ChoiceFilter::class, $this->sut);
    }

    public function test_it_applies_a_filter_on_product_family(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'family', 'IN', [2, 3]);
        $this->assertSame(true, $this->sut->apply($datasource, ['type' => 'IN', 'value' => [2, 3]]));
    }

    public function test_it_does_not_apply_filter_when_family_is_not_found(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->method('applyFilter')->with($datasource, 'family', 'IN', ['deleted_family'])->willThrowException(new ObjectNotFoundException());
        $this->assertSame(false, $this->sut->apply($datasource, ['type' => 'IN', 'value' => ['deleted_family']]));
    }
}
