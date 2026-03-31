<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Adapter;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductGrid\CountImpactedProducts;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi\AttributeSearchableRepository;
use Oro\Bundle\PimDataGridBundle\Adapter\ItemsCounter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemsCounterTest extends TestCase
{
    private CountImpactedProducts|MockObject $countImpactedProducts;
    private AttributeSearchableRepository|MockObject $countAttributes;
    private ItemsCounter $sut;

    protected function setUp(): void
    {
        $this->countImpactedProducts = $this->createMock(CountImpactedProducts::class);
        $this->countAttributes = $this->createMock(AttributeSearchableRepository::class);
        $this->sut = new ItemsCounter($this->countImpactedProducts, $this->countAttributes);
    }

    public function test_it_counts_items_in_the_product_grid(): void
    {
        $this->countImpactedProducts->method('count')->with(['filters'])->willReturn(42);
        $this->assertSame(42, $this->sut->count('product-grid', ['filters']));
    }

    public function test_it_counts_items_in_the_attribute_grid(): void
    {
        $this->countAttributes->method('count')->with(null, [])->willReturn(6);
        $this->assertSame(6, $this->sut->count('attribute-grid', [
                    'search' => null,
                    'options' => [],
                ]));
    }

    public function test_it_counts_items_in_the_other_grids(): void
    {
        $this->assertSame(5, $this->sut->count('family-grid', [
                    ['value' => [1, 2, 3, 4, 5]],
                ]));
    }

    public function test_it_raises_an_exception_when_unable_to_count_the_number_of_items(): void
    {
        $this->expectException(\Exception::class);
        $this->sut->count('family-grid', ['wrong filters']);
    }
}
