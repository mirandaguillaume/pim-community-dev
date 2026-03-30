<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Domain\ValueObject;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use PHPUnit\Framework\TestCase;

class ProductUuidCollectionTest extends TestCase
{
    private ProductUuidCollection $sut;

    protected function setUp(): void
    {
    }

    public function test_it_can_be_construct_from_an_array_of_string(): void
    {
        $ids = [
                    '6d125b99-d971-41d9-a264-b020cd486aee',
                    'fef37e64-a963-47a9-b087-2cc67968f0a2',
                ];
        $this->sut = ProductUuidCollection::fromStrings($ids);
        $this->assertCount(2, $this->sut->toArray());
        $this->assertInstanceOf(ProductUuid::class, $this->sut->toArray()[0]);
        $this->assertInstanceOf(ProductUuid::class, $this->sut->toArray()[1]);
    }

    public function test_it_can_be_construct_from_an_array_of_productUuid(): void
    {
        $ids = [
                    ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
                    ProductUuid::fromString(('fef37e64-a963-47a9-b087-2cc67968f0a2')),
                ];
        $this->sut = ProductUuidCollection::fromProductUuids($ids);
        $this->assertCount(2, $this->sut->toArray());
    }

    public function test_it_throws_an_exception_if_the_product_id_is_not_string_when_using_fromStrings(): void
    {
        $ids = [
                    '6d125b99-d971-41d9-a264-b020cd486aee',
                    12,
                ];
        $this->expectException(\InvalidArgumentException::class);
        ProductUuidCollection::fromStrings($ids);
    }

    public function test_it_throws_an_exception_if_the_product_id_is_not_productUuid_class_when_using_fromProductUuids(): void
    {
        $uuids = [
                    ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
                    57,
                ];
        $this->expectException(\InvalidArgumentException::class);
        ProductUuidCollection::fromProductUuids($uuids);
    }

    public function test_it_instantiates_with_unique_values_with_fromProductUuids(): void
    {
        $uuids = [
                    ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
                    ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
                    ProductUuid::fromString(('6d125b99-d971-41d9-a264-b020cd486aee')),
                    ProductUuid::fromString(('6d125b99-d971-41d9-a264-b020cd486aee')),
                    ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
                ];
        $this->sut = ProductUuidCollection::fromProductUuids($uuids);
        $this->assertEquals([
                    ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
                    ProductUuid::fromString(('6d125b99-d971-41d9-a264-b020cd486aee')),
                ], $this->sut->toArray());
    }

    public function test_it_gets_collection_as_an_array_of_ProductUuid(): void
    {
        $uuids = [
                    ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
                    ProductUuid::fromString(('fef37e64-a963-47a9-b087-2cc67968f0a2')),
                    ProductUuid::fromString(('6d125b99-d971-41d9-a264-b020cd486aee')),
                    ProductUuid::fromString(('b492b9f5-9a8f-495a-8cd7-912c69c31902')),
                ];
        $this->sut = ProductUuidCollection::fromProductUuids($uuids);
        $this->assertIsArray(, $this->sut->toArray());
        $this->assertEquals($uuids, $this->sut->toArray());
    }

    public function test_it_gets_collection_as_an_array_of_string(): void
    {
        $uuids = [
                    ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
                    ProductUuid::fromString(('fef37e64-a963-47a9-b087-2cc67968f0a2')),
                    ProductUuid::fromString(('6d125b99-d971-41d9-a264-b020cd486aee')),
                    ProductUuid::fromString(('b492b9f5-9a8f-495a-8cd7-912c69c31902')),
                ];
        $uuidsExpected = [
                    'df470d52-7723-4890-85a0-e79be625e2ed',
                    'fef37e64-a963-47a9-b087-2cc67968f0a2',
                    '6d125b99-d971-41d9-a264-b020cd486aee',
                    'b492b9f5-9a8f-495a-8cd7-912c69c31902',
                ];
        $this->sut = ProductUuidCollection::fromProductUuids($uuids);
        -$this->toArrayString()->shouldBeArray();
        $this->assertEquals($uuidsExpected, $this->sut->toArrayString());
    }

    public function test_it_counts_product_id_element(): void
    {
        $ids = [
                    '6d125b99-d971-41d9-a264-b020cd486aee',
                    'fef37e64-a963-47a9-b087-2cc67968f0a2',
                ];
        $this->sut = ProductUuidCollection::fromStrings($ids);
        $this->assertCount(2, $this);
    }
}
