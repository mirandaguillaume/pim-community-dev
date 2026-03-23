<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Database;

/**
 * Generates platform-native SQL fragments for database-portable queries.
 *
 * Implement this interface for each supported database platform.
 * Inject it into query classes that need non-standard SQL (JSON aggregation,
 * regex matching, grouped concatenation).
 *
 * @api
 */
interface SqlPlatformHelperInterface
{
    /**
     * Aggregates values into a JSON array.
     */
    public function jsonArrayAgg(string $expr): string;

    /**
     * Aggregates key/value pairs into a JSON object.
     */
    public function jsonObjectAgg(string $key, string $value): string;

    /**
     * Removes a top-level key from a JSON document.
     */
    public function jsonRemoveKey(string $doc, string $key): string;

    /**
     * Matches a column against a regular expression pattern.
     */
    public function regexpMatch(string $column, string $pattern): string;

    /**
     * Concatenates grouped values with a separator, optionally ordered.
     *
     * @param string      $expr      The expression to concatenate
     * @param string      $separator The separator string (e.g. "'-'")
     * @param string|null $orderBy   Optional ORDER BY expression
     */
    public function groupConcat(string $expr, string $separator, ?string $orderBy = null): string;

    /**
     * Returns an empty JSON array literal.
     */
    public function jsonArray(): string;

    /**
     * Extracts a value from a JSON document at the given path.
     * Returns a JSON-typed value (quoted strings, typed scalars).
     *
     * The path uses MySQL JSON path syntax (e.g. '$.sku', '$."attribute_code"').
     * Do NOT include outer quotes — the method handles quoting internally.
     *
     * @param string $doc  The JSON column or expression
     * @param string $path The JSON path (e.g. '$.sku', '$."attribute_code"')
     */
    public function jsonExtract(string $doc, string $path): string;

    /**
     * Extracts a scalar text value from a JSON document (unquoted).
     * Use this in WHERE clauses for string comparisons.
     *
     * Same path contract as jsonExtract().
     *
     * @param string $doc  The JSON column or expression
     * @param string $path The JSON path
     */
    public function jsonExtractText(string $doc, string $path): string;

    /**
     * Merges JSON documents with RFC 7396 patch semantics (last value wins,
     * null removes keys). Use for product value inheritance chains.
     *
     * @param string ...$docs Two or more JSON expressions to merge
     */
    public function jsonMergePatch(string ...$docs): string;

    /**
     * Merges JSON documents preserving all array elements (no dedup).
     * Use for quantified associations and array-type merges.
     *
     * Note: PostgreSQL implementation uses || which is last-key-wins for
     * objects. True array-preserving merge will require a custom PG function
     * when PostgreSQL migration starts.
     *
     * @param string ...$docs Two or more JSON expressions to merge
     */
    public function jsonMergePreserve(string ...$docs): string;

    /**
     * Returns a conditional expression (ternary).
     *
     * @param string $condition The boolean condition
     * @param string $then      The value when true
     * @param string $else      The value when false
     */
    public function conditional(string $condition, string $then, string $else): string;

    /**
     * Extracts all JSON values matching a path, including wildcard paths.
     * Returns a JSON array of all matches.
     *
     * Use this instead of jsonExtract() when the path contains wildcards
     * (e.g. '$.*.*.*', '$.*.products[*].id').
     *
     * @param string $doc  The JSON column or expression
     * @param string $path SQL/JSON path (e.g. '$.*.*.*')
     */
    public function jsonPathQuery(string $doc, string $path): string;

    /**
     * Returns the number of elements in a JSON array.
     *
     * Note: on PostgreSQL, only works for arrays (not objects).
     * All current usages in this codebase pass array expressions.
     */
    public function jsonLength(string $expr): string;

    /**
     * Returns the type of a JSON value as an uppercase string.
     *
     * Returns: 'NULL', 'OBJECT', 'ARRAY', 'STRING', 'INTEGER', 'DOUBLE', 'BOOLEAN'
     * PostgreSQL implementation normalizes to uppercase to match MySQL's output.
     */
    public function jsonType(string $expr): string;

    /**
     * Checks whether a JSON path returns any items (existence check).
     * Supports wildcard paths.
     *
     * @param string $doc  The JSON column or expression
     * @param string $path SQL/JSON path to check
     */
    public function jsonPathExists(string $doc, string $path): string;

    /**
     * Checks if a JSON array contains a specific value.
     *
     * @param string $arrayExpr The JSON array expression
     * @param string $valueExpr The value to search for (e.g. ':param', 'column')
     */
    public function jsonContains(string $arrayExpr, string $valueExpr): string;
}
