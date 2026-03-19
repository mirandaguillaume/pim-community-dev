<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;

/**
 * Provides platform-aware SQL helper methods to generate native MySQL or PostgreSQL syntax.
 *
 * Classes using this trait must implement getConnection() to return their DBAL Connection.
 * Each helper generates a raw SQL fragment appropriate for the current database platform.
 */
trait DatabasePlatformTrait
{
    abstract private function getConnection(): Connection;

    private function isMySQL(): bool
    {
        return $this->getConnection()->getDatabasePlatform() instanceof MySQLPlatform;
    }

    /**
     * Aggregates values into a JSON array.
     *
     * MySQL:      JSON_ARRAYAGG(expr)
     * PostgreSQL: jsonb_agg(expr)
     */
    private function jsonArrayAgg(string $expr): string
    {
        return $this->isMySQL()
            ? sprintf('JSON_ARRAYAGG(%s)', $expr)
            : sprintf('jsonb_agg(%s)', $expr);
    }

    /**
     * Aggregates key/value pairs into a JSON object.
     *
     * MySQL:      JSON_OBJECTAGG(key, value)
     * PostgreSQL: jsonb_object_agg(key, value)
     */
    private function jsonObjectAgg(string $key, string $value): string
    {
        return $this->isMySQL()
            ? sprintf('JSON_OBJECTAGG(%s, %s)', $key, $value)
            : sprintf('jsonb_object_agg(%s, %s)', $key, $value);
    }

    /**
     * Removes a top-level key from a JSON document.
     *
     * MySQL:      JSON_REMOVE(doc, '$.key')
     * PostgreSQL: (doc - 'key')
     */
    private function jsonRemoveKey(string $doc, string $key): string
    {
        return $this->isMySQL()
            ? sprintf("JSON_REMOVE(%s, '$.%s')", $doc, $key)
            : sprintf("(%s - '%s')", $doc, $key);
    }

    /**
     * Matches a column against a regular expression pattern.
     *
     * MySQL:      column REGEXP pattern
     * PostgreSQL: column ~ pattern
     */
    private function regexpMatch(string $column, string $pattern): string
    {
        return $this->isMySQL()
            ? sprintf('%s REGEXP %s', $column, $pattern)
            : sprintf('%s ~ %s', $column, $pattern);
    }

    /**
     * Concatenates grouped values with a separator, optionally ordered.
     *
     * MySQL:      GROUP_CONCAT(expr ORDER BY orderExpr SEPARATOR sep)
     * PostgreSQL: STRING_AGG(expr, sep ORDER BY orderExpr)
     *
     * @param string      $expr     The expression to concatenate
     * @param string      $separator The separator string (e.g. "'-'")
     * @param string|null $orderBy  Optional ORDER BY expression
     */
    private function groupConcat(string $expr, string $separator, ?string $orderBy = null): string
    {
        if ($this->isMySQL()) {
            $sql = sprintf('GROUP_CONCAT(%s', $expr);
            if (null !== $orderBy) {
                $sql .= sprintf(' ORDER BY %s', $orderBy);
            }
            $sql .= sprintf(' SEPARATOR %s)', $separator);

            return $sql;
        }

        $sql = sprintf('STRING_AGG(%s, %s', $expr, $separator);
        if (null !== $orderBy) {
            $sql .= sprintf(' ORDER BY %s', $orderBy);
        }
        $sql .= ')';

        return $sql;
    }
}
