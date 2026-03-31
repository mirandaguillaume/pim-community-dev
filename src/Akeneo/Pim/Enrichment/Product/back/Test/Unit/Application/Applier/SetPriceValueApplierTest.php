<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceValue;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetPriceValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetPriceValueApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $updater;
    private SetPriceValueApplier $sut;

    protected function setUp(): void
    {
        $this->updater = $this->createMock(ObjectUpdaterInterface::class);
        $this->sut = new SetPriceValueApplier($this->updater);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetPriceValueApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_set_price_value_user_intent(): void
    {
        $product = new Product();
        $setPriceValueIntent = new SetPriceValue(
            'a_price',
            'ecommerce',
            'en_US',
            new PriceValue(42, 'EUR'),
        );
        $this->updater->expects($this->once())->method('update')->with(
            $product,
            [
                        'values' => [
                            'a_price' => [
                                [
                                    'locale' => 'en_US',
                                    'scope' => 'ecommerce',
                                    'data' => [
                                        [
                                            'amount' => '42',
                                            'currency' => 'EUR',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
        );
        $this->sut->apply($setPriceValueIntent, $product, 1);
    }

    public function test_it_applies_set_price_value_user_intent_and_add_to_an_existing_price_collection_value(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $formerValue = $this->createMock(ValueInterface::class);

        $product->expects($this->once())->method('getValue')->with('a_price', null, null)->willReturn($formerValue);
        $formerValue->method('getData')->willReturn(new PriceCollection([
                        new ProductPrice('10', 'USD'),
                    ]));
        $setPriceValueIntent = new SetPriceValue(
            'a_price',
            null,
            null,
            new PriceValue('42', 'EUR'),
        );
        $this->updater->expects($this->once())->method('update')->with(
            $product,
            [
                        'values' => [
                            'a_price' => [
                                [
                                    'locale' => null,
                                    'scope' => null,
                                    'data' => [
                                        [
                                            'amount' => '10',
                                            'currency' => 'USD',
                                        ],
                                        [
                                            'amount' => '42',
                                            'currency' => 'EUR',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
        );
        $this->sut->apply($setPriceValueIntent, $product, 1);
    }

    public function test_it_applies_set_price_value_user_intent_and_update_an_existing_price_collection_value(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $formerValue = $this->createMock(ValueInterface::class);

        $product->expects($this->once())->method('getValue')->with('a_price', null, null)->willReturn($formerValue);
        $formerValue->method('getData')->willReturn(new PriceCollection([
                        new ProductPrice('10', 'EUR'),
                    ]));
        $setPriceValueIntent = new SetPriceValue(
            'a_price',
            null,
            null,
            new PriceValue('42', 'EUR'),
        );
        $this->updater->expects($this->once())->method('update')->with(
            $product,
            [
                        'values' => [
                            'a_price' => [
                                [
                                    'locale' => null,
                                    'scope' => null,
                                    'data' => [
                                        [
                                            'amount' => '42',
                                            'currency' => 'EUR',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
        );
        $this->sut->apply($setPriceValueIntent, $product, 1);
    }

    public function test_it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->apply($setEnabledUserIntent, $product, 1);
    }
}
