<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration;

use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\IndexConfiguration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\UpdateIndexMappingWithoutDowntime;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Infrastructure\Client\ClientMigrationInterface;
use Akeneo\Tool\Component\Elasticsearch\ClockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateIndexMappingWithoutDowntimeTest extends TestCase
{
    private const INDEX_NAME_TO_MIGRATE = 'index_name_to_migrate';
    private const INDEX_ALIAS_TO_MIGRATE = 'index_alias_to_migrate';
    private const MIGRATED_INDEX_NAME = 'migrated_index_name';
    private const TEMPORARY_INDEX_ALIAS = 'temporary_index_alias';

    private ClockInterface|MockObject $clock;
    private ClientMigrationInterface|MockObject $clientMigration;
    private IndexConfiguration|MockObject $indexConfiguration;
    private UpdateIndexMappingWithoutDowntime $sut;

    protected function setUp(): void
    {
        $this->clock = $this->createMock(ClockInterface::class);
        $this->clientMigration = $this->createMock(ClientMigrationInterface::class);
        $this->indexConfiguration = $this->createMock(IndexConfiguration::class);
        $this->sut = new UpdateIndexMappingWithoutDowntime(
            $this->clock,
            $this->clientMigration,
        );
        $this->indexConfiguration->method('buildAggregated')->willReturn([
            'settings' => [
                'index' => [
                    'number_of_shards' => 3,
                    'number_of_replicas' => 2,
                ],
            ],
            'mappings' => [
                'properties' => [
                    'name' => [
                        'properties' => [
                            'last' => [
                                'type' => 'text',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->clientMigration->method('getIndexSettings')->willReturnCallback(function (string $indexName) {
            return [
                'refresh_interval' => 5,
                'number_of_replicas' => 2,
            ];
        });
    }

    public function test_it_reindex_all_records_on_the_given_index(): void
    {
        $this->clientMigration->method('aliasExist')->with(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(true);

        $getIndexNameCallCount = 0;
        $this->clientMigration->method('getIndexNameFromAlias')->willReturnCallback(
            function () use (&$getIndexNameCallCount) {
                $getIndexNameCallCount++;
                return $getIndexNameCallCount === 1
                    ? [self::INDEX_NAME_TO_MIGRATE]
                    : [self::MIGRATED_INDEX_NAME];
            }
        );

        $this->clientMigration->expects($this->once())->method('createIndex');

        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:25-0700');

        $clockCallCount = 0;
        $clockValues = [$firstDatetime, $datetimeAfterFirstIndexation, $datetimeAfterSwitch];
        $this->clock->method('now')->willReturnCallback(function () use (&$clockCallCount, $clockValues) {
            return $clockValues[$clockCallCount++] ?? end($clockValues);
        });

        $reindexCallCount = 0;
        $this->clientMigration->method('reindex')->willReturnCallback(
            function (string $source, string $dest, array $query) use (&$reindexCallCount) {
                $reindexCallCount++;
                if ($reindexCallCount === 1) {
                    $this->assertSame(self::INDEX_ALIAS_TO_MIGRATE, $source);
                    $this->assertSame(self::TEMPORARY_INDEX_ALIAS, $dest);
                    return 10;
                }
                if ($reindexCallCount === 2) {
                    $this->assertSame(self::INDEX_ALIAS_TO_MIGRATE, $source);
                    $this->assertSame(self::TEMPORARY_INDEX_ALIAS, $dest);
                    return 0;
                }
                // After switch: source and dest are swapped
                $this->assertSame(self::TEMPORARY_INDEX_ALIAS, $source);
                $this->assertSame(self::INDEX_ALIAS_TO_MIGRATE, $dest);
                return 0;
            }
        );

        $this->clientMigration->expects($this->once())->method('putIndexSetting');
        $this->clientMigration->expects($this->once())->method('refreshIndex');
        $this->clientMigration->expects($this->once())->method('switchIndexAlias');
        $this->clientMigration->expects($this->once())->method('removeIndex')->with(self::INDEX_NAME_TO_MIGRATE);

        $this->sut->execute(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME,
            $this->indexConfiguration,
            fn (\DateTimeImmutable $referenceDatetime) => [
                'range' => [
                    'updated_at' => ['gt' => $referenceDatetime->getTimestamp()],
                ],
            ]
        );
    }

    public function test_it_handle_index_migration_without_alias(): void
    {
        $this->clientMigration->method('aliasExist')->with(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(false);

        $migrationAlias = 'index_alias_to_migrate_migration_alias';

        $getIndexNameCallCount = 0;
        $this->clientMigration->method('getIndexNameFromAlias')->willReturnCallback(
            function () use (&$getIndexNameCallCount) {
                $getIndexNameCallCount++;
                return $getIndexNameCallCount === 1
                    ? [self::INDEX_ALIAS_TO_MIGRATE]
                    : [self::MIGRATED_INDEX_NAME];
            }
        );

        $this->clientMigration->expects($this->once())->method('createAlias')->with($migrationAlias, self::INDEX_ALIAS_TO_MIGRATE);
        $this->clientMigration->expects($this->once())->method('createIndex');

        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:25-0700');

        $clockCallCount = 0;
        $clockValues = [$firstDatetime, $datetimeAfterFirstIndexation, $datetimeAfterSwitch];
        $this->clock->method('now')->willReturnCallback(function () use (&$clockCallCount, $clockValues) {
            return $clockValues[$clockCallCount++] ?? end($clockValues);
        });

        $reindexCallCount = 0;
        $this->clientMigration->method('reindex')->willReturnCallback(
            function (string $source, string $dest, array $query) use (&$reindexCallCount, $migrationAlias) {
                $reindexCallCount++;
                if ($reindexCallCount === 1) {
                    $this->assertSame($migrationAlias, $source);
                    return 10;
                }
                if ($reindexCallCount === 2) {
                    $this->assertSame($migrationAlias, $source);
                    return 0;
                }
                return 0;
            }
        );

        $this->clientMigration->expects($this->once())->method('putIndexSetting');
        $this->clientMigration->expects($this->once())->method('refreshIndex');
        $this->clientMigration->expects($this->once())->method('switchIndexAlias');
        $this->clientMigration->expects($this->once())->method('removeIndex')->with(self::INDEX_ALIAS_TO_MIGRATE);
        $this->clientMigration->expects($this->once())->method('renameAlias');

        $this->sut->execute(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME,
            $this->indexConfiguration,
            fn (\DateTimeImmutable $referenceDatetime) => [
                'range' => [
                    'updated_at' => ['gt' => $referenceDatetime->getTimestamp()],
                ],
            ]
        );
    }

    public function test_it_reindex_records_updated_during_the_indexation(): void
    {
        $this->clientMigration->method('aliasExist')->with(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(true);

        $getIndexNameCallCount = 0;
        $this->clientMigration->method('getIndexNameFromAlias')->willReturnCallback(
            function () use (&$getIndexNameCallCount) {
                $getIndexNameCallCount++;
                return $getIndexNameCallCount === 1
                    ? [self::INDEX_NAME_TO_MIGRATE]
                    : [self::MIGRATED_INDEX_NAME];
            }
        );

        $this->clientMigration->expects($this->once())->method('createIndex');

        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSecondIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:25-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:28-0700');

        $clockCallCount = 0;
        $clockValues = [$firstDatetime, $datetimeAfterFirstIndexation, $datetimeAfterSecondIndexation, $datetimeAfterSwitch];
        $this->clock->method('now')->willReturnCallback(function () use (&$clockCallCount, $clockValues) {
            return $clockValues[$clockCallCount++] ?? end($clockValues);
        });

        $reindexCallCount = 0;
        $this->clientMigration->method('reindex')->willReturnCallback(
            function () use (&$reindexCallCount) {
                $reindexCallCount++;
                if ($reindexCallCount === 1) {
                    return 10;
                }
                if ($reindexCallCount === 2) {
                    return 2;
                }
                if ($reindexCallCount === 3) {
                    return 0;
                }
                return 0; // after switch
            }
        );

        $this->clientMigration->expects($this->once())->method('putIndexSetting');
        $this->clientMigration->expects($this->once())->method('refreshIndex');
        $this->clientMigration->expects($this->once())->method('switchIndexAlias');
        $this->clientMigration->expects($this->once())->method('removeIndex')->with(self::INDEX_NAME_TO_MIGRATE);

        $this->sut->execute(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME,
            $this->indexConfiguration,
            fn (\DateTimeImmutable $referenceDatetime) => [
                'range' => [
                    'updated_at' => ['gt' => $referenceDatetime->getTimestamp()],
                ],
            ]
        );
    }

    public function test_it_reindex_records_updated_between_indexation_and_swap(): void
    {
        $this->clientMigration->method('aliasExist')->with(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(true);

        $getIndexNameCallCount = 0;
        $this->clientMigration->method('getIndexNameFromAlias')->willReturnCallback(
            function () use (&$getIndexNameCallCount) {
                $getIndexNameCallCount++;
                return $getIndexNameCallCount === 1
                    ? [self::INDEX_NAME_TO_MIGRATE]
                    : [self::MIGRATED_INDEX_NAME];
            }
        );

        $this->clientMigration->expects($this->once())->method('createIndex');

        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:28-0700');

        $clockCallCount = 0;
        $clockValues = [$firstDatetime, $datetimeAfterFirstIndexation, $datetimeAfterSwitch];
        $this->clock->method('now')->willReturnCallback(function () use (&$clockCallCount, $clockValues) {
            return $clockValues[$clockCallCount++] ?? end($clockValues);
        });

        $reindexCallCount = 0;
        $this->clientMigration->method('reindex')->willReturnCallback(
            function () use (&$reindexCallCount) {
                $reindexCallCount++;
                if ($reindexCallCount === 1) {
                    return 10;
                }
                if ($reindexCallCount === 2) {
                    return 0;
                }
                return 1; // after switch
            }
        );

        $this->clientMigration->expects($this->once())->method('putIndexSetting');
        $this->clientMigration->expects($this->once())->method('refreshIndex');
        $this->clientMigration->expects($this->once())->method('switchIndexAlias');
        $this->clientMigration->expects($this->once())->method('removeIndex')->with(self::INDEX_NAME_TO_MIGRATE);

        $this->sut->execute(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME,
            $this->indexConfiguration,
            fn (\DateTimeImmutable $referenceDatetime) => [
                'range' => [
                    'updated_at' => ['gt' => $referenceDatetime->getTimestamp()],
                ],
            ]
        );
    }

    public function test_it_does_not_remove_index_while_swap_is_not_done(): void
    {
        $this->clientMigration->method('aliasExist')->with(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(true);

        $getIndexNameCallCount = 0;
        $this->clientMigration->method('getIndexNameFromAlias')->willReturnCallback(
            function () use (&$getIndexNameCallCount) {
                $getIndexNameCallCount++;
                return $getIndexNameCallCount === 1
                    ? [self::INDEX_NAME_TO_MIGRATE]
                    : [self::MIGRATED_INDEX_NAME];
            }
        );

        $this->clientMigration->expects($this->once())->method('createIndex');

        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:28-0700');

        $clockCallCount = 0;
        $clockValues = [$firstDatetime, $datetimeAfterFirstIndexation, $datetimeAfterSwitch];
        $this->clock->method('now')->willReturnCallback(function () use (&$clockCallCount, $clockValues) {
            return $clockValues[$clockCallCount++] ?? end($clockValues);
        });

        $reindexCallCount = 0;
        $this->clientMigration->method('reindex')->willReturnCallback(
            function () use (&$reindexCallCount) {
                $reindexCallCount++;
                if ($reindexCallCount === 1) {
                    return 10;
                }
                return 0;
            }
        );

        $this->clientMigration->expects($this->once())->method('putIndexSetting');
        $this->clientMigration->expects($this->once())->method('refreshIndex');
        $this->clientMigration->expects($this->once())->method('switchIndexAlias')->willThrowException(new \InvalidArgumentException());
        $this->clientMigration->expects($this->never())->method('removeIndex');

        $this->expectException(\InvalidArgumentException::class);
        $this->sut->execute(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME,
            $this->indexConfiguration,
            fn (\DateTimeImmutable $referenceDatetime) => [
                'range' => [
                    'updated_at' => ['gt' => $referenceDatetime->getTimestamp()],
                ],
            ],
        );
    }
}
