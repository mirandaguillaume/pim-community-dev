<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Database;

use Akeneo\Tool\Component\StorageUtils\Database\MySqlPlatformHelper;
use Akeneo\Tool\Component\StorageUtils\Database\PostgreSqlPlatformHelper;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperFactory;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SqlPlatformHelperFactoryTest extends TestCase
{
    private SqlPlatformHelperFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new SqlPlatformHelperFactory();
    }

    public function test_it_creates_mysql_helper(): void
    {
        $connection = $this->createMock(Connection::class);

        $connection->method('getDatabasePlatform')->willReturn(new MySQLPlatform());
        $result = $this->create($connection);
        $result->shouldBeAnInstanceOf(SqlPlatformHelperInterface::class);
        $result->shouldBeAnInstanceOf(MySqlPlatformHelper::class);
    }

    public function test_it_creates_postgresql_helper(): void
    {
        $connection = $this->createMock(Connection::class);

        $connection->method('getDatabasePlatform')->willReturn(new PostgreSQLPlatform());
        $result = $this->create($connection);
        $result->shouldBeAnInstanceOf(SqlPlatformHelperInterface::class);
        $result->shouldBeAnInstanceOf(PostgreSqlPlatformHelper::class);
    }

    public function test_it_throws_on_unsupported_platform(): void
    {
        $connection = $this->createMock(Connection::class);

        $connection->method('getDatabasePlatform')->willReturn(new SQLitePlatform());
        $this->expectException(\LogicException::class);
        $this->sut->create($connection);
    }
}
