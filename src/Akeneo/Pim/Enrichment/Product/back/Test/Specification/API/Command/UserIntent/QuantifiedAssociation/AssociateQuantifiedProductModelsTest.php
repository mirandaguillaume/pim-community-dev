<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use PHPUnit\Framework\TestCase;

class AssociateQuantifiedProductModelsTest extends TestCase
{
    private AssociateQuantifiedProductModels $sut;

    protected function setUp(): void
    {
        $this->sut = new AssociateQuantifiedProductModels('X_SELL', [new QuantifiedEntity('foo', 5)]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AssociateQuantifiedProductModels::class, $this->sut);
        $this->assertInstanceOf(QuantifiedAssociationUserIntent::class, $this->sut);
        $this->assertInstanceOf(UserIntent::class, $this->sut);
    }

    public function test_it_cannot_be_constructed_with_empty_association_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AssociateQuantifiedProductModels('', [new QuantifiedEntity('foo', 5)]);
    }

    public function test_it_cannot_be_constructed_with_empty_quantified_product_models(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AssociateQuantifiedProductModels('X_SELL', []);
    }

    public function test_it_cannot_be_constructed_with_non_valid_quantified_entities(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AssociateQuantifiedProductModels('X_SELL', [new \stdClass()]);
    }

    public function test_it_returns_the_association_type(): void
    {
        $this->assertSame('X_SELL', $this->sut->associationType());
    }

    public function test_it_returns_the_quantified_product_models(): void
    {
        $this->assertEquals([new QuantifiedEntity('foo', 5)], $this->sut->quantifiedProductModels());
    }
}
