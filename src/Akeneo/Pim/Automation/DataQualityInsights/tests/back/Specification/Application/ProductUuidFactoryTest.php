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
        $this->assertEquals(ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')), $this->sut->create(Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed')));
    }

    public function test_it_creates_a_collection_of_product_uuids(): void
    {
        $collectionBehavior = $this->createCollection([
                    'df470d52-7723-4890-85a0-e79be625e2ed',
                    '6d125b99-d971-41d9-a264-b020cd486aee',
                ]);
        $collection = $collectionBehavior;
        Assert::allIsInstanceOf($collection, ProductUuid::class);
        Assert::same((string) $collection->toArray()[0], 'df470d52-7723-4890-85a0-e79be625e2ed');
        Assert::same((string) $collection->toArray()[1], '6d125b99-d971-41d9-a264-b020cd486aee');
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
