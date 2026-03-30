<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\Application\Applier\AddMultiReferenceEntityValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddMultiReferenceEntityValueApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $updater;
    private AddMultiReferenceEntityValueApplier $sut;

    protected function setUp(): void
    {
        $this->updater = $this->createMock(ObjectUpdaterInterface::class);
        $this->sut = new AddMultiReferenceEntityValueApplier($this->updater);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AddMultiReferenceEntityValueApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_add_multi_reference_entity_user_intent(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $formerRecordCodes = $this->createMock(ValueInterface::class);

        $addMultiReferenceEntityValue = new AddMultiReferenceEntityValue(
            'code',
            null,
            null,
            ['AnotherAkeneo', 'AnotherZiggy']
        );
        $product->expects($this->once())->method('getValue')->with('code', null, null)->willReturn($formerRecordCodes);
        $formerRecordCodes->method('getData')->willReturn([
                        'Akeneo',
                        'Ziggy',
                    ]);
        $this->updater->expects($this->once())->method('update')->with(
            $product,
            [
                        'values' => [
                            'code' => [
                                [
                                    'locale' => null,
                                    'scope' => null,
                                    'data' => ['Akeneo', 'Ziggy', 'AnotherAkeneo', 'AnotherZiggy'],
                                ],
                            ],
                        ],
                    ]
        );
        $this->sut->apply($addMultiReferenceEntityValue, $product, 1);
    }

    public function test_it_does_not_update_the_product_when_there_is_nothing_to_add(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $formerRecordCodes = $this->createMock(ValueInterface::class);

        $addMultiReferenceEntityValue = new AddMultiReferenceEntityValue(
            'code',
            null,
            null,
            ['Ziggy', 'Akeneo']
        );
        $product->expects($this->once())->method('getValue')->with('code', null, null)->willReturn($formerRecordCodes);
        $formerRecordCodes->method('getData')->willReturn(['Akeneo', 'Ziggy']);
        $this->updater->expects($this->never())->method('update')->with($this->anything());
        $this->sut->apply($addMultiReferenceEntityValue, $product, 1);
    }

    public function test_it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->apply($setEnabledUserIntent, $product, 1);
    }
}
