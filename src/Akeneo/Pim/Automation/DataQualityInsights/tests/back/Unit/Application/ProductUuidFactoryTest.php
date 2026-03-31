<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductUuidFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

class ProductUuidFactoryTest extends TestCase
{
    private ProductUuidFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductUuidFactory();
    }

    public function test_it_creates_a_product_uuid(): void
    {
        $this->assertEquals(ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed'), $this->sut->create('df470d52-7723-4890-85a0-e79be625e2ed'));
    }

    public function test_it_creates_a_collection_of_product_uuids(): void
    {
        $collection = $this->sut->createCollection([
                    'df470d52-7723-4890-85a0-e79be625e2ed',
                    '6d125b99-d971-41d9-a264-b020cd486aee',
                ]);
        $items = $collection->toArray();
        $this->assertContainsOnlyInstancesOf(ProductUuid::class, $items);
        $this->assertSame('df470d52-7723-4890-85a0-e79be625e2ed', (string) $items[0]);
        $this->assertSame('6d125b99-d971-41d9-a264-b020cd486aee', (string) $items[1]);
    }

    public function test_it_throws_exception_when_invalid_uuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->create('6d125b99');
    }

    public function test_it_throws_exception_when_invalid_list_of_uuids(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createCollection(['6d125b99']);
    }
}
