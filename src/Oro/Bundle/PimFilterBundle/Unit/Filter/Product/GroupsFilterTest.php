<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimFilterBundle\Filter\Product;

use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimFilterBundle\Filter\Product\GroupsFilter;
use Symfony\Component\Form\FormFactoryInterface;

class GroupsFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $factory;
    private ProductFilterUtility|MockObject $utility;
    private UserContext|MockObject $userContext;
    private GroupsFilter $sut;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->utility = $this->createMock(ProductFilterUtility::class);
        $this->userContext = $this->createMock(UserContext::class);
        $this->sut = new GroupsFilter($this->factory, $this->utility, $this->userContext, 'Group');
    }

    public function test_it_is_an_oro_choice_filter(): void
    {
        $this->assertInstanceOf(ChoiceFilter::class, $this->sut);
    }

    public function test_it_applies_a_filter_on_product_groups(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'groups', 'IN', ['foo', 'bar']);
        $this->sut->apply($datasource, ['type' => null, 'value' => ['foo', 'bar']]);
    }
}
