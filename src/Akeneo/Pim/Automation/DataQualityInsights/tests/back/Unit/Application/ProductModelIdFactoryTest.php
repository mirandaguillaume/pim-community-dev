<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductModelIdFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class ProductModelIdFactoryTest extends TestCase
{
    private ProductModelIdFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductModelIdFactory();
    }

    public function test_it_creates_a_product_model_id(): void
    {
        $this->assertEquals(new ProductModelId(1234), $this->sut->create('1234'));
    }

    public function test_it_creates_a_collection_of_product_model_id(): void
    {
        $collection = $this->sut->createCollection(['1234', '4321']);
        Assert::allIsInstanceOf($collection, ProductModelId::class);
        Assert::same((string) $collection->toArray()[0], '1234');
        Assert::same((string) $collection->toArray()[1], '4321');
    }

    public function test_it_throws_exception_when_invalid_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->create('abcd');
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->create('-1234');
    }

    public function test_it_throws_exception_when_invalid_list_of_ids(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createCollection(['abcd']);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createCollection(['-1234']);
    }
}
