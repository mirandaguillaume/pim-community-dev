<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ConvertToSimpleProduct;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\Application\Applier\ConvertToSimpleProductApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConvertToSimpleProductApplierTest extends TestCase
{
    private RemoveParentInterface|MockObject $removeParent;
    private ConvertToSimpleProductApplier $sut;

    protected function setUp(): void
    {
        $this->removeParent = $this->createMock(RemoveParentInterface::class);
        $this->sut = new ConvertToSimpleProductApplier($this->removeParent);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ConvertToSimpleProductApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_remove_parent_user_intent(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $removeParentIntent = new ConvertToSimpleProduct();
        $product->method('isVariant')->willReturn(true);
        $this->removeParent->expects($this->once())->method('from')->with($product);
        $this->sut->apply($removeParentIntent, $product, 1);
    }

    public function test_it_does_nothing_when_product_has_no_parent(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $removeParentIntent = new ConvertToSimpleProduct();
        $product->method('isVariant')->willReturn(false);
        $this->removeParent->expects($this->never())->method('from')->with($product);
        $this->sut->apply($removeParentIntent, $product, 1);
    }

    public function test_it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->apply($setEnabledUserIntent, $product, 1);
    }
}
