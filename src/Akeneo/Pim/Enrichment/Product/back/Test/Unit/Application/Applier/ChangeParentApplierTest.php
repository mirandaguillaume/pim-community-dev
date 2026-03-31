<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\Application\Applier\ChangeParentApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChangeParentApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $updater;
    private ChangeParentApplier $sut;

    protected function setUp(): void
    {
        $this->updater = $this->createMock(ObjectUpdaterInterface::class);
        $this->sut = new ChangeParentApplier($this->updater);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ChangeParentApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_set_parent_user_intent(): void
    {
        $product = new Product();
        $setParent = new ChangeParent('product_model_code');
        $this->updater->expects($this->once())->method('update')->with($product, ['parent' => 'product_model_code']);
        $this->sut->apply($setParent, $product, 1);
    }

    public function test_it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->apply($setEnabledUserIntent, $product, 1);
    }

    public function test_it_does_not_update_if_parent_is_already_set_on_the_product(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $productModel = $this->createMock(ProductModelInterface::class);

        $setParent = new ChangeParent('product_model_code');
        $product->method('getParent')->willReturn($productModel);
        $productModel->method('getCode')->willReturn('product_model_code');
        $this->updater->expects($this->never())->method('update');
        $this->sut->apply($setParent, $product, 1);
    }
}
