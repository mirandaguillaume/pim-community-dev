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

    public function jsonArray(): string
    {
        return "'[]'::jsonb";
    }

    public function jsonExtract(string $doc, string $path): string
    {
        $pgPath = $this->convertJsonPath($path);

        return sprintf('(%s #> %s)', $doc, $pgPath);
    }

    public function jsonExtractText(string $doc, string $path): string
    {
        $pgPath = $this->convertJsonPath($path);

        return sprintf('(%s #>> %s)', $doc, $pgPath);
    }

    public function jsonMergePatch(string ...$docs): string
    {
        return '(' . implode(' || ', $docs) . ')';
    }

    public function jsonMergePreserve(string ...$docs): string
    {
        // PG || is last-key-wins for objects. True array-preserving merge
        // will require a custom SQL function when PG migration starts.
        return '(' . implode(' || ', $docs) . ')';
    }

    public function conditional(string $condition, string $then, string $else): string
    {
        return sprintf('CASE WHEN %s THEN %s ELSE %s END', $condition, $then, $else);
    }

    /**
     * Converts a MySQL JSON path ('$.key', '$."key"', '$.foo.bar')
     * to PostgreSQL array path format ('{key}', '{foo,bar}').
     */
    private function convertJsonPath(string $mysqlPath): string
    {
        $path = preg_replace('/^\$\.?/', '', $mysqlPath);
        $segments = preg_split('/\./', $path);
        $cleaned = array_map(static fn (string $s): string => trim($s, '"'), $segments);

        return "'{" . implode(',', $cleaned) . "}'";
    }
}
