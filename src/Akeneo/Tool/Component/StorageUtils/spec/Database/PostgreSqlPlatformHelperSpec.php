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

    public function it_generates_json_extract_text(): void
    {
        $this->jsonExtractText('scores', '$.status')->shouldReturn("(scores #>> '{status}')");
    }

    public function it_generates_json_merge_patch(): void
    {
        $this->jsonMergePatch("COALESCE(pm1.raw_values, '{}')", "p.raw_values")
            ->shouldReturn("(COALESCE(pm1.raw_values, '{}') || p.raw_values)");
    }

    public function it_generates_json_merge_preserve(): void
    {
        $this->jsonMergePreserve("COALESCE(pm2.qa, '{}')", "p.qa")
            ->shouldReturn("(COALESCE(pm2.qa, '{}') || p.qa)");
    }

    public function it_generates_conditional(): void
    {
        $this->conditional('a.is_scopable', 'c.code', "'<all_channels>'")
            ->shouldReturn("CASE WHEN a.is_scopable THEN c.code ELSE '<all_channels>' END");
    }
}
