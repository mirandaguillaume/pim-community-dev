<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;

/**
 * Generates platform-native SQL fragments for MySQL or PostgreSQL.
 *
 * Inject this service into any query class that needs database-portable SQL.
 * The platform is resolved once at construction time (not per query).
 *
 * @example
 *   public function __construct(
 *       private Connection $connection,
 *       private SqlPlatformHelper $sql,
 *   ) {}
 *
 *   public function findAll(): array {
 *       $agg = $this->sql->jsonArrayAgg('locale.code');
 *       return $this->connection->executeQuery("SELECT $agg FROM ...")->...;
 *   }
 */
final readonly class SqlPlatformHelper
{
    private bool $isMySQL;

    public function __construct(Connection $connection)
    {
        $this->isMySQL = $connection->getDatabasePlatform() instanceof MySQLPlatform;
    }

    /**
     * Aggregates values into a JSON array.
     *
     * MySQL:      JSON_ARRAYAGG(expr)
     * PostgreSQL: jsonb_agg(expr)
     */
    public function jsonArrayAgg(string $expr): string
    {
        return $this->isMySQL
            ? sprintf('JSON_ARRAYAGG(%s)', $expr)
            : sprintf('jsonb_agg(%s)', $expr);
    }

    /**
     * Aggregates key/value pairs into a JSON object.
     *
     * MySQL:      JSON_OBJECTAGG(key, value)
     * PostgreSQL: jsonb_object_agg(key, value)
     */
    public function jsonObjectAgg(string $key, string $value): string
    {
        return $this->isMySQL
            ? sprintf('JSON_OBJECTAGG(%s, %s)', $key, $value)
            : sprintf('jsonb_object_agg(%s, %s)', $key, $value);
    }

    /**
     * Removes a top-level key from a JSON document.
     *
     * MySQL:      JSON_REMOVE(doc, '$.key')
     * PostgreSQL: (doc - 'key')
     */
    public function jsonRemoveKey(string $doc, string $key): string
    {
        return $this->isMySQL
            ? sprintf("JSON_REMOVE(%s, '$.%s')", $doc, $key)
            : sprintf("(%s - '%s')", $doc, $key);
    }

    /**
     * Matches a column against a regular expression pattern.
     *
     * MySQL:      column REGEXP pattern
     * PostgreSQL: column ~ pattern
     */
    public function regexpMatch(string $column, string $pattern): string
    {
        return $this->isMySQL
            ? sprintf('%s REGEXP %s', $column, $pattern)
            : sprintf('%s ~ %s', $column, $pattern);
    }

    /**
     * Concatenates grouped values with a separator, optionally ordered.
     *
     * MySQL:      GROUP_CONCAT(expr ORDER BY orderExpr SEPARATOR sep)
     * PostgreSQL: STRING_AGG(expr, sep ORDER BY orderExpr)
     *
     * @param string      $expr      The expression to concatenate
     * @param string      $separator The separator string (e.g. "'-'")
     * @param string|null $orderBy   Optional ORDER BY expression
     */
    public function groupConcat(string $expr, string $separator, ?string $orderBy = null): string
    {
        if ($this->isMySQL) {
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
