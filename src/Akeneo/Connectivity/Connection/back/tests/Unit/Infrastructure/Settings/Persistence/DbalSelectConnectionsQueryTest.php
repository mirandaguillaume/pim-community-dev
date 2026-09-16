<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Settings\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\Settings\Persistence\DbalSelectConnectionsQuery;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalSelectConnectionsQueryTest extends TestCase
{
    private Connection|MockObject $connection;
    private DbalSelectConnectionsQuery $sut;

    /**
     * @var array<int, array{sql: string, params: array<string, mixed>, types: array<string, mixed>}>
     */
    private array $executedQueries = [];

    private array $rows = [];

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->executedQueries = [];
        $this->rows = [];

        $this->connection->method('executeQuery')->willReturnCallback(
            function (string $sql, array $params = [], array $types = []): Result {
                $this->executedQueries[] = ['sql' => $sql, 'params' => $params, 'types' => $types];
                $result = $this->createMock(Result::class);
                $result->method('fetchAllAssociative')->willReturn($this->rows);

                return $result;
            },
        );

        $this->sut = new DbalSelectConnectionsQuery($this->connection);
    }

    public function test_it_selects_every_connection_when_no_type_is_given(): void
    {
        $this->rows = [
            ['code' => 'erp', 'label' => 'Erp', 'flow_type' => 'other', 'image' => null, 'auditable' => '1', 'type' => 'default'],
        ];

        $connections = $this->sut->execute();

        $query = $this->executedQueries[0];
        $this->assertStringNotContainsString('WHERE', $query['sql']);
        $this->assertSame([], $query['params']);
        $this->assertCount(1, $connections);
        $this->assertSame('erp', $connections[0]->code());
        $this->assertTrue($connections[0]->auditable());
    }

    public function test_it_filters_by_type_when_types_are_given(): void
    {
        $this->sut->execute(['default']);

        $query = $this->executedQueries[0];
        $this->assertStringContainsString('WHERE type IN (:types)', $query['sql']);
        $this->assertSame(['types' => ['default']], $query['params']);
        $this->assertSame(['types' => ArrayParameterType::STRING], $query['types']);
    }
}
