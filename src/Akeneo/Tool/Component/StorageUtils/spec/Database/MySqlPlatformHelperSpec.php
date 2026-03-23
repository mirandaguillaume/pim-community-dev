<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\StorageUtils\Database;

use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use PhpSpec\ObjectBehavior;

final class MySqlPlatformHelperSpec extends ObjectBehavior
{
    public function it_implements_the_interface(): void
    {
        $this->shouldImplement(SqlPlatformHelperInterface::class);
    }

    public function it_generates_json_array_agg(): void
    {
        $this->jsonArrayAgg('locale.code')->shouldReturn('JSON_ARRAYAGG(locale.code)');
    }

    public function it_generates_json_object_agg(): void
    {
        $this->jsonObjectAgg('l.id', 'l.code')->shouldReturn('JSON_OBJECTAGG(l.id, l.code)');
    }

    public function it_generates_json_remove_key(): void
    {
        $this->jsonRemoveKey('doc', 'NO_LOCALE')->shouldReturn("JSON_REMOVE(doc, '$.NO_LOCALE')");
    }

    public function it_generates_regexp_match(): void
    {
        $this->regexpMatch('raw_parameters', ':regex')->shouldReturn('raw_parameters REGEXP :regex');
    }

    public function it_generates_group_concat_without_order(): void
    {
        $this->groupConcat('currency.code', "'-'")->shouldReturn("GROUP_CONCAT(currency.code SEPARATOR '-')");
    }

    public function it_generates_group_concat_with_order(): void
    {
        $this->groupConcat('currency.code', "'-'", 'currency.code')
            ->shouldReturn("GROUP_CONCAT(currency.code ORDER BY currency.code SEPARATOR '-')");
    }

    public function it_generates_empty_json_array(): void
    {
        $this->jsonArray()->shouldReturn('JSON_ARRAY()');
    }

    public function it_generates_json_extract(): void
    {
        $this->jsonExtract('raw_values', '$.sku')->shouldReturn("JSON_EXTRACT(raw_values, '$.sku')");
    }

    public function it_generates_json_extract_with_nested_path(): void
    {
        $this->jsonExtract('scores', '$."rates"')->shouldReturn('JSON_EXTRACT(scores, \'$."rates"\')');
    }

    public function it_generates_json_extract_text(): void
    {
        $this->jsonExtractText('scores', '$.average_ranks_consolidated_at')
            ->shouldReturn("JSON_UNQUOTE(JSON_EXTRACT(scores, '$.average_ranks_consolidated_at'))");
    }

    public function it_generates_json_merge_patch(): void
    {
        $this->jsonMergePatch("COALESCE(pm1.raw_values, '{}')", "COALESCE(pm.raw_values, '{}')", "p.raw_values")
            ->shouldReturn("JSON_MERGE_PATCH(COALESCE(pm1.raw_values, '{}'), COALESCE(pm.raw_values, '{}'), p.raw_values)");
    }

    public function it_generates_json_merge_preserve(): void
    {
        $this->jsonMergePreserve("COALESCE(pm2.quantified_associations, '{}')", "p.quantified_associations")
            ->shouldReturn("JSON_MERGE_PRESERVE(COALESCE(pm2.quantified_associations, '{}'), p.quantified_associations)");
    }

    public function it_generates_conditional(): void
    {
        $this->conditional('attribute.is_scopable', 'channel.code', "'<all_channels>'")
            ->shouldReturn("IF(attribute.is_scopable, channel.code, '<all_channels>')");
    }
}
