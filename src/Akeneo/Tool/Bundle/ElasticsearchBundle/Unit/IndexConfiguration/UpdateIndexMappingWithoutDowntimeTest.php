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
            ['elasticsearch_host']
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
        $this->clientMigration->method('getIndexSettings')->with(self::INDEX_NAME_TO_MIGRATE)->willReturn([
        'refresh_interval' => 5,
        'number_of_replicas' => 2,
        ]);
    }

    public function test_it_reindex_all_records_on_the_given_index(): void
    {
        $this->clientMigration->method('aliasExist')->with(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(true);
        $this->clientMigration->method('getIndexNameFromAlias')->with(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(
            [self::INDEX_NAME_TO_MIGRATE],
            [self::MIGRATED_INDEX_NAME]
        );
        $this->clientMigration->expects($this->once())->method('createIndex')->with(self::MIGRATED_INDEX_NAME, [
                    'settings' => [
                        'index' => [
                            'number_of_shards' => 3,
                            'number_of_replicas' => 0,
                            'refresh_interval' => -1,
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
                    'aliases' => [self::TEMPORARY_INDEX_ALIAS => new \stdClass()],
                ]);
        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:25-0700');
        $this->clock->method('now')->willReturn($firstDatetime, $datetimeAfterFirstIndexation, $datetimeAfterSwitch);
        $this->clientMigration->expects($this->once())->method('reindex')->with(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                        'range' => [
                            'updated_at' => ['gt' => $firstDatetime->getTimestamp()],
                        ],
                    ]
        )->willReturn(10);
        $this->clientMigration->expects($this->once())->method('reindex')->with(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterFirstIndexation->modify('- 1second')->getTimestamp()],
                        ],
                    ]
        )->willReturn(0);
        $this->clientMigration->expects($this->exactly(1))->method('putIndexSetting')->with(self::MIGRATED_INDEX_NAME, ['refresh_interval' => 5, 'number_of_replicas' => 2]);
        $this->clientMigration->expects($this->once())->method('refreshIndex')->with(self::MIGRATED_INDEX_NAME);
        $this->clientMigration->expects($this->once())->method('switchIndexAlias')->with(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::INDEX_NAME_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME
        );
        $this->clientMigration->expects($this->once())->method('reindex')->with(
            self::TEMPORARY_INDEX_ALIAS,
            self::INDEX_ALIAS_TO_MIGRATE,
            [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterSwitch->modify('- 1second')->getTimestamp()],
                        ],
                    ]
        )->willReturn(0);
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
        $this->clientMigration->method('createAlias')->with('index_alias_to_migrate_migration_alias', self::INDEX_ALIAS_TO_MIGRATE);
        $this->clientMigration->method('getIndexNameFromAlias')->with('index_alias_to_migrate_migration_alias')->willReturn(
            [self::INDEX_ALIAS_TO_MIGRATE],
            [self::MIGRATED_INDEX_NAME]
        );
        $this->clientMigration->method('getIndexSettings')->with(self::INDEX_ALIAS_TO_MIGRATE)->willReturn([
                    'refresh_interval' => 5,
                    'number_of_replicas' => 2,
                ]);
        $this->clientMigration->expects($this->once())->method('createIndex')->with(self::MIGRATED_INDEX_NAME, [
                    'settings' => [
                        'index' => [
                            'number_of_shards' => 3,
                            'number_of_replicas' => 0,
                            'refresh_interval' => -1,
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
                    'aliases' => [self::TEMPORARY_INDEX_ALIAS => new \stdClass()],
                ]);
        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:25-0700');
        $this->clock->method('now')->willReturn($firstDatetime, $datetimeAfterFirstIndexation, $datetimeAfterSwitch);
        $this->clientMigration->expects($this->once())->method('reindex')->with(
            'index_alias_to_migrate_migration_alias',
            self::TEMPORARY_INDEX_ALIAS,
            [
                        'range' => [
                            'updated_at' => ['gt' => $firstDatetime->getTimestamp()],
                        ],
                    ]
        )->willReturn(10);
        $this->clientMigration->expects($this->once())->method('reindex')->with(
            'index_alias_to_migrate_migration_alias',
            self::TEMPORARY_INDEX_ALIAS,
            [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterFirstIndexation->modify('- 1second')->getTimestamp()],
                        ],
                    ]
        )->willReturn(0);
        $this->clientMigration->expects($this->exactly(1))->method('putIndexSetting')->with(self::MIGRATED_INDEX_NAME, ['refresh_interval' => 5, 'number_of_replicas' => 2]);
        $this->clientMigration->expects($this->once())->method('refreshIndex')->with(self::MIGRATED_INDEX_NAME);
        $this->clientMigration->expects($this->once())->method('switchIndexAlias')->with(
            'index_alias_to_migrate_migration_alias',
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME
        );
        $this->clientMigration->expects($this->once())->method('reindex')->with(
            self::TEMPORARY_INDEX_ALIAS,
            'index_alias_to_migrate_migration_alias',
            [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterSwitch->modify('- 1second')->getTimestamp()],
                        ],
                    ]
        )->willReturn(0);
        $this->clientMigration->expects($this->once())->method('removeIndex')->with(self::INDEX_ALIAS_TO_MIGRATE);
        $this->clientMigration->expects($this->once())->method('renameAlias')->with('index_alias_to_migrate_migration_alias', self::INDEX_ALIAS_TO_MIGRATE, self::MIGRATED_INDEX_NAME);
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
        $this->clientMigration->method('getIndexNameFromAlias')->with(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(
            [self::INDEX_NAME_TO_MIGRATE],
            [self::MIGRATED_INDEX_NAME]
        );
        $this->clientMigration->expects($this->once())->method('createIndex')->with(self::MIGRATED_INDEX_NAME, [
                    'settings' => [
                        'index' => [
                            'number_of_shards' => 3,
                            'number_of_replicas' => 0,
                            'refresh_interval' => -1,
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
                    'aliases' => [self::TEMPORARY_INDEX_ALIAS => new \stdClass()],
                ]);
        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSecondIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:25-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:28-0700');
        $this->clock->method('now')->willReturn(
            $firstDatetime,
            $datetimeAfterFirstIndexation,
            $datetimeAfterSecondIndexation,
            $datetimeAfterSwitch
        );
        $this->clientMigration->expects($this->once())->method('reindex')->with(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                        'range' => [
                            'updated_at' => ['gt' => $firstDatetime->getTimestamp()],
                        ],
                    ]
        )->willReturn(10);
        $this->clientMigration->expects($this->once())->method('reindex')->with(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterFirstIndexation->modify('- 1second')->getTimestamp()],
                        ],
                    ]
        )->willReturn(2);
        $this->clientMigration->expects($this->once())->method('reindex')->with(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterSecondIndexation->modify('- 1second')->getTimestamp()],
                        ],
                    ]
        )->willReturn(0);
        $this->clientMigration->expects($this->exactly(1))->method('putIndexSetting')->with(self::MIGRATED_INDEX_NAME, ['refresh_interval' => 5, 'number_of_replicas' => 2]);
        $this->clientMigration->expects($this->once())->method('refreshIndex')->with(self::MIGRATED_INDEX_NAME);
        $this->clientMigration->expects($this->once())->method('switchIndexAlias')->with(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::INDEX_NAME_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME
        );
        $this->clientMigration->expects($this->once())->method('reindex')->with(
            self::TEMPORARY_INDEX_ALIAS,
            self::INDEX_ALIAS_TO_MIGRATE,
            [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterSwitch->modify('- 1second')->getTimestamp()],
                        ],
                    ]
        )->willReturn(0);
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
        $this->clientMigration->method('getIndexNameFromAlias')->with(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(
            [self::INDEX_NAME_TO_MIGRATE],
            [self::MIGRATED_INDEX_NAME]
        );
        $this->clientMigration->expects($this->once())->method('createIndex')->with(self::MIGRATED_INDEX_NAME, [
                    'settings' => [
                        'index' => [
                            'number_of_shards' => 3,
                            'number_of_replicas' => 0,
                            'refresh_interval' => -1,
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
                    'aliases' => [self::TEMPORARY_INDEX_ALIAS => new \stdClass()],
                ]);
        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:28-0700');
        $this->clock->method('now')->willReturn(
            $firstDatetime,
            $datetimeAfterFirstIndexation,
            $datetimeAfterSwitch
        );
        $this->clientMigration->expects($this->once())->method('reindex')->with(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                        'range' => [
                            'updated_at' => ['gt' => $firstDatetime->getTimestamp()],
                        ],
                    ]
        )->willReturn(10);
        $this->clientMigration->expects($this->once())->method('reindex')->with(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterFirstIndexation->modify('- 1second')->getTimestamp()],
                        ],
                    ]
        )->willReturn(0);
        $this->clientMigration->expects($this->exactly(1))->method('putIndexSetting')->with(self::MIGRATED_INDEX_NAME, ['refresh_interval' => 5, 'number_of_replicas' => 2]);
        $this->clientMigration->expects($this->once())->method('refreshIndex')->with(self::MIGRATED_INDEX_NAME);
        $this->clientMigration->expects($this->once())->method('switchIndexAlias')->with(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::INDEX_NAME_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME
        );
        $this->clientMigration->expects($this->once())->method('reindex')->with(
            self::TEMPORARY_INDEX_ALIAS,
            self::INDEX_ALIAS_TO_MIGRATE,
            [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterSwitch->modify('- 1second')->getTimestamp()],
                        ],
                    ]
        )->willReturn(1);
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
        $this->clientMigration->method('getIndexNameFromAlias')->with(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(
            [self::INDEX_NAME_TO_MIGRATE],
            [self::MIGRATED_INDEX_NAME]
        );
        $this->clientMigration->expects($this->once())->method('createIndex')->with(self::MIGRATED_INDEX_NAME, [
                    'settings' => [
                        'index' => [
                            'number_of_shards' => 3,
                            'number_of_replicas' => 0,
                            'refresh_interval' => -1,
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
                    'aliases' => [self::TEMPORARY_INDEX_ALIAS => new \stdClass()],
                ]);
        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:28-0700');
        $this->clock->method('now')->willReturn(
            $firstDatetime,
            $datetimeAfterFirstIndexation,
            $datetimeAfterSwitch
        );
        $this->clientMigration->expects($this->once())->method('reindex')->with(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                        'range' => [
                            'updated_at' => ['gt' => $firstDatetime->getTimestamp()],
                        ],
                    ]
        )->willReturn(10);
        $this->clientMigration->expects($this->once())->method('reindex')->with(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterFirstIndexation->modify('- 1second')->getTimestamp()],
                        ],
                    ]
        )->willReturn(0);
        $this->clientMigration->expects($this->exactly(1))->method('putIndexSetting')->with(self::MIGRATED_INDEX_NAME, ['refresh_interval' => 5, 'number_of_replicas' => 2]);
        $this->clientMigration->expects($this->once())->method('refreshIndex')->with(self::MIGRATED_INDEX_NAME);
        $this->clientMigration->expects($this->once())->method('switchIndexAlias')->with(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::INDEX_NAME_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME
        )->willThrowException(\InvalidArgumentException::class);
        $this->clientMigration->expects($this->never())->method('removeIndex')->with(self::INDEX_NAME_TO_MIGRATE);
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
