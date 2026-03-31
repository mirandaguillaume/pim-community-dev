<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimFilterBundle\Filter;

use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Oro\Bundle\FilterBundle\Filter\NumberFilter;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\CategoryFilter;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;

class CategoryFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $factory;
    private ProductFilterUtility|MockObject $utility;
    private CategoryRepositoryInterface|MockObject $categoryRepo;
    private CategoryFilter $sut;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->utility = $this->createMock(ProductFilterUtility::class);
        $this->categoryRepo = $this->createMock(CategoryRepositoryInterface::class);
        $this->sut = new CategoryFilter($this->factory, $this->utility, $this->categoryRepo);
    }

    public function test_it_is_an_oro_number_filter(): void
    {
        $this->assertInstanceOf(NumberFilter::class, $this->sut);
    }

    public function test_it_applies_a_filter_on_all_products(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->assertSame(true, $this->sut->apply($datasource, ['value' => ['categoryId' => -2]]));
    }

    public function test_it_applies_a_filter_by_unclassified_products(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);
        $tree = $this->createMock(CategoryInterface::class);

        $tree->method('getCode')->willReturn('my_tree');
        $this->categoryRepo->method('find')->with(1)->willReturn($tree);
        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'categories', 'NOT IN CHILDREN', ['my_tree']);
        $this->sut->apply($datasource, ['value' => ['categoryId' => -1, 'treeId' => 1]]);
    }

    public function test_it_applies_a_filter_by_in_category(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);
        $category = $this->createMock(CategoryInterface::class);

        $this->categoryRepo->method('find')->with(42)->willReturn($category);
        $category->method('getCode')->willReturn('foo');
        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'categories', 'IN', ['foo']);
        $this->sut->apply($datasource, ['value' => ['categoryId' => 42], 'type' => false]);
    }

    public function test_it_applies_a_filter_by_in_category_with_children(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);
        $category = $this->createMock(CategoryInterface::class);

        $this->categoryRepo->method('find')->with(42)->willReturn($category);
        $category->method('getCode')->willReturn('foo');
        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'categories', 'IN CHILDREN', ['foo']);
        $this->sut->apply($datasource, ['value' => ['categoryId' => 42], 'type' => true]);
    }
}
