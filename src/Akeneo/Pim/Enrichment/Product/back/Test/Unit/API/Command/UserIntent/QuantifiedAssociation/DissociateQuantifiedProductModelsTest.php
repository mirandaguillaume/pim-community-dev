<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntent;
use PHPUnit\Framework\TestCase;

class DissociateQuantifiedProductModelsTest extends TestCase
{
    private DissociateQuantifiedProductModels $sut;

    protected function setUp(): void
    {
        $this->sut = new DissociateQuantifiedProductModels('X_SELL', ['identifier1', 'identifier2']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(DissociateQuantifiedProductModels::class, $this->sut);
        $this->assertInstanceOf(QuantifiedAssociationUserIntent::class, $this->sut);
    }

    public function test_it_returns_the_association_type(): void
    {
        $this->assertSame('X_SELL', $this->sut->associationType());
    }

    public function test_it_returns_the_product_model_codes(): void
    {
        $this->assertSame(['identifier1', 'identifier2'], $this->sut->productModelCodes());
    }

    public function test_it_can_only_be_instantiated_with_string_product_model_codes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DissociateQuantifiedProductModels('X_SELL', ['test', 12, false]);
    }

    public function test_it_cannot_be_instantiated_with_empty_product_model_codes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DissociateQuantifiedProductModels('X_SELL', []);
    }

    public function test_it_cannot_be_instantiated_if_one_of_the_product_model_codes_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DissociateQuantifiedProductModels('X_SELL', ['a', '', 'b']);
    }

    public function test_it_cannot_be_instantiated_with_empty_association_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DissociateQuantifiedProductModels('', ['identifier1', 'identifier2']);
    }
}
