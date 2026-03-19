<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Database;

/**
 * MySQL-native SQL fragment generator.
 *
 * Supports MySQL 5.7+ and MariaDB 10.5+ JSON functions.
 */
final readonly class MySqlPlatformHelper implements SqlPlatformHelperInterface
{
    public function jsonArrayAgg(string $expr): string
    {
        return sprintf('JSON_ARRAYAGG(%s)', $expr);
    }

    public function jsonObjectAgg(string $key, string $value): string
    {
        return sprintf('JSON_OBJECTAGG(%s, %s)', $key, $value);
    }

    public function jsonRemoveKey(string $doc, string $key): string
    {
        return sprintf("JSON_REMOVE(%s, '$.%s')", $doc, $key);
    }

    public function regexpMatch(string $column, string $pattern): string
    {
        return sprintf('%s REGEXP %s', $column, $pattern);
    }

    public function groupConcat(string $expr, string $separator, ?string $orderBy = null): string
    {
        $sql = sprintf('GROUP_CONCAT(%s', $expr);
        if (null !== $orderBy) {
            $sql .= sprintf(' ORDER BY %s', $orderBy);
        }
        $sql .= sprintf(' SEPARATOR %s)', $separator);

        return $sql;
    }

    public function jsonArray(): string
    {
        return 'JSON_ARRAY()';
    }
}
