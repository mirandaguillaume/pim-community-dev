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
}
