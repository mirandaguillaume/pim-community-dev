<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Database;

use Doctrine\DBAL\Connection;

/**
 * Concrete class exposing DatabasePlatformTrait helpers for direct use or testing.
 *
 * Production classes should prefer using the trait directly for zero-cost abstraction.
 * This class is useful when a non-trait approach is preferred (e.g., delegation).
 */
final readonly class DatabasePlatformAwareQuery
{
    use DatabasePlatformTrait;

    public function __construct(private Connection $connection)
    {
    }

    private function getConnection(): Connection
    {
        return $this->connection;
    }

    public function buildJsonArrayAgg(string $expr): string
    {
        return $this->jsonArrayAgg($expr);
    }

    public function buildJsonObjectAgg(string $key, string $value): string
    {
        return $this->jsonObjectAgg($key, $value);
    }

    public function buildJsonRemoveKey(string $doc, string $key): string
    {
        return $this->jsonRemoveKey($doc, $key);
    }

    public function buildRegexpMatch(string $column, string $pattern): string
    {
        return $this->regexpMatch($column, $pattern);
    }

    public function buildGroupConcat(string $expr, string $separator, ?string $orderBy = null): string
    {
        return $this->groupConcat($expr, $separator, $orderBy);
    }
}
