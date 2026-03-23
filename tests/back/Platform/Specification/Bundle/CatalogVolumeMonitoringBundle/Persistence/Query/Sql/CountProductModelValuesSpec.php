<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\CountProductModelValues;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Prophecy\Argument;

class CountProductModelValuesSpec extends ObjectBehavior
{
    function let(Connection $connection, SqlPlatformHelperInterface $platformHelper)
    {
        $this->beConstructedWith($connection, $platformHelper);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CountProductModelValues::class);
    }

    function it_is_a_count_query()
    {
        $this->shouldImplement(CountQuery::class);
    }

    function it_gets_count_volume(Connection $connection, Result $statement, SqlPlatformHelperInterface $platformHelper)
    {
        $platformHelper->jsonPathQuery('raw_values', '$.*.*.*')->willReturn('JSON_EXTRACT(raw_values, \'$.*.*.*\')');
        $platformHelper->jsonLength(Argument::type('string'))->willReturn('JSON_LENGTH(JSON_EXTRACT(raw_values, \'$.*.*.*\'))');
        $connection->executeQuery(Argument::type('string'))->willReturn($statement);
        $statement->fetchAssociative()->willReturn(['sum_product_model_values' => '4']);
        $this->fetch()->shouldBeLike(new CountVolume(4, 'count_product_model_values'));
    }
}
