<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\CategoryTree\ListChildrenCategoriesWithCountNotIncludingSubCategories;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListChildrenCategoriesWithCountNotIncludingSubCategoriesTest extends TestCase
{
    private Connection|MockObject $connection;
    private Client|MockObject $client;
    private ListChildrenCategoriesWithCountNotIncludingSubCategories $sut;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->client = $this->createMock(Client::class);
        $this->sut = new ListChildrenCategoriesWithCountNotIncludingSubCategories($this->connection, $this->client);
    }

    public function test_it_lists_the_direct_children_of_the_category_to_expand_when_nothing_is_selected_as_filter(): void
    {
        $this->connection->method('executeQuery')->willReturn($this->rows([
            ['child_id' => '10', 'child_code' => 'shoes', 'is_leaf' => '1', 'label' => 'Shoes'],
        ]));
        $this->client->method('msearch')->willReturn([
            'responses' => [['hits' => ['total' => ['value' => 3]]]],
        ]);

        $categories = $this->sut->list('en_US', 42, 5, null);

        $this->assertCount(1, $categories);
        $this->assertSame(10, $categories[0]->id());
        $this->assertSame('shoes', $categories[0]->code());
        $this->assertSame(3, $categories[0]->numberProductsInCategory());
        $this->assertTrue($categories[0]->isLeaf());
        $this->assertFalse($categories[0]->selectedAsFilter());
        $this->assertFalse($categories[0]->expanded());
        $this->assertSame([], $categories[0]->childrenCategoriesToExpand());
    }

    public function test_it_recursively_expands_down_to_the_category_selected_as_filter(): void
    {
        $this->connection->method('executeQuery')->willReturnCallback(
            function (string $_sql, array $params): Result {
                if (\array_key_exists('category_to_expand', $params)) {
                    return $this->rows([['id' => '5'], ['id' => '8']]);
                }

                return match ($params['parent_category_id']) {
                    5 => $this->rows([
                        ['child_id' => '8', 'child_code' => 'clothing', 'is_leaf' => '0', 'label' => 'Clothing'],
                        ['child_id' => '9', 'child_code' => 'shoes', 'is_leaf' => '1', 'label' => 'Shoes'],
                    ]),
                    8 => $this->rows([
                        ['child_id' => '20', 'child_code' => 'sneakers', 'is_leaf' => '1', 'label' => 'Sneakers'],
                    ]),
                };
            },
        );
        $this->client->method('msearch')->willReturnOnConsecutiveCalls(
            ['responses' => [
                ['hits' => ['total' => ['value' => 2]]],
                ['hits' => ['total' => ['value' => 0]]],
            ]],
            ['responses' => [
                ['hits' => ['total' => ['value' => 7]]],
            ]],
        );

        $categories = $this->sut->list('en_US', 42, 5, 20);

        $this->assertCount(2, $categories);
        [$clothing, $shoes] = $categories;

        $this->assertSame(8, $clothing->id());
        $this->assertFalse($clothing->selectedAsFilter());
        $this->assertTrue($clothing->expanded());
        $this->assertCount(1, $clothing->childrenCategoriesToExpand());
        $sneakers = $clothing->childrenCategoriesToExpand()[0];
        $this->assertSame(20, $sneakers->id());
        $this->assertSame(7, $sneakers->numberProductsInCategory());
        $this->assertTrue($sneakers->selectedAsFilter());

        $this->assertSame(9, $shoes->id());
        $this->assertFalse($shoes->expanded());
        $this->assertFalse($shoes->selectedAsFilter());
    }

    private function rows(array $rows): Result
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn($rows);

        return $result;
    }
}
