<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderWithSearchAggregatorFactory;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelSearchAggregator;
use Akeneo\Pim\Enrichment\Bundle\ProductQueryBuilder\ProductAndProductModelQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductAndProductModelQueryBuilderWithSearchAggregatorFactoryTest extends TestCase
{
    private ProductAndProductModelQueryBuilderWithSearchAggregatorFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductAndProductModelQueryBuilderWithSearchAggregatorFactory();
    }

    public function test_it_is_a_product_query_builder_factory(): void
    {
        $this->assertInstanceOf(ProductQueryBuilderFactoryInterface::class, $this->sut);
    }

    public function test_it_creates_a_product_and_product_model_query_builder(): void
    {
        $basePqb = $this->createMock(ProductQueryBuilderInterface::class);

        $factory->create(['default_locale' => 'en_US', 'default_scope' => 'print'])->willReturn($basePqb);
        $this->assertInstanceOf(ProductQueryBuilderInterface::class, $this->sut->create(['default_locale' => 'en_US', 'default_scope' => 'print']));
    }
}
