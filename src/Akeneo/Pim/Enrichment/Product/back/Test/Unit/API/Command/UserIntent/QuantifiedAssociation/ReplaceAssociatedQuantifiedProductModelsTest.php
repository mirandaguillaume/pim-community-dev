<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReplaceAssociatedQuantifiedProductModelsTest extends TestCase
{
    private ReplaceAssociatedQuantifiedProductModels $sut;

    protected function setUp(): void
    {
        $this->sut = new ReplaceAssociatedQuantifiedProductModels('X_SELL', [new QuantifiedEntity('foo', 5)]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ReplaceAssociatedQuantifiedProductModels::class, $this->sut);
        $this->assertInstanceOf(QuantifiedAssociationUserIntent::class, $this->sut);
        $this->assertInstanceOf(UserIntent::class, $this->sut);
    }

    public function test_it_cannot_be_constructed_with_empty_association_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ReplaceAssociatedQuantifiedProductModels('', [new QuantifiedEntity('foo', 5)]);
    }

    public function test_it_cannot_be_constructed_with_non_valid_quantified_product_models(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ReplaceAssociatedQuantifiedProductModels('X_SELL', [new \stdClass()]);
    }

    public function test_it_returns_the_association_type(): void
    {
        $this->assertSame('X_SELL', $this->sut->associationType());
    }

    public function test_it_returns_the_quantified_product_models(): void
    {
        $this->assertEquals([new QuantifiedEntity('foo', 5)], $this->sut->quantifiedProductModels());
    }

    public function test_it_can_be_constructed_with_empty_quantified_product_models(): void
    {
        $this->sut = new ReplaceAssociatedQuantifiedProductModels('X_SELL', []);
        $this->assertSame([], $this->sut->quantifiedProductModels());
    }
}
