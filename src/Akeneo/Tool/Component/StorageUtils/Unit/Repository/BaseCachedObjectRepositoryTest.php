<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Repository;

use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\StorageUtils\Repository\BaseCachedObjectRepository;

class BaseCachedObjectRepositoryTest extends TestCase
{
    private IdentifiableObjectRepositoryInterface|MockObject $repository;
    private BaseCachedObjectRepository $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->sut = new BaseCachedObjectRepository($this->repository);
    }

    public function test_it_is_a_cache_clearer(): void
    {
        $this->assertInstanceOf(EntityManagerClearerInterface::class, $this->sut);
    }

    public function test_it_is_an_identifiable_object_repository(): void
    {
        $this->assertInstanceOf(IdentifiableObjectRepositoryInterface::class, $this->sut);
    }

    public function test_it_cached_objects(): void
    {
        $object1 = new \stdClass();
        $object2 = new \stdClass();
        $this->repository->expects($this->exactly(1))->method('findOneByIdentifier')->with('objectidentifier1')->willReturn($object1);
        $this->repository->expects($this->exactly(1))->method('findOneByIdentifier')->with('objectidentifier2')->willReturn($object2);
        $this->assertSame($object1, $this->sut->findOneByIdentifier('objectidentifier1'));
        $this->assertSame($object1, $this->sut->findOneByIdentifier('objectidentifier1'));
        $this->assertSame($object1, $this->sut->findOneByIdentifier('objectidentifier1'));
        $this->assertSame($object2, $this->sut->findOneByIdentifier('objectidentifier2'));
    }

    public function test_it_clears_internal_cache(): void
    {
        $object1 = new \stdClass();
        $this->repository->expects($this->exactly(2))->method('findOneByIdentifier')->with('objectidentifier1')->willReturn($object1);
        $this->assertSame($object1, $this->sut->findOneByIdentifier('objectidentifier1'));
        $this->sut->clear();
        $this->assertSame($object1, $this->sut->findOneByIdentifier('objectidentifier1'));
    }

    public function test_it_returns_null_on_non_existing_object(): void
    {
        $this->repository->method('findOneByIdentifier')->with('objectidentifier1')->willReturn(null);
        $this->assertNull($this->sut->findOneByIdentifier('objectidentifier1'));
    }
}
