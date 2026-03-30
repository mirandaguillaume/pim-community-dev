<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveCategories;
use Akeneo\Pim\Enrichment\Product\Application\Applier\RemoveCategoriesApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RemoveCategoriesApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $productUpdater;
    private GetCategoryCodes|MockObject $getCategoryCodes;
    private RemoveCategoriesApplier $sut;

    protected function setUp(): void
    {
        $this->productUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $this->getCategoryCodes = $this->createMock(GetCategoryCodes::class);
        $this->sut = new RemoveCategoriesApplier($this->productUpdater, $this->getCategoryCodes);
    }

    public function test_it_is_an_user_intent_applier(): void
    {
        $this->assertInstanceOf(RemoveCategoriesApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_supports_remove_category_user_intent(): void
    {
        $this->assertSame([RemoveCategories::class], $this->sut->getSupportedUserIntents());
    }

    public function test_it_removes_categories_on_an_uncategorized_product(): void
    {
        $product = new Product();
        $this->getCategoryCodes->method('fromProductUuids')->with([$product->getUuid()])->willReturn([$product->getUuid()->toString() => []]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['categories' => []]);
        $this->sut->apply(new RemoveCategories(['supplier', 'print']), $product, 10);
    }

    public function test_it_removes_categories_on_an_categorized_product(): void
    {
        $product = new Product();
        $this->getCategoryCodes->method('fromProductUuids')->with([$product->getUuid()])->willReturn([$product->getUuid()->toString() => ['print', 'master', 'sales']]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['categories' => ['master', 'sales']]);
        $this->sut->apply(new RemoveCategories(['supplier', 'print']), $product, 10);
    }

    public function test_it_removes_categories_on_an_unknown_product(): void
    {
        $product = new Product();
        $this->getCategoryCodes->method('fromProductUuids')->with([$product->getUuid()])->willReturn([]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['categories' => []]);
        $this->sut->apply(new RemoveCategories(['supplier', 'print']), $product, 10);
    }
}
