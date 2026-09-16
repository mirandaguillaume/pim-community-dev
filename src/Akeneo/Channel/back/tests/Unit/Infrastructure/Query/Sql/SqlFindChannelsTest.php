<?php

declare(strict_types=1);

namespace Akeneo\Channel\Test\Unit\Infrastructure\Query\Sql;

use Akeneo\Channel\Infrastructure\Query\Sql\SqlFindChannels;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlFindChannelsTest extends TestCase
{
    private Connection|MockObject $connection;
    private SqlPlatformHelperInterface|MockObject $platformHelper;
    private SqlFindChannels $sut;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->platformHelper = $this->createMock(SqlPlatformHelperInterface::class);
        $this->platformHelper->method('jsonObjectAgg')->willReturnArgument(0);
        $this->platformHelper->method('jsonRemoveKey')->willReturnArgument(0);

        $this->sut = new SqlFindChannels($this->connection, $this->platformHelper);
    }

    public function test_it_finds_no_channels_when_there_are_none(): void
    {
        $this->givenTheRows([]);

        $this->assertSame([], $this->sut->findAll());
    }

    public function test_it_finds_every_channel_with_its_locales_labels_currencies_and_conversion_units(): void
    {
        $this->givenTheRows([[
            'channelCode' => 'ecommerce',
            'localeCodes' => \json_encode(['1' => 'en_US']),
            'labels' => \json_encode(['en_US' => 'Ecommerce']),
            'activatedCurrencies' => \json_encode(['1' => 'USD']),
            'conversionUnits' => \serialize(['weight' => 'KILOGRAM']),
        ]]);

        $channels = $this->sut->findAll();

        $this->assertCount(1, $channels);
        $channel = $channels[0];
        $this->assertSame('ecommerce', $channel->getCode());
        $this->assertSame(['en_US'], $channel->getLocaleCodes());
        $this->assertSame('Ecommerce', $channel->getLabels()->getLabel('en_US'));
        $this->assertSame(['USD'], $channel->getActiveCurrencies());
        $this->assertSame('KILOGRAM', $channel->getConversionUnits()->getConversionUnit('weight'));
    }

    private function givenTheRows(array $rows): void
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn($rows);
        $this->connection->method('executeQuery')->willReturn($result);
    }
}
