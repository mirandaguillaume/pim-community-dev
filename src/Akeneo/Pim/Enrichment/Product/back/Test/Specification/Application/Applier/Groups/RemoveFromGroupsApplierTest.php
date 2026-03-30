<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier\Groups;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\AddToGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\RemoveFromGroups;
use Akeneo\Pim\Enrichment\Product\Application\Applier\Groups\AddToGroupsApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\Groups\RemoveFromGroupsApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RemoveFromGroupsApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $productUpdater;
    private RemoveFromGroupsApplier $sut;

    protected function setUp(): void
    {
        $this->productUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $this->sut = new RemoveFromGroupsApplier($this->productUpdater);
    }

    public function test_it_is_an_user_intent_applier(): void
    {
        $this->assertInstanceOf(RemoveFromGroupsApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_a_remove_groups_user_intent_on_a_product(): void
    {
        $product = $this->createMock(Product::class);

        $userIntent = new RemoveFromGroups(['promotion']);
        $product->method('getGroupCodes')->willReturn(['promotion', 'foo', 'bar']);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['groups' => ['foo', 'bar']]);
        $this->sut->apply($userIntent, $product, 10);
    }

    public function test_it_applies_a_remove_groups_user_intent_on_non_present_groups(): void
    {
        $product = $this->createMock(Product::class);

        $userIntent = new RemoveFromGroups(['toto']);
        $product->method('getGroupCodes')->willReturn(['promotion', 'foo', 'bar']);
        $this->productUpdater->expects($this->never())->method('update');
        $this->sut->apply($userIntent, $product, 10);
    }
}
