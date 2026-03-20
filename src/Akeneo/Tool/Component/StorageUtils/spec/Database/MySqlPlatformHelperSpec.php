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
}
