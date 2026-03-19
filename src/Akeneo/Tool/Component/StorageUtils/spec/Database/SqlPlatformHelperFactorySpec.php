<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\StorageUtils\Database;

use Akeneo\Tool\Component\StorageUtils\Database\MySqlPlatformHelper;
use Akeneo\Tool\Component\StorageUtils\Database\PostgreSqlPlatformHelper;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use PhpSpec\ObjectBehavior;

final class SqlPlatformHelperFactorySpec extends ObjectBehavior
{
    public function it_creates_mysql_helper(Connection $connection): void
    {
        $connection->getDatabasePlatform()->willReturn(new MySQLPlatform());

        $result = $this->create($connection);
        $result->shouldBeAnInstanceOf(SqlPlatformHelperInterface::class);
        $result->shouldBeAnInstanceOf(MySqlPlatformHelper::class);
    }

    public function it_creates_postgresql_helper(Connection $connection): void
    {
        $connection->getDatabasePlatform()->willReturn(new PostgreSQLPlatform());

        $result = $this->create($connection);
        $result->shouldBeAnInstanceOf(SqlPlatformHelperInterface::class);
        $result->shouldBeAnInstanceOf(PostgreSqlPlatformHelper::class);
    }

    public function it_throws_on_unsupported_platform(Connection $connection): void
    {
        $connection->getDatabasePlatform()->willReturn(new SQLitePlatform());

        $this->shouldThrow(\LogicException::class)->during('create', [$connection]);
    }
}
