<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\StorageUtils\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use PhpSpec\ObjectBehavior;

final class SqlPlatformHelperSpec extends ObjectBehavior
{
    // -- jsonArrayAgg --

    public function it_generates_mysql_json_array_agg(Connection $connection): void
    {
        $connection->getDatabasePlatform()->willReturn(new MySQLPlatform());
        $this->beConstructedWith($connection);
        $this->jsonArrayAgg('locale.code')->shouldReturn('JSON_ARRAYAGG(locale.code)');
    }

    public function it_generates_postgresql_json_array_agg(Connection $connection): void
    {
        $connection->getDatabasePlatform()->willReturn(new PostgreSQLPlatform());
        $this->beConstructedWith($connection);
        $this->jsonArrayAgg('locale.code')->shouldReturn('jsonb_agg(locale.code)');
    }

    // -- jsonObjectAgg --

    public function it_generates_mysql_json_object_agg(Connection $connection): void
    {
        $connection->getDatabasePlatform()->willReturn(new MySQLPlatform());
        $this->beConstructedWith($connection);
        $this->jsonObjectAgg('l.id', 'l.code')->shouldReturn('JSON_OBJECTAGG(l.id, l.code)');
    }

    public function it_generates_postgresql_json_object_agg(Connection $connection): void
    {
        $connection->getDatabasePlatform()->willReturn(new PostgreSQLPlatform());
        $this->beConstructedWith($connection);
        $this->jsonObjectAgg('l.id', 'l.code')->shouldReturn('jsonb_object_agg(l.id, l.code)');
    }

    // -- jsonRemoveKey --

    public function it_generates_mysql_json_remove_key(Connection $connection): void
    {
        $connection->getDatabasePlatform()->willReturn(new MySQLPlatform());
        $this->beConstructedWith($connection);
        $this->jsonRemoveKey('doc', 'NO_LOCALE')->shouldReturn("JSON_REMOVE(doc, '$.NO_LOCALE')");
    }

    public function it_generates_postgresql_json_remove_key(Connection $connection): void
    {
        $connection->getDatabasePlatform()->willReturn(new PostgreSQLPlatform());
        $this->beConstructedWith($connection);
        $this->jsonRemoveKey('doc', 'NO_LOCALE')->shouldReturn("(doc - 'NO_LOCALE')");
    }

    // -- regexpMatch --

    public function it_generates_mysql_regexp_match(Connection $connection): void
    {
        $connection->getDatabasePlatform()->willReturn(new MySQLPlatform());
        $this->beConstructedWith($connection);
        $this->regexpMatch('raw_parameters', ':regex')->shouldReturn('raw_parameters REGEXP :regex');
    }

    public function it_generates_postgresql_regexp_match(Connection $connection): void
    {
        $connection->getDatabasePlatform()->willReturn(new PostgreSQLPlatform());
        $this->beConstructedWith($connection);
        $this->regexpMatch('raw_parameters', ':regex')->shouldReturn('raw_parameters ~ :regex');
    }

    // -- groupConcat --

    public function it_generates_mysql_group_concat_without_order(Connection $connection): void
    {
        $connection->getDatabasePlatform()->willReturn(new MySQLPlatform());
        $this->beConstructedWith($connection);
        $this->groupConcat('currency.code', "'-'")->shouldReturn("GROUP_CONCAT(currency.code SEPARATOR '-')");
    }

    public function it_generates_mysql_group_concat_with_order(Connection $connection): void
    {
        $connection->getDatabasePlatform()->willReturn(new MySQLPlatform());
        $this->beConstructedWith($connection);
        $this->groupConcat('currency.code', "'-'", 'currency.code')
            ->shouldReturn("GROUP_CONCAT(currency.code ORDER BY currency.code SEPARATOR '-')");
    }

    public function it_generates_postgresql_group_concat_without_order(Connection $connection): void
    {
        $connection->getDatabasePlatform()->willReturn(new PostgreSQLPlatform());
        $this->beConstructedWith($connection);
        $this->groupConcat('currency.code', "'-'")->shouldReturn("STRING_AGG(currency.code, '-')");
    }

    public function it_generates_postgresql_group_concat_with_order(Connection $connection): void
    {
        $connection->getDatabasePlatform()->willReturn(new PostgreSQLPlatform());
        $this->beConstructedWith($connection);
        $this->groupConcat('currency.code', "'-'", 'currency.code')
            ->shouldReturn("STRING_AGG(currency.code, '-' ORDER BY currency.code)");
    }
}
