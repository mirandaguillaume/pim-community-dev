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
}
