<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\CacheClearer;
use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Cache\LRUCachedGetAttributes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueriesClearerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CacheClearerTest extends TestCase
{
    private UnitOfWorkAndRepositoriesClearer|MockObject $unitOfWorkAndRepositoriesClearer;
    private CachedQueriesClearerInterface|MockObject $cachedQueriesClearer;
    private CacheClearer $sut;

    protected function setUp(): void
    {
        $this->unitOfWorkAndRepositoriesClearer = $this->createMock(UnitOfWorkAndRepositoriesClearer::class);
        $this->cachedQueriesClearer = $this->createMock(CachedQueriesClearerInterface::class);
        $this->sut = new CacheClearer($this->unitOfWorkAndRepositoriesClearer, $this->cachedQueriesClearer);
    }

    public function test_it_is_a_cache_clearer(): void
    {
        $this->assertInstanceOf(CacheClearer::class, $this->sut);
        $this->assertInstanceOf(CacheClearerInterface::class, $this->sut);
    }

    public function test_it_clears_the_cache(): void
    {
        $this->unitOfWorkAndRepositoriesClearer->expects($this->once())->method('clear');
        $this->cachedQueriesClearer->expects($this->once())->method('clear');
        $this->sut->clear();
    }
}
