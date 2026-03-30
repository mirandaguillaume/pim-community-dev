<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\Model;

use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use PHPUnit\Framework\TestCase;

class ProductIdentifierTest extends TestCase
{
    private ProductIdentifier $sut;

    protected function setUp(): void
    {
    }

    public function test_it_cannot_be_constructed_with_an_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ProductIdentifier::fromString('');
    }

    public function test_it_can_be_constructed_from_a_string(): void
    {
        $this->sut = ProductIdentifier::fromString('foo');
        $this->assertTrue(is_a(ProductIdentifier::class, ProductIdentifier::class, true));
        $this->assertSame('foo', $this->sut->asString());
    }
}
