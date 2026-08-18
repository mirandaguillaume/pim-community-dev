<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Storage\Sql\Update;

use Akeneo\Category\Domain\Query\UpdateCategoryUpdatedDate;
use Akeneo\Category\Infrastructure\Storage\Sql\Update\UpdateCategoryUpdatedDateSql;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateCategoryUpdatedDateSqlTest extends TestCase
{
    private Connection|MockObject $connection;
    private UpdateCategoryUpdatedDateSql $sut;

    /**
     * @var array<int, array{sql: string, params: array<string, mixed>, types: array<string, mixed>}>
     */
    private array $executedQueries = [];

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->executedQueries = [];

        $this->connection->method('executeQuery')->willReturnCallback(
            function (string $sql, array $params = [], array $types = []): Result {
                $this->executedQueries[] = ['sql' => $sql, 'params' => $params, 'types' => $types];

                return $this->createMock(Result::class);
            },
        );

        $this->sut = new UpdateCategoryUpdatedDateSql($this->connection);
    }

    public function testItIsAnUpdateCategoryUpdatedDate(): void
    {
        $this->assertInstanceOf(UpdateCategoryUpdatedDate::class, $this->sut);
    }

    public function testItTouchesTheUpdatedDateOfTheGivenCategoryOnly(): void
    {
        $this->sut->execute('socks');

        $this->assertCount(1, $this->executedQueries);
        $query = $this->executedQueries[0];
        $this->assertStringContainsString('UPDATE pim_catalog_category', $query['sql']);
        $this->assertStringContainsString('SET updated = NOW()', $query['sql']);
        $this->assertStringContainsString('WHERE code = :code', $query['sql']);
        $this->assertSame(['code' => 'socks'], $query['params']);
        $this->assertSame(['code' => ParameterType::STRING], $query['types']);
    }
}
