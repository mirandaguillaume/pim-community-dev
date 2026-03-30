<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddCategories;
use Akeneo\Pim\Enrichment\Product\Application\Applier\AddCategoriesApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddCategoriesApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $productUpdater;
    private GetCategoryCodes|MockObject $getCategoryCodes;
    private AddCategoriesApplier $sut;

    protected function setUp(): void
    {
        $this->productUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $this->getCategoryCodes = $this->createMock(GetCategoryCodes::class);
        $this->sut = new AddCategoriesApplier($this->productUpdater, $this->getCategoryCodes);
    }

    public function test_it_is_an_user_intent_applier(): void
    {
        $this->assertInstanceOf(AddCategoriesApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_supports_add_category_user_intent(): void
    {
        $this->assertSame([AddCategories::class], $this->sut->getSupportedUserIntents());
    }

    public function test_it_adds_categories_on_an_uncategorized_product(): void
    {
        $product = new Product();
        $this->getCategoryCodes->method('fromProductUuids')->with([$product->getUuid()])->willReturn([$product->getUuid()->toString() => []]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['categories' => ['supplier', 'print']]);
        $this->sut->apply(new AddCategories(['supplier', 'print']), $product, 10);
    }

    public function test_it_adds_categories_on_an_categorized_product(): void
    {
        $product = new Product();
        $this->getCategoryCodes->method('fromProductUuids')->with([$product->getUuid()])->willReturn([$product->getUuid()->toString() => ['print', 'master']]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['categories' => ['print', 'master', 'supplier']]);
        $this->sut->apply(new AddCategories(['supplier', 'print']), $product, 10);
    }

    public function test_it_adds_categories_on_an_unknown_product(): void
    {
        $product = new Product();
        $this->getCategoryCodes->method('fromProductUuids')->with([$product->getUuid()])->willReturn([]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['categories' => ['supplier', 'print']]);
        $this->sut->apply(new AddCategories(['supplier', 'print']), $product, 10);
    }
}
