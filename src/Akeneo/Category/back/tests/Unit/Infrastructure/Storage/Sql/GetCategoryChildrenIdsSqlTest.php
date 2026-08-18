<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Storage\Sql;

use Akeneo\Category\Infrastructure\Storage\Sql\GetCategoryChildrenIdsSql;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryChildrenIdsSqlTest extends TestCase
{
    private Connection|MockObject $connection;
    private GetCategoryChildrenIdsSql $sut;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->sut = new GetCategoryChildrenIdsSql($this->connection);
    }

    public function test_it_returns_no_children_when_the_category_has_none(): void
    {
        $this->givenTheRows([]);

        $this->assertSame([], ($this->sut)(5));
    }

    public function test_it_returns_the_ids_of_every_recursive_child(): void
    {
        $this->givenTheRows([['id' => '6'], ['id' => '7']]);

        $this->assertSame([6, 7], ($this->sut)(5));
    }

    private function givenTheRows(array $rows): void
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn($rows);
        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->with(
                $this->anything(),
                ['category_id' => 5],
                ['category_id' => ParameterType::INTEGER],
            )
            ->willReturn($result);
    }
}
