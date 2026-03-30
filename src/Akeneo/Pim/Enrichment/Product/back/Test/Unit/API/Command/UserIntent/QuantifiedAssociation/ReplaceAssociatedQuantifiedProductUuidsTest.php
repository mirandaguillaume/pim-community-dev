<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReplaceAssociatedQuantifiedProductUuidsTest extends TestCase
{
    private ReplaceAssociatedQuantifiedProductUuids $sut;

    protected function setUp(): void
    {
        $this->sut = new ReplaceAssociatedQuantifiedProductUuids('PRODUCT_SET', [new QuantifiedEntity('b8f895c5-330a-4d6d-9a74-78db307633bd', 5)]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ReplaceAssociatedQuantifiedProductUuids::class, $this->sut);
        $this->assertInstanceOf(QuantifiedAssociationUserIntent::class, $this->sut);
        $this->assertInstanceOf(UserIntent::class, $this->sut);
    }

    public function test_it_cannot_be_constructed_with_empty_association_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ReplaceAssociatedQuantifiedProductUuids('', [new QuantifiedEntity('b8f895c5-330a-4d6d-9a74-78db307633bd', 5)]);
    }

    public function test_it_cannot_be_constructed_with_non_valid_quantified_products(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ReplaceAssociatedQuantifiedProductUuids('PRODUCT_SET', [new \stdClass()]);
    }

    public function test_it_returns_the_association_type(): void
    {
        $this->assertSame('PRODUCT_SET', $this->sut->associationType());
    }

    public function test_it_returns_the_quantified_products(): void
    {
        $this->assertEquals([new QuantifiedEntity('b8f895c5-330a-4d6d-9a74-78db307633bd', 5)], $this->sut->quantifiedProducts());
    }

    public function test_it_can_be_constructed_with_empty_quantified_products(): void
    {
        $this->sut = new ReplaceAssociatedQuantifiedProductUuids('PRODUCT_SET', []);
        $this->assertSame([], $this->sut->quantifiedProducts());
    }
}
