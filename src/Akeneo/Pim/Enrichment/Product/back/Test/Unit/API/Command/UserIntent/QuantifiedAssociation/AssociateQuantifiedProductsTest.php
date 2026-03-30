<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use PHPUnit\Framework\TestCase;

class AssociateQuantifiedProductsTest extends TestCase
{
    private AssociateQuantifiedProducts $sut;

    protected function setUp(): void
    {
        $this->sut = new AssociateQuantifiedProducts('X_SELL', [new QuantifiedEntity('foo', 5)]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AssociateQuantifiedProducts::class, $this->sut);
        $this->assertInstanceOf(QuantifiedAssociationUserIntent::class, $this->sut);
        $this->assertInstanceOf(UserIntent::class, $this->sut);
    }

    public function test_it_cannot_be_constructed_with_empty_association_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AssociateQuantifiedProducts('', [new QuantifiedEntity('foo', 5)]);
    }

    public function test_it_cannot_be_constructed_with_empty_quantified_products(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AssociateQuantifiedProducts('X_SELL', []);
    }

    public function test_it_cannot_be_constructed_with_non_valid_quantified_products(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AssociateQuantifiedProducts('X_SELL', [new \stdClass()]);
    }

    public function test_it_returns_the_association_type(): void
    {
        $this->assertSame('X_SELL', $this->sut->associationType());
    }

    public function test_it_returns_the_quantified_products(): void
    {
        $this->assertEquals([new QuantifiedEntity('foo', 5)], $this->sut->quantifiedProducts());
    }
}
