<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Extension\Sorter\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductDatasource;
use Oro\Bundle\PimDataGridBundle\Extension\Sorter\SorterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimDataGridBundle\Extension\Sorter\Product\ValueSorter;

class ValueSorterTest extends TestCase
{
    private AttributeRepositoryInterface|MockObject $attributeRepository;
    private ValueSorter $sut;

    protected function setUp(): void
    {
        $this->attributeRepository = $this->createMock(AttributeRepositoryInterface::class);
        $this->sut = new ValueSorter($this->attributeRepository);
    }

    public function test_it_is_a_sorter(): void
    {
        $this->assertInstanceOf(SorterInterface::class, $this->sut);
    }

    public function test_it_applies_a_sort_on_product_sku(): void
    {
        $datasource = $this->createMock(ProductDatasource::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $this->attributeRepository->method('findOneByIdentifier')->with('sku')->willReturn($attribute);
        $attribute->method('isScopable')->willReturn(false);
        $attribute->method('isLocalizable')->willReturn(false);
        $datasource->method('getProductQueryBuilder')->willReturn($pqb);
        $pqb->expects($this->once())->method('addSorter')->with('sku', 'ASC', ['scope' => null, 'locale' => null]);
        $this->sut->apply($datasource, 'sku', 'ASC');
    }
}
