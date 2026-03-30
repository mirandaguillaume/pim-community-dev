<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Database;

use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\StorageUtils\Database\MySqlPlatformHelper;

class MySqlPlatformHelperTest extends TestCase
{
    private MySqlPlatformHelper $sut;

    protected function setUp(): void
    {
        $this->sut = new MySqlPlatformHelper();
    }

    public function test_it_implements_the_interface(): void
    {
        $this->assertInstanceOf(SqlPlatformHelperInterface::class, $this->sut);
    }

    public function test_it_generates_json_array_agg(): void
    {
        $this->assertSame('JSON_ARRAYAGG(locale.code)', $this->sut->jsonArrayAgg('locale.code'));
    }

    public function test_it_generates_json_object_agg(): void
    {
        $this->assertSame('JSON_OBJECTAGG(l.id, l.code)', $this->sut->jsonObjectAgg('l.id', 'l.code'));
    }

    public function test_it_generates_json_remove_key(): void
    {
        $this->assertSame("JSON_REMOVE(doc, '$.NO_LOCALE')", $this->sut->jsonRemoveKey('doc', 'NO_LOCALE'));
    }

    public function test_it_generates_regexp_match(): void
    {
        $this->assertSame('raw_parameters REGEXP :regex', $this->sut->regexpMatch('raw_parameters', ':regex'));
    }

    public function test_it_generates_group_concat_without_order(): void
    {
        $this->assertSame("GROUP_CONCAT(currency.code SEPARATOR '-')", $this->sut->groupConcat('currency.code', "'-'"));
    }

    public function test_it_generates_group_concat_with_order(): void
    {
        $this->assertSame("GROUP_CONCAT(currency.code ORDER BY currency.code SEPARATOR '-')", $this->sut->groupConcat('currency.code', "'-'", 'currency.code'));
    }

    public function test_it_generates_empty_json_array(): void
    {
        $this->assertSame('JSON_ARRAY()', $this->sut->jsonArray());
    }

    public function test_it_generates_json_extract(): void
    {
        $this->assertSame("JSON_EXTRACT(raw_values, '$.sku')", $this->sut->jsonExtract('raw_values', '$.sku'));
    }

    public function test_it_generates_json_extract_with_nested_path(): void
    {
        $this->assertSame('JSON_EXTRACT(scores, \'$."rates"\')', $this->sut->jsonExtract('scores', '$."rates"'));
    }

    public function test_it_generates_json_extract_text(): void
    {
        $this->assertSame("JSON_UNQUOTE(JSON_EXTRACT(scores, '$.average_ranks_consolidated_at'))", $this->sut->jsonExtractText('scores', '$.average_ranks_consolidated_at'));
    }

    public function test_it_generates_json_merge_patch(): void
    {
        $this->assertSame("JSON_MERGE_PATCH(COALESCE(pm1.raw_values, '{}'), COALESCE(pm.raw_values, '{}'), p.raw_values)", $this->sut->jsonMergePatch("COALESCE(pm1.raw_values, '{}')", "COALESCE(pm.raw_values, '{}')", "p.raw_values"));
    }

    public function test_it_throws_when_json_merge_patch_has_fewer_than_two_args(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->jsonMergePatch("doc");
    }

    public function test_it_generates_json_merge_preserve(): void
    {
        $this->assertSame("JSON_MERGE_PRESERVE(COALESCE(pm2.quantified_associations, '{}'), p.quantified_associations)", $this->sut->jsonMergePreserve("COALESCE(pm2.quantified_associations, '{}')", "p.quantified_associations"));
    }

    public function test_it_throws_when_json_merge_preserve_has_fewer_than_two_args(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->jsonMergePreserve("doc");
    }

    public function test_it_generates_conditional(): void
    {
        $this->assertSame("IF(attribute.is_scopable, channel.code, '<all_channels>')", $this->sut->conditional('attribute.is_scopable', 'channel.code', "'<all_channels>'"));
    }

    public function test_it_generates_json_path_query(): void
    {
        $this->assertSame("JSON_EXTRACT(raw_values, '\$.*.*.*')", $this->sut->jsonPathQuery('raw_values', '$.*.*.*'));
    }

    public function test_it_generates_json_length(): void
    {
        $this->assertSame('JSON_LENGTH(some_expr)', $this->sut->jsonLength('some_expr'));
    }

    public function test_it_generates_json_type(): void
    {
        $this->assertSame('JSON_TYPE(image_value)', $this->sut->jsonType('image_value'));
    }

    public function test_it_generates_json_path_exists(): void
    {
        $this->assertSame("JSON_CONTAINS_PATH(scores, 'one', '\$.average_ranks_consolidated_at')", $this->sut->jsonPathExists('scores', '$.average_ranks_consolidated_at'));
    }

    public function test_it_generates_json_contains(): void
    {
        $this->assertSame(':familyVariantCode MEMBER OF(some_array)', $this->sut->jsonContains('some_array', ':familyVariantCode'));
    }

    public function test_it_generates_upsert_clause(): void
    {
        $this->assertSame('ON DUPLICATE KEY UPDATE completeness = VALUES(completeness)', $this->sut->upsertClause(
            ['product_uuid'],
            ['completeness = VALUES(completeness)']
        ));
    }

    public function test_it_generates_upsert_clause_with_multiple_update_expressions(): void
    {
        $this->assertSame('ON DUPLICATE KEY UPDATE labels = :labels, units = :units', $this->sut->upsertClause(
            ['code'],
            ['labels = :labels', 'units = :units']
        ));
    }

    public function test_it_generates_inserted_value(): void
    {
        $this->assertSame('VALUES(completeness)', $this->sut->insertedValue('completeness'));
    }
}
