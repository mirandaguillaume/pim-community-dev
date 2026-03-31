<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use PHPUnit\Framework\TestCase;

class QuantifiedEntityTest extends TestCase
{
    private QuantifiedEntity $sut;

    protected function setUp(): void
    {
        $this->sut = new QuantifiedEntity('foo', 5);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(QuantifiedEntity::class, $this->sut);
    }

    public function test_it_cannot_be_constructed_with_empty_product_identifier(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new QuantifiedEntity('', 5);
    }

    public function test_it_returns_the_entity_identifier(): void
    {
        $this->assertSame('foo', $this->sut->entityIdentifier());
    }

    public function test_it_returns_the_quantity(): void
    {
        $this->assertSame(5, $this->sut->quantity());
    }
}
