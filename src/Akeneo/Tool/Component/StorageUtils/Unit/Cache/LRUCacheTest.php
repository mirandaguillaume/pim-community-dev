<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Cache;

use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

interface EntityObjectQuery
{
    public function fromCode(string $code): ?EntityObject;
    public function fromCodes(array $codes): array;
}

class EntityObject
{
    public function __construct(private readonly string $code)
    {
    }
    public function getCode(): string
    {
        return $this->code;
    }
}

class LRUCacheTest extends TestCase
{
    private LRUCache $sut;

    protected function setUp(): void
    {
        $this->sut = new LRUCache(2);
    }

    public function test_it_cannot_be_instantiated_with_zero_or_negative_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new LRUCache(0);
    }

    public function test_it_gets_result_for_single_key_by_calling_the_callable(): void
    {
        $entityObjectQuery = $this->createMock(EntityObjectQuery::class);

        $entityObjectQuery->method('fromCode')->with('entity_code_3')->willReturn(new EntityObject('entity_code_3'));
        $this->assertEquals(new EntityObject('entity_code_3'), $this->sut->getForKey('entity_code_3', $this->queryToFetchEntityFromCode($entityObjectQuery)));
    }

    public function test_it_gets_result_from_single_key_from_the_cache_and_does_not_call_the_callable_query(): void
    {
        $entityObjectQuery = $this->createMock(EntityObjectQuery::class);

        $entityObjectQuery->expects($this->once())->method('fromCode')->with('entity_code_1')->willReturn(new EntityObject('entity_code_1'));
        $this->assertEquals(new EntityObject('entity_code_1'), $this->sut->getForKey('entity_code_1', $this->queryToFetchEntityFromCode($entityObjectQuery)));
        $this->assertEquals(new EntityObject('entity_code_1'), $this->sut->getForKey('entity_code_1', $this->queryToFetchEntityFromCode($entityObjectQuery)));
    }

    public function test_it_removes_the_least_recently_used_element_when_maximum_size_is_reached(): void
    {
        $entityObjectQuery = $this->createMock(EntityObjectQuery::class);

        $entityObjectQuery->method('fromCode')->willReturnCallback(function (string $code) {
            return new EntityObject($code);
        });
        $this->assertEquals(new EntityObject('entity_code_1'), $this->sut->getForKey('entity_code_1', $this->queryToFetchEntityFromCode($entityObjectQuery)));
        $this->assertEquals(new EntityObject('entity_code_2'), $this->sut->getForKey('entity_code_2', $this->queryToFetchEntityFromCode($entityObjectQuery)));
        $this->assertEquals(new EntityObject('entity_code_3'), $this->sut->getForKey('entity_code_3', $this->queryToFetchEntityFromCode($entityObjectQuery)));
        $this->assertEquals(new EntityObject('entity_code_1'), $this->sut->getForKey('entity_code_1', $this->queryToFetchEntityFromCode($entityObjectQuery)));
    }

    public function test_it_store_null_values_and_does_not_call_the_query_if_null_value_is_stored(): void
    {
        $query = fn (string $entityCode) => null;
        $this->assertNull($this->sut->getForKey('entity_code_1', $query));
        $this->assertNull($this->sut->getForKey('entity_code_1', $query));
    }

    public function test_it_gets_multiple_keys_by_calling_the_callable(): void
    {
        $entityObjectQuery = $this->createMock(EntityObjectQuery::class);

        $entityObjectQuery->expects($this->once())->method('fromCodes')->with(['entity_code_1', 'entity_code_2'])->willReturn([
                            new EntityObject('entity_code_1'),
                            new EntityObject('entity_code_2'),
                        ]);
        $this->assertEquals([
                        new EntityObject('entity_code_1'),
                        new EntityObject('entity_code_2'),
                    ], $this->sut->getForKeys(['entity_code_1', 'entity_code_2'], $this->queryToFetchEntitiesFromCodes($entityObjectQuery)));
    }

    public function test_it_does_not_store_all_entries_when_query_result_is_greater_than_cache_size(): void
    {
        $entityObjectQuery = $this->createMock(EntityObjectQuery::class);

        $entityObjectQuery->expects($this->once())->method('fromCodes')->with(['entity_code_1', 'entity_code_2', 'entity_code_3'])->willReturn([
                            'entity_code_1' => new EntityObject('entity_code_1'),
                            'entity_code_2' => new EntityObject('entity_code_2'),
                            'entity_code_3' => new EntityObject('entity_code_3'),
                        ]);
        $entityObjectQuery->method('fromCode')->willReturnCallback(function (string $code) {
            return new EntityObject($code);
        });
        $this->assertEquals([
                        'entity_code_1' => new EntityObject('entity_code_1'),
                        'entity_code_2' => new EntityObject('entity_code_2'),
                        'entity_code_3' => new EntityObject('entity_code_3'),
                    ], $this->sut->getForKeys(['entity_code_1', 'entity_code_2', 'entity_code_3'], $this->queryToFetchEntitiesFromCodes($entityObjectQuery)));
        $this->assertEquals(new EntityObject('entity_code_2'), $this->sut->getForKey('entity_code_2', $this->queryToFetchEntityFromCode($entityObjectQuery)));
        $this->assertEquals(new EntityObject('entity_code_1'), $this->sut->getForKey('entity_code_1', $this->queryToFetchEntityFromCode($entityObjectQuery)));
    }

    public function test_it_call_the_callable_only_on_keys_that_are_not_in_the_cache(): void
    {
        $entityObjectQuery = $this->createMock(EntityObjectQuery::class);

        $entityObjectQuery->expects($this->once())->method('fromCode')->with('entity_code_1')->willReturn(new EntityObject('entity_code_1'));
        $this->sut->getForKey('entity_code_1', $this->queryToFetchEntityFromCode($entityObjectQuery));
        $entityObjectQuery->expects($this->once())->method('fromCodes')->with(['entity_code_2'])->willReturn(['entity_code_2' => new EntityObject('entity_code_2')]);
        $this->assertEquals([
                        'entity_code_1' => new EntityObject('entity_code_1'),
                        'entity_code_2' => new EntityObject('entity_code_2'),
                    ], $this->sut->getForKeys(['entity_code_1', 'entity_code_2'], $this->queryToFetchEntitiesFromCodes($entityObjectQuery)));
    }

    public function test_it_handles_string_keys_that_are_numeric(): void
    {
        $entityObjectQuery = $this->createMock(EntityObjectQuery::class);

        $entityObjectQuery->expects($this->once())->method('fromCode')->with('123')->willReturn(new EntityObject('123'));
        $this->sut->getForKey('123', $this->queryToFetchEntityFromCode($entityObjectQuery));
        $entityObjectQuery->expects($this->once())->method('fromCodes')->with(['entity_code_2'])->willReturn(['entity_code_2' => new EntityObject('entity_code_2')]);
        $this->assertEquals([
                        '123' => new EntityObject('123'),
                        'entity_code_2' => new EntityObject('entity_code_2'),
                    ], $this->sut->getForKeys(['123', 'entity_code_2'], $this->queryToFetchEntitiesFromCodes($entityObjectQuery)));
    }

    private function queryToFetchEntityFromCode(EntityObjectQuery $entityObjectQuery)
    {
        return fn (string $entityCode) => $entityObjectQuery->fromCode($entityCode);
    }

    private function queryToFetchEntitiesFromCodes(EntityObjectQuery $entityObjectQuery)
    {
        return fn (array $entityCodes) => $entityObjectQuery->fromCodes($entityCodes);
    }
}
