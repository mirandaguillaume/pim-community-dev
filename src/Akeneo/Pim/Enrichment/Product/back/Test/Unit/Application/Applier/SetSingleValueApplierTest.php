<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetSingleValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetSingleValueApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $updater;
    private SetSingleValueApplier $sut;

    protected function setUp(): void
    {
        $this->updater = $this->createMock(ObjectUpdaterInterface::class);
        $this->sut = new SetSingleValueApplier($this->updater);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetSingleValueApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_a_user_intent(): void
    {
        $product = new Product();
        $setText = new SetTextValue('code', 'ecommerce', 'en_US', 'foo');
        $this->updater->expects($this->once())->method('update')->with(
            $product,
            [
                        'values' => [
                            'code' => [
                                [
                                    'locale' => 'en_US',
                                    'scope' => 'ecommerce',
                                    'data' => 'foo',
                                ],
                            ],
                        ],
                    ]
        );
        $this->sut->apply($setText, $product, 1);
    }

    public function test_it_applies_an_identifier_value_user_intent(): void
    {
        $product = new Product();
        $setText = new SetIdentifierValue('sku', 'foo');
        $this->updater->expects($this->once())->method('update')->with(
            $product,
            [
                        'values' => [
                            'sku' => [
                                [
                                    'locale' => null,
                                    'scope' => null,
                                    'data' => 'foo',
                                ],
                            ],
                        ],
                    ]
        );
        $this->sut->apply($setText, $product, 1);
    }

    public function test_it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->apply($setEnabledUserIntent, $product, 1);
    }
}
