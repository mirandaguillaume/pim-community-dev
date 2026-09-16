<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\CategoryTree\ListRootCategoriesWithCountNotIncludingSubCategories;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListRootCategoriesWithCountNotIncludingSubCategoriesTest extends TestCase
{
    private Connection|MockObject $connection;
    private Client|MockObject $client;
    private ListRootCategoriesWithCountNotIncludingSubCategories $sut;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->client = $this->createMock(Client::class);
        $this->sut = new ListRootCategoriesWithCountNotIncludingSubCategories($this->connection, $this->client);
    }

    public function test_it_returns_no_root_category_when_there_is_none(): void
    {
        $this->givenTheRootCategoryRows([]);
        $this->client->expects($this->never())->method('msearch');

        $this->assertSame([], $this->sut->list('en_US', 42, 1));
    }

    public function test_it_lists_root_categories_with_their_product_count_and_marks_the_one_being_expanded(): void
    {
        $this->givenTheRootCategoryRows([
            ['root_id' => '1', 'root_code' => 'master', 'label' => 'Master catalog'],
            ['root_id' => '2', 'root_code' => 'print', 'label' => 'Print catalog'],
        ]);
        $this->client->method('msearch')->willReturn([
            'responses' => [
                ['hits' => ['total' => ['value' => 5]]],
                ['hits' => ['total' => ['value' => 0]]],
            ],
        ]);

        $rootCategories = $this->sut->list('en_US', 42, 2);

        $this->assertCount(2, $rootCategories);
        $this->assertSame(1, $rootCategories[0]->id());
        $this->assertSame('master', $rootCategories[0]->code());
        $this->assertSame(5, $rootCategories[0]->numberProductsInCategory());
        $this->assertFalse($rootCategories[0]->selected());
        $this->assertSame(2, $rootCategories[1]->id());
        $this->assertSame(0, $rootCategories[1]->numberProductsInCategory());
        $this->assertTrue($rootCategories[1]->selected());
    }

    private function givenTheRootCategoryRows(array $rows): void
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn($rows);
        $this->connection->method('executeQuery')->willReturn($result);
    }
}
