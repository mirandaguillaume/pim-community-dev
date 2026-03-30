<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntent;
use PHPUnit\Framework\TestCase;

class DissociateQuantifiedProductsTest extends TestCase
{
    private DissociateQuantifiedProducts $sut;

    protected function setUp(): void
    {
        $this->sut = new DissociateQuantifiedProducts('X_SELL', ['identifier1', 'identifier2']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(DissociateQuantifiedProducts::class, $this->sut);
        $this->assertInstanceOf(QuantifiedAssociationUserIntent::class, $this->sut);
    }

    public function test_it_returns_the_association_type(): void
    {
        $this->assertSame('X_SELL', $this->sut->associationType());
    }

    public function test_it_returns_the_product_identifiers(): void
    {
        $this->assertSame(['identifier1', 'identifier2'], $this->sut->productIdentifiers());
    }

    public function test_it_can_only_be_instantiated_with_string_product_identifiers(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DissociateQuantifiedProducts('X_SELL', ['test', 12, false]);
    }

    public function test_it_cannot_be_instantiated_with_empty_product_identifiers(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DissociateQuantifiedProducts('X_SELL', []);
    }

    public function test_it_cannot_be_instantiated_if_one_of_the_product_identifiers_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DissociateQuantifiedProducts('X_SELL', ['a', '', 'b']);
    }

    public function test_it_cannot_be_instantiated_with_empty_association_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DissociateQuantifiedProducts('', ['identifier1', 'identifier2']);
    }
}
