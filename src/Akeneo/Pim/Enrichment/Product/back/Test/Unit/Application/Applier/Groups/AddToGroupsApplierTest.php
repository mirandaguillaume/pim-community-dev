<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier\Groups;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\AddToGroups;
use Akeneo\Pim\Enrichment\Product\Application\Applier\Groups\AddToGroupsApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddToGroupsApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $productUpdater;
    private AddToGroupsApplier $sut;

    protected function setUp(): void
    {
        $this->productUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $this->sut = new AddToGroupsApplier($this->productUpdater);
    }

    public function test_it_is_an_user_intent_applier(): void
    {
        $this->assertInstanceOf(AddToGroupsApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_a_add_groups_user_intent_on_a_new_product(): void
    {
        $product = $this->createMock(Product::class);

        $userIntent = new AddToGroups(['promotion', 'toto']);
        $product->method('getGroupCodes')->willReturn([]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['groups' => ['promotion', 'toto']]);
        $this->sut->apply($userIntent, $product, 10);
    }

    public function test_it_applies_a_add_groups_user_intent_on_an_existing_product_with_groups(): void
    {
        $product = $this->createMock(Product::class);

        $userIntent = new AddToGroups(['toto']);
        $product->method('getGroupCodes')->willReturn(['promotion']);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['groups' => ['promotion', 'toto']]);
        $this->sut->apply($userIntent, $product, 10);
    }

    public function test_it_ignores_duplicate_groups(): void
    {
        $product = $this->createMock(Product::class);

        $userIntent = new AddToGroups(['promotion']);
        $product->method('getGroupCodes')->willReturn(['promotion']);
        $this->productUpdater->expects($this->never())->method('update');
        $this->sut->apply($userIntent, $product, 10);
    }
}
