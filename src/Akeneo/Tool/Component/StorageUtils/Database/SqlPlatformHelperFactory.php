<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

/**
 * Resolves the appropriate SqlPlatformHelperInterface implementation
 * based on the DBAL connection's database platform.
 *
 * Register this factory in Symfony DI to auto-wire the correct helper:
 *
 *     SqlPlatformHelperInterface:
 *         factory: ['@SqlPlatformHelperFactory', 'create']
 *         arguments: ['@database_connection']
 */
final readonly class SqlPlatformHelperFactory
{
    public function create(Connection $connection): SqlPlatformHelperInterface
    {
        $platform = $connection->getDatabasePlatform();

        return match (true) {
            $platform instanceof MySQLPlatform => new MySqlPlatformHelper(),
            $platform instanceof PostgreSQLPlatform => new PostgreSqlPlatformHelper(),
            default => throw new \LogicException(sprintf(
                'Unsupported database platform "%s". Implement SqlPlatformHelperInterface for your platform.',
                $platform::class,
            )),
        };
    }
}
