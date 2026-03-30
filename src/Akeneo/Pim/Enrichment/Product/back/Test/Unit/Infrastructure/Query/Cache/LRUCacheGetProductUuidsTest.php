<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Infrastructure\Query\Cache;

use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuids;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Query\Cache\LRUCacheGetProductUuids;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class LRUCacheGetProductUuidsTest extends TestCase
{
    private GetProductUuids|MockObject $getProductUuids;
    private LRUCacheGetProductUuids $sut;

    protected function setUp(): void
    {
        $this->getProductUuids = $this->createMock(GetProductUuids::class);
        $this->sut = new LRUCacheGetProductUuids($this->getProductUuids);
    }

    public function test_it_returns_a_single_uuid(): void
    {
        $uuid = Uuid::uuid4();
        $this->getProductUuids->method('fromIdentifier')->with('product')->willReturn($uuid);
        $this->assertSame($uuid, $this->sut->fromIdentifier('product'));
    }

    public function test_it_uses_lru_cache_for_a_single_identifier(): void
    {
        $uuid = Uuid::uuid4();
        $this->getProductUuids->expects($this->once())->method('fromIdentifier')->with('product')->willReturn($uuid);
        $this->sut->fromIdentifier('product');
        $this->sut->fromIdentifier('product');
    }

    public function test_it_returns_multiple_uuids(): void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $this->getProductUuids->method('fromIdentifiers')->with(['product1', 'product2'])->willReturn([
                    'product1' => $uuid1,
                    'product2' => $uuid2,
                ]);
        $this->assertSame([
                    'product1' => $uuid1,
                    'product2' => $uuid2,
                ], $this->sut->fromIdentifiers(['product1', 'product2']));
    }

    public function test_it_uses_lru_cache_for_multiple_uuids(): void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $expectedResult = [
                    'product1' => $uuid1,
                    'product2' => $uuid2,
                ];
        $this->getProductUuids->expects($this->once())->method('fromIdentifiers')->with(['product1', 'product2'])->willReturn($expectedResult);
        $this->sut->fromIdentifiers(['product1', 'product2']);
        $this->sut->fromIdentifiers(['product1', 'product2']);
    }

    public function test_it_uses_lru_cache_for_multiple_and_simple_uuids(): void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $expectedResult = [
                    'product1' => $uuid1,
                    'product2' => $uuid2,
                ];
        $this->getProductUuids->expects($this->once())->method('fromIdentifiers')->with(['product1', 'product2'])->willReturn($expectedResult);
        $this->getProductUuids->expects($this->never())->method('fromIdentifier')->with($this->anything());
        $this->sut->fromIdentifiers(['product1', 'product2']);
        $this->sut->fromIdentifier('product1');
        $this->sut->fromIdentifier('product2');
        $this->sut->fromIdentifiers(['product2', 'product1']);
    }

    public function test_it_returns_null_when_product_does_not_exist(): void
    {
        $this->getProductUuids->method('fromIdentifier')->with('non_existing_product')->willReturn(null);
        $this->assertNull($this->sut->fromIdentifier('non_existing_product'));
    }

    public function test_it_returns_null_when_products_do_not_exist(): void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $expectedResult = [
                    'product1' => $uuid1,
                    'product2' => $uuid2,
                ];
        $this->getProductUuids->method('fromIdentifiers')->with(['product1', 'non_existing_product', 'product2'])->willReturn($expectedResult);
        $this->assertSame($expectedResult, $this->sut->fromIdentifiers(['product1', 'non_existing_product', 'product2']));
    }
}
