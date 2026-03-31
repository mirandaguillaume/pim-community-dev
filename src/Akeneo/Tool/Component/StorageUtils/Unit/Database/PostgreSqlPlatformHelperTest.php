<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Database;

use Akeneo\Tool\Component\StorageUtils\Database\PostgreSqlPlatformHelper;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use PHPUnit\Framework\TestCase;

class PostgreSqlPlatformHelperTest extends TestCase
{
    private PostgreSqlPlatformHelper $sut;

    protected function setUp(): void
    {
        $this->sut = new PostgreSqlPlatformHelper();
    }

    public function test_it_implements_the_interface(): void
    {
        $this->assertInstanceOf(SqlPlatformHelperInterface::class, $this->sut);
    }

    public function test_it_generates_json_array_agg(): void
    {
        $this->assertSame('jsonb_agg(locale.code)', $this->sut->jsonArrayAgg('locale.code'));
    }

    public function test_it_generates_json_object_agg(): void
    {
        $this->assertSame('jsonb_object_agg(l.id, l.code)', $this->sut->jsonObjectAgg('l.id', 'l.code'));
    }

    public function test_it_generates_json_remove_key(): void
    {
        $this->assertSame("(doc - 'NO_LOCALE')", $this->sut->jsonRemoveKey('doc', 'NO_LOCALE'));
    }

    public function test_it_generates_regexp_match(): void
    {
        $this->assertSame('raw_parameters ~ :regex', $this->sut->regexpMatch('raw_parameters', ':regex'));
    }

    public function test_it_generates_group_concat_without_order(): void
    {
        $this->assertSame("STRING_AGG(currency.code, '-')", $this->sut->groupConcat('currency.code', "'-'"));
    }

    public function test_it_generates_group_concat_with_order(): void
    {
        $this->assertSame("STRING_AGG(currency.code, '-' ORDER BY currency.code)", $this->sut->groupConcat('currency.code', "'-'", 'currency.code'));
    }

    public function test_it_generates_empty_json_array(): void
    {
        $this->assertSame("'[]'::jsonb", $this->sut->jsonArray());
    }

    public function test_it_generates_json_extract(): void
    {
        $this->assertSame("(raw_values #> '{sku}')", $this->sut->jsonExtract('raw_values', '$.sku'));
    }

    public function test_it_generates_json_extract_with_nested_path(): void
    {
        $this->assertSame("(raw_values #> '{foo,bar}')", $this->sut->jsonExtract('raw_values', '$.foo.bar'));
    }

    public function test_it_generates_json_extract_with_quoted_key(): void
    {
        $this->assertSame("(scores #> '{rates}')", $this->sut->jsonExtract('scores', '$."rates"'));
    }

    public function test_it_generates_json_extract_with_dotted_quoted_key(): void
    {
        $this->assertSame("(data #> '{foo.bar}')", $this->sut->jsonExtract('data', '$."foo.bar"'));
    }

    public function test_it_generates_json_extract_text(): void
    {
        $this->assertSame("(scores #>> '{status}')", $this->sut->jsonExtractText('scores', '$.status'));
    }

    public function test_it_generates_json_merge_patch(): void
    {
        $this->assertSame("(COALESCE(pm1.raw_values, '{}') || p.raw_values)", $this->sut->jsonMergePatch("COALESCE(pm1.raw_values, '{}')", "p.raw_values"));
    }

    public function test_it_throws_when_json_merge_patch_has_fewer_than_two_args(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->jsonMergePatch("doc");
    }

    public function test_it_generates_json_merge_preserve(): void
    {
        $this->assertSame("(COALESCE(pm2.qa, '{}') || p.qa)", $this->sut->jsonMergePreserve("COALESCE(pm2.qa, '{}')", "p.qa"));
    }

    public function test_it_throws_when_json_merge_preserve_has_fewer_than_two_args(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->jsonMergePreserve("doc");
    }

    public function test_it_generates_conditional(): void
    {
        $this->assertSame("CASE WHEN a.is_scopable THEN c.code ELSE '<all_channels>' END", $this->sut->conditional('a.is_scopable', 'c.code', "'<all_channels>'"));
    }

    public function test_it_generates_json_path_query(): void
    {
        $this->assertSame("jsonb_path_query_array(raw_values, '\$.*.*.*')", $this->sut->jsonPathQuery('raw_values', '$.*.*.*'));
    }

    public function test_it_generates_json_length(): void
    {
        $this->assertSame('jsonb_array_length(some_expr)', $this->sut->jsonLength('some_expr'));
    }

    public function test_it_generates_json_type(): void
    {
        $this->assertSame('UPPER(jsonb_typeof(image_value))', $this->sut->jsonType('image_value'));
    }

    public function test_it_generates_json_path_exists(): void
    {
        $this->assertSame("jsonb_path_exists(scores, '\$.average_ranks_consolidated_at')", $this->sut->jsonPathExists('scores', '$.average_ranks_consolidated_at'));
    }

    public function test_it_generates_json_path_exists_with_wildcard(): void
    {
        $this->assertSame("jsonb_path_exists(quantified_associations, '\$.*.products[*].id')", $this->sut->jsonPathExists('quantified_associations', '$.*.products[*].id'));
    }

    public function test_it_generates_json_contains(): void
    {
        $this->assertSame('some_array @> to_jsonb(:familyVariantCode::text)', $this->sut->jsonContains('some_array', ':familyVariantCode'));
    }

    public function test_it_generates_upsert_clause(): void
    {
        $this->assertSame('ON CONFLICT (product_uuid) DO UPDATE SET completeness = EXCLUDED.completeness', $this->sut->upsertClause(
            ['product_uuid'],
            ['completeness = EXCLUDED.completeness']
        ));
    }

    public function test_it_generates_upsert_clause_with_multiple_conflict_columns(): void
    {
        $this->assertSame('ON CONFLICT (connection_code, event_datetime, event_type) DO UPDATE SET event_count = event_count + :count', $this->sut->upsertClause(
            ['connection_code', 'event_datetime', 'event_type'],
            ['event_count = event_count + :count']
        ));
    }

    public function test_it_generates_inserted_value(): void
    {
        $this->assertSame('EXCLUDED.completeness', $this->sut->insertedValue('completeness'));
    }
}
