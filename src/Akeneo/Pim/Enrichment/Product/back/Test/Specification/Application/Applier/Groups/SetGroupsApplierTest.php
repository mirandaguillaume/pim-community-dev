<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier\Groups;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\Application\Applier\Groups\SetGroupsApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetGroupsApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $productUpdater;
    private SetGroupsApplier $sut;

    protected function setUp(): void
    {
        $this->productUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $this->sut = new SetGroupsApplier($this->productUpdater);
    }

    public function test_it_is_an_user_intent_applier(): void
    {
        $this->assertInstanceOf(SetGroupsApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_a_set_groups_user_intent(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $userIntent = new SetGroups(['promotion', 'toto']);
        $product->method('getGroupCodes')->willReturn([]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['groups' => ['promotion', 'toto']]);
        $this->sut->apply($userIntent, $product, 10);
    }

    public function test_it_does_not_update_if_groups_are_the_same(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $userIntent = new SetGroups(['promotion', 'toto']);
        $product->method('getGroupCodes')->willReturn(['toto', 'promotion']);
        $this->productUpdater->expects($this->never())->method('update');
        $this->sut->apply($userIntent, $product, 10);
    }
}
