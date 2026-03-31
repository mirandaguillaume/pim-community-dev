<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Storage\Sql\ProductGrid;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductGrid\CountImpactedProducts;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CountImpactedProductsTest extends TestCase
{
    private ProductQueryBuilderFactoryInterface|MockObject $pqbFactory;
    private CountImpactedProducts $sut;

    protected function setUp(): void
    {
        $this->pqbFactory = $this->createMock(ProductQueryBuilderFactoryInterface::class);
        $this->sut = new CountImpactedProducts($this->pqbFactory);
    }

    public function test_it_returns_all_the_selected_products_count_when_a_user_selects_a_list_of_products(): void
    {
        $pqbForProducts = $this->createMock(ProductQueryBuilderInterface::class);
        $pqbForProductsInsideProductModels = $this->createMock(ProductQueryBuilderInterface::class);
        $cursorForProducts = $this->createMock(CursorInterface::class);
        $cursorForProductsInsideProductModels = $this->createMock(CursorInterface::class);

        $pqbFilters = [['field' => 'id', 'operator' => 'IN', 'value' => ['product_1', 'product_2', 'product_3'], 'context' => []]];

        $this->pqbFactory->method('create')
            ->willReturnCallback(function (array $options) use ($pqbForProducts, $pqbForProductsInsideProductModels) {
                $filters = $options['filters'] ?? [];
                if (!empty($filters) && ($filters[0]['field'] ?? '') === 'id') {
                    return $pqbForProducts;
                }
                return $pqbForProductsInsideProductModels;
            });

        $pqbForProducts->method('addFilter')->willReturnSelf();
        $pqbForProducts->method('execute')->willReturn($cursorForProducts);
        $pqbForProductsInsideProductModels->method('addFilter')->willReturnSelf();
        $pqbForProductsInsideProductModels->method('execute')->willReturn($cursorForProductsInsideProductModels);
        $cursorForProducts->method('count')->willReturn(3);
        $cursorForProductsInsideProductModels->method('count')->willReturn(0);

        $this->assertSame(3, $this->sut->count($pqbFilters));
    }

    public function test_it_returns_all_the_selected_variant_products_when_a_user_selects_a_product_model(): void
    {
        $pqbForProducts = $this->createMock(ProductQueryBuilderInterface::class);
        $pqbForProductsInsideProductModels = $this->createMock(ProductQueryBuilderInterface::class);
        $cursorForProducts = $this->createMock(CursorInterface::class);
        $cursorForProductsInsideProductModels = $this->createMock(CursorInterface::class);

        $pqbFilters = [['field' => 'id', 'operator' => 'IN', 'value' => ['product_model_1'], 'context' => []]];

        $this->pqbFactory->method('create')
            ->willReturnCallback(function (array $options) use ($pqbForProducts, $pqbForProductsInsideProductModels) {
                $filters = $options['filters'] ?? [];
                if (!empty($filters) && ($filters[0]['field'] ?? '') === 'id') {
                    return $pqbForProducts;
                }
                return $pqbForProductsInsideProductModels;
            });

        $pqbForProducts->method('addFilter')->willReturnSelf();
        $pqbForProducts->method('execute')->willReturn($cursorForProducts);
        $pqbForProductsInsideProductModels->method('addFilter')->willReturnSelf();
        $pqbForProductsInsideProductModels->method('execute')->willReturn($cursorForProductsInsideProductModels);
        $cursorForProducts->method('count')->willReturn(0);
        $cursorForProductsInsideProductModels->method('count')->willReturn(10);

        $this->assertSame(10, $this->sut->count($pqbFilters));
    }

    public function test_it_returns_all_the_selected_variant_products_when_a_user_selects_product_models_and_products(): void
    {
        $pqbForProducts = $this->createMock(ProductQueryBuilderInterface::class);
        $pqbForProductsInsideProductModels = $this->createMock(ProductQueryBuilderInterface::class);
        $cursorForProducts = $this->createMock(CursorInterface::class);
        $cursorForProductsInsideProductModels = $this->createMock(CursorInterface::class);

        $pqbFilters = [['field' => 'id', 'operator' => 'IN', 'value' => ['product_model_1', 'product_model_2', 'product_1', 'product_2'], 'context' => []]];

        $this->pqbFactory->method('create')
            ->willReturnCallback(function (array $options) use ($pqbForProducts, $pqbForProductsInsideProductModels) {
                $filters = $options['filters'] ?? [];
                if (!empty($filters) && ($filters[0]['field'] ?? '') === 'id') {
                    return $pqbForProducts;
                }
                return $pqbForProductsInsideProductModels;
            });

        $pqbForProducts->method('addFilter')->willReturnSelf();
        $pqbForProducts->method('execute')->willReturn($cursorForProducts);
        $pqbForProductsInsideProductModels->method('addFilter')->willReturnSelf();
        $pqbForProductsInsideProductModels->method('execute')->willReturn($cursorForProductsInsideProductModels);
        $cursorForProducts->method('count')->willReturn(2);
        $cursorForProductsInsideProductModels->method('count')->willReturn(8);

        $this->assertSame(10, $this->sut->count($pqbFilters));
    }

    public function test_it_substracts_the_product_catalog_count_to_the_selected_entities_when_a_user_selects_all_and_unchecks_products(): void
    {
        $pqbForAllProducts = $this->createMock(ProductQueryBuilderInterface::class);
        $searchQueryBuilder = $this->createMock(SearchQueryBuilder::class);
        $pqbForProducts = $this->createMock(ProductQueryBuilderInterface::class);
        $pqbForProductsInsideProductModels = $this->createMock(ProductQueryBuilderInterface::class);
        $cursorForAllProducts = $this->createMock(CursorInterface::class);
        $cursorForProducts = $this->createMock(CursorInterface::class);
        $cursorForProductsInsideProductModels = $this->createMock(CursorInterface::class);

        $pqbFilters = [['field' => 'id', 'operator' => 'NOT IN', 'value' => ['product_1', 'product_2'], 'context' => []]];

        $callIndex = 0;
        $this->pqbFactory->method('create')
            ->willReturnCallback(function () use (&$callIndex, $pqbForAllProducts, $pqbForProducts, $pqbForProductsInsideProductModels) {
                $callIndex++;
                return match($callIndex) {
                    1 => $pqbForAllProducts,
                    2 => $pqbForProducts,
                    3 => $pqbForProductsInsideProductModels,
                    default => $pqbForAllProducts,
                };
            });

        $pqbForAllProducts->method('execute')->willReturn($cursorForAllProducts);
        $pqbForAllProducts->method('getQueryBuilder')->willReturn($searchQueryBuilder);
        $pqbForAllProducts->method('getRawFilters')->willReturn([
            ['field' => 'entity_type', 'operator' => '=', 'value' => ProductInterface::class, 'type' => 'field', 'context' => []],
        ]);
        $pqbForAllProducts->method('addFilter')->willReturnSelf();
        $searchQueryBuilder->expects($this->never())->method('addFilter');
        $pqbForProducts->method('addFilter')->willReturnSelf();
        $pqbForProducts->method('execute')->willReturn($cursorForProducts);
        $pqbForProductsInsideProductModels->method('addFilter')->willReturnSelf();
        $pqbForProductsInsideProductModels->method('execute')->willReturn($cursorForProductsInsideProductModels);
        $cursorForAllProducts->method('count')->willReturn(2500);
        $cursorForProducts->method('count')->willReturn(2);
        $cursorForProductsInsideProductModels->method('count')->willReturn(0);

        $this->assertSame(2498, $this->sut->count($pqbFilters));
    }

    public function test_it_substracts_the_product_catalog_count_to_the_selected_entities_when_a_user_selects_all_and_unchecks_products_and_product_models(): void
    {
        $searchQueryBuilder = $this->createMock(SearchQueryBuilder::class);
        $pqbForAllProducts = $this->createMock(ProductQueryBuilderInterface::class);
        $pqbForProducts = $this->createMock(ProductQueryBuilderInterface::class);
        $pqbForProductsInsideProductModels = $this->createMock(ProductQueryBuilderInterface::class);
        $cursorForAllProducts = $this->createMock(CursorInterface::class);
        $cursorForProducts = $this->createMock(CursorInterface::class);
        $cursorForProductsInsideProductModels = $this->createMock(CursorInterface::class);

        $pqbFilters = [['field' => 'id', 'operator' => 'NOT IN', 'value' => ['product_model_1', 'product_model_2', 'product_1', 'product_2'], 'context' => []]];

        $callIndex = 0;
        $this->pqbFactory->method('create')
            ->willReturnCallback(function () use (&$callIndex, $pqbForAllProducts, $pqbForProducts, $pqbForProductsInsideProductModels) {
                $callIndex++;
                return match($callIndex) {
                    1 => $pqbForAllProducts,
                    2 => $pqbForProducts,
                    3 => $pqbForProductsInsideProductModels,
                    default => $pqbForAllProducts,
                };
            });

        $pqbForAllProducts->method('execute')->willReturn($cursorForAllProducts);
        $pqbForAllProducts->method('getQueryBuilder')->willReturn($searchQueryBuilder);
        $pqbForAllProducts->method('getRawFilters')->willReturn([
            ['field' => 'entity_type', 'operator' => '=', 'value' => ProductInterface::class, 'type' => 'field', 'context' => []],
        ]);
        $pqbForAllProducts->method('addFilter')->willReturnSelf();
        $searchQueryBuilder->expects($this->never())->method('addFilter');
        $pqbForProducts->method('addFilter')->willReturnSelf();
        $pqbForProducts->method('execute')->willReturn($cursorForProducts);
        $pqbForProductsInsideProductModels->method('addFilter')->willReturnSelf();
        $pqbForProductsInsideProductModels->method('execute')->willReturn($cursorForProductsInsideProductModels);
        $cursorForAllProducts->method('count')->willReturn(2500);
        $cursorForProducts->method('count')->willReturn(2);
        $cursorForProductsInsideProductModels->method('count')->willReturn(8);

        $this->assertSame(2490, $this->sut->count($pqbFilters));
    }

    public function test_it_substracts_the_product_catalog_count_to_the_selected_entities_when_a_user_selects_all_and_unchecks_products_and_product_models_with_a_completeness_filter(): void
    {
        $pqbForAllProducts = $this->createMock(ProductQueryBuilderInterface::class);
        $sqb = $this->createMock(SearchQueryBuilder::class);
        $pqbForProducts = $this->createMock(ProductQueryBuilderInterface::class);
        $pqbForProductsInsideProductModels = $this->createMock(ProductQueryBuilderInterface::class);
        $cursorForAllProducts = $this->createMock(CursorInterface::class);
        $cursorForProducts = $this->createMock(CursorInterface::class);
        $cursorForProductsInsideProductModels = $this->createMock(CursorInterface::class);

        $pqbFilters = [
            ['field' => 'id', 'operator' => 'NOT IN', 'value' => ['product_model_1', 'product_model_2', 'product_1', 'product_2'], 'context' => []],
            ['field' => 'completeness', 'operator' => 'AT LEAST COMPLETE', 'value' => null, 'context' => []],
        ];

        $callIndex = 0;
        $this->pqbFactory->method('create')
            ->willReturnCallback(function () use (&$callIndex, $pqbForAllProducts, $pqbForProducts, $pqbForProductsInsideProductModels) {
                $callIndex++;
                return match($callIndex) {
                    1 => $pqbForAllProducts,
                    2 => $pqbForProducts,
                    3 => $pqbForProductsInsideProductModels,
                    default => $pqbForAllProducts,
                };
            });

        $pqbForAllProducts->method('execute')->willReturn($cursorForAllProducts);
        $pqbForAllProducts->method('getQueryBuilder')->willReturn($sqb);
        $pqbForAllProducts->method('getRawFilters')->willReturn([
            ['field' => 'id', 'operator' => 'NOT IN', 'value' => ['product_model_1', 'product_model_2', 'product_1', 'product_2'], 'context' => [], 'type' => 'field'],
            ['field' => 'completeness', 'operator' => '=', 'value' => 100, 'context' => [], 'type' => 'field'],
            ['field' => 'entity_type', 'operator' => '=', 'value' => ProductInterface::class, 'type' => 'field', 'context' => []],
        ]);
        $pqbForAllProducts->method('addFilter')->willReturnSelf();
        $sqb->expects($this->never())->method('addFilter');
        $pqbForProducts->method('addFilter')->willReturnSelf();
        $pqbForProducts->method('execute')->willReturn($cursorForProducts);
        $pqbForProductsInsideProductModels->method('addFilter')->willReturnSelf();
        $pqbForProductsInsideProductModels->method('execute')->willReturn($cursorForProductsInsideProductModels);
        $cursorForAllProducts->method('count')->willReturn(2500);
        $cursorForProducts->method('count')->willReturn(2);
        $cursorForProductsInsideProductModels->method('count')->willReturn(8);

        $this->assertSame(2490, $this->sut->count($pqbFilters));
    }

    public function test_it_computes_when_a_user_selects_all_entities_with_other_filters(): void
    {
        $sqb = $this->createMock(SearchQueryBuilder::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);
        $countable = $this->createMock(\Countable::class);

        $pqbFilters = [
            ['field' => 'color', 'operator' => '=', 'value' => 'red', 'context' => []],
            ['field' => 'size', 'operator' => 'IN LIST', 'value' => ['l', 'm'], 'context' => []],
        ];

        $this->pqbFactory->method('create')->willReturn($pqb);
        $pqb->method('getQueryBuilder')->willReturn($sqb);
        $pqb->method('getRawFilters')->willReturn([
            ['field' => 'color', 'operator' => '=', 'value' => 'red', 'context' => [], 'type' => 'attribute'],
            ['field' => 'size', 'operator' => 'IN LIST', 'value' => ['l', 'm'], 'context' => [], 'type' => 'attribute'],
        ]);
        $sqb->expects($this->never())->method('addFilter');
        $pqb->method('execute')->willReturn($countable);
        $countable->method('count')->willReturn(12);

        $this->assertSame(12, $this->sut->count($pqbFilters));
    }

    public function test_it_adds_a_filter_on_attributes_level_when_there_are_empty_attribute_filters(): void
    {
        $sqb = $this->createMock(SearchQueryBuilder::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);
        $countable = $this->createMock(\Countable::class);

        $pqbFilters = [['field' => 'color', 'operator' => Operators::IS_EMPTY, 'value' => '', 'context' => []]];

        $this->pqbFactory->method('create')->willReturn($pqb);
        $pqb->method('getQueryBuilder')->willReturn($sqb);
        $pqb->method('getRawFilters')->willReturn([
            ['field' => 'color', 'operator' => Operators::IS_EMPTY, 'value' => '', 'context' => [], 'type' => 'attribute'],
        ]);
        $sqb->expects($this->once())->method('addFilter')->with([
            'bool' => [
                'should' => [
                    ['terms' => ['attributes_for_this_level' => ['color']]],
                    ['terms' => ['attributes_of_ancestors' => ['color']]],
                ]
            ]
        ]);
        $pqb->method('execute')->willReturn($countable);
        $countable->method('count')->willReturn(12);

        $this->assertSame(12, $this->sut->count($pqbFilters));
    }
}
