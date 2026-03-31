<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetCategoriesApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableCategoryCodes;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetCategoriesApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $productUpdater;
    private GetNonViewableCategoryCodes|MockObject $getNonViewableCategoryCodes;
    private SetCategoriesApplier $sut;

    protected function setUp(): void
    {
        $this->productUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $this->getNonViewableCategoryCodes = $this->createMock(GetNonViewableCategoryCodes::class);
        $this->sut = new SetCategoriesApplier($this->productUpdater);
    }

    public function test_it_is_an_user_intent_applier(): void
    {
        $this->assertInstanceOf(SetCategoriesApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_a_set_categories_user_intent_on_a_new_product(): void
    {
        $userIntent = new SetCategories(['categoryA', 'categoryB']);
        $product = new Product();
        $product->setIdentifier('foo');
        $this->getNonViewableCategoryCodes->method('fromProductUuids')->with([$product->getUuid()], 10)->willReturn([]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['categories' => ['categoryA', 'categoryB']]);
        $this->sut->apply($userIntent, $product, 10);
    }
}
