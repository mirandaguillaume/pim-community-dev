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

        return $sql . ')';
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
        if (\count($docs) < 2) {
            throw new \InvalidArgumentException('jsonMergePatch requires at least 2 documents');
        }

        return '(' . implode(' || ', $docs) . ')';
    }

    public function jsonMergePreserve(string ...$docs): string
    {
        if (\count($docs) < 2) {
            throw new \InvalidArgumentException('jsonMergePreserve requires at least 2 documents');
        }

        // PG || is last-key-wins for objects. True array-preserving merge
        // will require a custom SQL function when PG migration starts.
        return '(' . implode(' || ', $docs) . ')';
    }

    public function conditional(string $condition, string $then, string $else): string
    {
        return sprintf('CASE WHEN %s THEN %s ELSE %s END', $condition, $then, $else);
    }

    public function jsonPathQuery(string $doc, string $path): string
    {
        return sprintf("jsonb_path_query_array(%s, '%s')", $doc, $path);
    }

    public function jsonLength(string $expr): string
    {
        return sprintf('jsonb_array_length(%s)', $expr);
    }

    public function jsonType(string $expr): string
    {
        return sprintf('UPPER(jsonb_typeof(%s))', $expr);
    }

    public function jsonPathExists(string $doc, string $path): string
    {
        return sprintf("jsonb_path_exists(%s, '%s')", $doc, $path);
    }

    public function jsonContains(string $arrayExpr, string $valueExpr): string
    {
        return sprintf('%s @> to_jsonb(%s::text)', $arrayExpr, $valueExpr);
    }

    public function upsertClause(array $conflictColumns, array $updateExpressions): string
    {
        return sprintf(
            'ON CONFLICT (%s) DO UPDATE SET %s',
            implode(', ', $conflictColumns),
            implode(', ', $updateExpressions)
        );
    }

    public function insertedValue(string $column): string
    {
        return sprintf('EXCLUDED.%s', $column);
    }

    /**
     * Converts a MySQL JSON path ('$.key', '$."key"', '$.foo.bar')
     * to PostgreSQL array path format ('{key}', '{foo,bar}').
     */
    private function convertJsonPath(string $mysqlPath): string
    {
        $path = preg_replace('/^\$\.?/', '', $mysqlPath);
        // Split on dots NOT inside double-quotes
        preg_match_all('/"[^"]*"|[^.]+/', $path, $matches);
        $cleaned = array_map(static fn (string $s): string => trim($s, '"'), $matches[0]);

        return "'{" . implode(',', $cleaned) . "}'";
    }
}
