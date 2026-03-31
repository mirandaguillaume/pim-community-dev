<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Cache;

use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueriesClearer;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CachedQueriesClearerTest extends TestCase
{
    private CachedQueriesClearer $sut;

    protected function setUp(): void
    {
    }

    public function test_it_clear_all_cached_queries(): void
    {
        $cachedQuery1 = $this->createMock(CachedQueryInterface::class);
        $cachedQuery2 = $this->createMock(CachedQueryInterface::class);

        $this->sut = new CachedQueriesClearer([
                    $cachedQuery1,
                    $cachedQuery2,
                ]);
        $cachedQuery1->expects($this->once())->method('clearCache');
        $cachedQuery2->expects($this->once())->method('clearCache');
        $this->sut->clear();
    }

    public function test_it_throws_an_exception_when_query_is_not_a_cached_query(): void
    {
        $cachedQuery1 = $this->createMock(CachedQueryInterface::class);
        $LRUCache = $this->createMock(\stdClass::class);

        $this->expectException(\InvalidArgumentException::class);
        new CachedQueriesClearer([$cachedQuery1, $LRUCache]);
    }
}
