<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\StorageUtils\Database;

use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use PhpSpec\ObjectBehavior;

final class PostgreSqlPlatformHelperSpec extends ObjectBehavior
{
    public function it_implements_the_interface(): void
    {
        $this->shouldImplement(SqlPlatformHelperInterface::class);
    }

    public function it_generates_json_array_agg(): void
    {
        $this->jsonArrayAgg('locale.code')->shouldReturn('jsonb_agg(locale.code)');
    }

    public function it_generates_json_object_agg(): void
    {
        $this->jsonObjectAgg('l.id', 'l.code')->shouldReturn('jsonb_object_agg(l.id, l.code)');
    }

    public function it_generates_json_remove_key(): void
    {
        $this->jsonRemoveKey('doc', 'NO_LOCALE')->shouldReturn("(doc - 'NO_LOCALE')");
    }

    public function it_generates_regexp_match(): void
    {
        $this->regexpMatch('raw_parameters', ':regex')->shouldReturn('raw_parameters ~ :regex');
    }

    public function it_generates_group_concat_without_order(): void
    {
        $this->groupConcat('currency.code', "'-'")->shouldReturn("STRING_AGG(currency.code, '-')");
    }

    public function it_generates_group_concat_with_order(): void
    {
        $this->groupConcat('currency.code', "'-'", 'currency.code')
            ->shouldReturn("STRING_AGG(currency.code, '-' ORDER BY currency.code)");
    }

    public function it_generates_empty_json_array(): void
    {
        $this->jsonArray()->shouldReturn("'[]'::jsonb");
    }

    public function it_generates_json_extract(): void
    {
        $this->jsonExtract('raw_values', '$.sku')->shouldReturn("(raw_values #> '{sku}')");
    }

    public function it_generates_json_extract_with_nested_path(): void
    {
        $this->jsonExtract('raw_values', '$.foo.bar')->shouldReturn("(raw_values #> '{foo,bar}')");
    }

    public function it_generates_json_extract_with_quoted_key(): void
    {
        $this->jsonExtract('scores', '$."rates"')->shouldReturn("(scores #> '{rates}')");
    }

    public function it_generates_json_extract_with_dotted_quoted_key(): void
    {
        $this->jsonExtract('data', '$."foo.bar"')->shouldReturn("(data #> '{foo.bar}')");
    }

    public function it_generates_json_extract_text(): void
    {
        $this->jsonExtractText('scores', '$.status')->shouldReturn("(scores #>> '{status}')");
    }

    public function it_generates_json_merge_patch(): void
    {
        $this->jsonMergePatch("COALESCE(pm1.raw_values, '{}')", "p.raw_values")
            ->shouldReturn("(COALESCE(pm1.raw_values, '{}') || p.raw_values)");
    }

    public function it_throws_when_json_merge_patch_has_fewer_than_two_args(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('jsonMergePatch', ["doc"]);
    }

    public function it_generates_json_merge_preserve(): void
    {
        $this->jsonMergePreserve("COALESCE(pm2.qa, '{}')", "p.qa")
            ->shouldReturn("(COALESCE(pm2.qa, '{}') || p.qa)");
    }

    public function it_throws_when_json_merge_preserve_has_fewer_than_two_args(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('jsonMergePreserve', ["doc"]);
    }

    public function it_generates_conditional(): void
    {
        $this->conditional('a.is_scopable', 'c.code', "'<all_channels>'")
            ->shouldReturn("CASE WHEN a.is_scopable THEN c.code ELSE '<all_channels>' END");
    }

    public function it_generates_json_path_query(): void
    {
        $this->jsonPathQuery('raw_values', '$.*.*.*')
            ->shouldReturn("jsonb_path_query_array(raw_values, '\$.*.*.*')");
    }

    public function it_generates_json_length(): void
    {
        $this->jsonLength('some_expr')->shouldReturn('jsonb_array_length(some_expr)');
    }

    public function it_generates_json_type(): void
    {
        $this->jsonType('image_value')->shouldReturn('UPPER(jsonb_typeof(image_value))');
    }

    public function it_generates_json_path_exists(): void
    {
        $this->jsonPathExists('scores', '$.average_ranks_consolidated_at')
            ->shouldReturn("jsonb_path_exists(scores, '\$.average_ranks_consolidated_at')");
    }

    public function it_generates_json_path_exists_with_wildcard(): void
    {
        $this->jsonPathExists('quantified_associations', '$.*.products[*].id')
            ->shouldReturn("jsonb_path_exists(quantified_associations, '\$.*.products[*].id')");
    }

    public function it_generates_json_contains(): void
    {
        $this->jsonContains('some_array', ':familyVariantCode')
            ->shouldReturn('some_array @> to_jsonb(:familyVariantCode::text)');
    }

    public function it_generates_upsert_clause(): void
    {
        $this->upsertClause(
            ['product_uuid'],
            ['completeness = EXCLUDED.completeness']
        )->shouldReturn('ON CONFLICT (product_uuid) DO UPDATE SET completeness = EXCLUDED.completeness');
    }

    public function it_generates_upsert_clause_with_multiple_conflict_columns(): void
    {
        $this->upsertClause(
            ['connection_code', 'event_datetime', 'event_type'],
            ['event_count = event_count + :count']
        )->shouldReturn('ON CONFLICT (connection_code, event_datetime, event_type) DO UPDATE SET event_count = event_count + :count');
    }

    public function it_generates_inserted_value(): void
    {
        $this->insertedValue('completeness')->shouldReturn('EXCLUDED.completeness');
    }
}
