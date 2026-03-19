<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Database;

/**
 * PostgreSQL-native SQL fragment generator.
 *
 * Uses JSONB functions and PostgreSQL-specific operators.
 */
final readonly class PostgreSqlPlatformHelper implements SqlPlatformHelperInterface
{
    public function jsonArrayAgg(string $expr): string
    {
        return sprintf('jsonb_agg(%s)', $expr);
    }

    public function jsonObjectAgg(string $key, string $value): string
    {
        return sprintf('jsonb_object_agg(%s, %s)', $key, $value);
    }

    public function jsonRemoveKey(string $doc, string $key): string
    {
        return sprintf("(%s - '%s')", $doc, $key);
    }

    public function regexpMatch(string $column, string $pattern): string
    {
        return sprintf('%s ~ %s', $column, $pattern);
    }

    public function groupConcat(string $expr, string $separator, ?string $orderBy = null): string
    {
        $sql = sprintf('STRING_AGG(%s, %s', $expr, $separator);
        if (null !== $orderBy) {
            $sql .= sprintf(' ORDER BY %s', $orderBy);
        }
        $sql .= ')';

        return $sql;
    }
}
