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

    public function jsonExtract(string $doc, string $path): string
    {
        return sprintf("JSON_EXTRACT(%s, '%s')", $doc, $path);
    }

    public function jsonExtractText(string $doc, string $path): string
    {
        return sprintf("JSON_UNQUOTE(JSON_EXTRACT(%s, '%s'))", $doc, $path);
    }

    public function jsonMergePatch(string ...$docs): string
    {
        if (\count($docs) < 2) {
            throw new \InvalidArgumentException('jsonMergePatch requires at least 2 documents');
        }

        return sprintf('JSON_MERGE_PATCH(%s)', implode(', ', $docs));
    }

    public function jsonMergePreserve(string ...$docs): string
    {
        if (\count($docs) < 2) {
            throw new \InvalidArgumentException('jsonMergePreserve requires at least 2 documents');
        }

        return sprintf('JSON_MERGE_PRESERVE(%s)', implode(', ', $docs));
    }

    public function conditional(string $condition, string $then, string $else): string
    {
        return sprintf('IF(%s, %s, %s)', $condition, $then, $else);
    }

    public function jsonPathQuery(string $doc, string $path): string
    {
        return sprintf("JSON_EXTRACT(%s, '%s')", $doc, $path);
    }

    public function jsonLength(string $expr): string
    {
        return sprintf('JSON_LENGTH(%s)', $expr);
    }

    public function jsonType(string $expr): string
    {
        return sprintf('JSON_TYPE(%s)', $expr);
    }

    public function jsonPathExists(string $doc, string $path): string
    {
        return sprintf("JSON_CONTAINS_PATH(%s, 'one', '%s')", $doc, $path);
    }

    public function jsonContains(string $arrayExpr, string $valueExpr): string
    {
        return sprintf('%s MEMBER OF(%s)', $valueExpr, $arrayExpr);
    }
}
