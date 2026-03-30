<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetPriceCollectionValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetPriceCollectionValueApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $updater;
    private SetPriceCollectionValueApplier $sut;

    protected function setUp(): void
    {
        $this->updater = $this->createMock(ObjectUpdaterInterface::class);
        $this->sut = new SetPriceCollectionValueApplier($this->updater);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetPriceCollectionValueApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_set_price_collection_value_user_intent(): void
    {
        $product = new Product();
        $setPriceValueIntent = new SetPriceCollectionValue(
            'msrp',
            'ecommerce',
            'en_US',
            [
                        new PriceValue(42, 'EUR'),
                        new PriceValue('45', 'USD'),
                    ]
        );
        $this->updater->expects($this->once())->method('update')->with(
            $product,
            [
                        'values' => [
                            'msrp' => [
                                [
                                    'locale' => 'en_US',
                                    'scope' => 'ecommerce',
                                    'data' => [
                                        [
                                            'amount' => '42',
                                            'currency' => 'EUR',
                                        ],
                                        [
                                            'amount' => '45',
                                            'currency' => 'USD',
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
