<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearPriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\Application\Applier\ClearPriceValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClearPriceValueApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $updater;
    private ClearPriceValueApplier $sut;

    protected function setUp(): void
    {
        $this->updater = $this->createMock(ObjectUpdaterInterface::class);
        $this->sut = new ClearPriceValueApplier($this->updater);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ClearPriceValueApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_clear_price_value_user_intent(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $formerValue = $this->createMock(ValueInterface::class);

        $product->expects($this->once())->method('getValue')->with('a_price', null, null)->willReturn($formerValue);
        $formerValue->method('getData')->willReturn(new PriceCollection([
                        new ProductPrice('10', 'USD'),
                        new ProductPrice('42', 'EUR'),
                    ]));
        $clearPriceValueIntent = new ClearPriceValue('a_price', null, null, 'USD');
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
        $this->sut->apply($clearPriceValueIntent, $product, 1);
    }

    public function test_it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->apply($setEnabledUserIntent, $product, 1);
    }
}
