<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\Application\Applier\RemoveMultiReferenceEntityValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RemoveMultiReferenceEntityValueApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $updater;
    private RemoveMultiReferenceEntityValueApplier $sut;

    protected function setUp(): void
    {
        $this->updater = $this->createMock(ObjectUpdaterInterface::class);
        $this->sut = new RemoveMultiReferenceEntityValueApplier($this->updater);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(RemoveMultiReferenceEntityValueApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_remove_multi_reference_entity_user_intent(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $formerRecordCodes = $this->createMock(ValueInterface::class);

        $removeMultiReferenceEntityValue = new RemoveMultiReferenceEntityValue(
            'code',
            null,
            null,
            ['Akeneo', 'Ziggy']
        );
        $product->expects($this->once())->method('getValue')->with('code', null, null)->willReturn($formerRecordCodes);
        $formerRecordCodes->method('getData')->willReturn([
                        'Akeneo',
                        'AnotherAkeneo',
                        'Ziggy',
                        'AnotherZiggy',
                    ]);
        $this->updater->expects($this->once())->method('update')->with(
            $product,
            [
                        'values' => [
                            'code' => [
                                [
                                    'locale' => null,
                                    'scope' => null,
                                    'data' => ['AnotherAkeneo', 'AnotherZiggy'],
                                ],
                            ],
                        ],
                    ]
        );
        $this->sut->apply($removeMultiReferenceEntityValue, $product, 1);
    }

    public function test_it_removes_the_last_records(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $formerRecordCodes = $this->createMock(ValueInterface::class);

        $removeMultiReferenceEntityValue = new RemoveMultiReferenceEntityValue(
            'code',
            null,
            null,
            ['Akeneo']
        );
        $product->expects($this->once())->method('getValue')->with('code', null, null)->willReturn($formerRecordCodes);
        $formerRecordCodes->method('getData')->willReturn(['Akeneo']);
        $this->updater->expects($this->once())->method('update')->with(
            $product,
            [
                        'values' => [
                            'code' => [
                                [
                                    'locale' => null,
                                    'scope' => null,
                                    'data' => [],
                                ],
                            ],
                        ],
                    ]
        );
        $this->sut->apply($removeMultiReferenceEntityValue, $product, 1);
    }

    public function test_it_does_nothing_when_product_has_no_record_to_remove(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $removeMultiReferenceEntityValue = new RemoveMultiReferenceEntityValue(
            'code',
            null,
            null,
            ['Akeneo', 'Ziggy']
        );
        $product->expects($this->once())->method('getValue')->with('code', null, null)->willReturn(null);
        $this->updater->expects($this->never())->method('update')->with($this->anything());
        $this->sut->apply($removeMultiReferenceEntityValue, $product, 1);
    }

    public function test_it_does_nothing_when_product_does_not_have_the_record_to_remove(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $formerRecordCodes = $this->createMock(ValueInterface::class);

        $removeMultiReferenceEntityValue = new RemoveMultiReferenceEntityValue(
            'code',
            null,
            null,
            ['Ziggy']
        );
        $product->expects($this->once())->method('getValue')->with('code', null, null)->willReturn($formerRecordCodes);
        $formerRecordCodes->method('getData')->willReturn(['Akeneo']);
        $this->updater->expects($this->never())->method('update')->with($this->anything());
        $this->sut->apply($removeMultiReferenceEntityValue, $product, 1);
    }

    public function test_it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->apply($setEnabledUserIntent, $product, 1);
    }
}
