<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\Application\Applier\ClearValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClearValueApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $updater;
    private ClearValueApplier $sut;

    protected function setUp(): void
    {
        $this->updater = $this->createMock(ObjectUpdaterInterface::class);
        $this->sut = new ClearValueApplier($this->updater);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ClearValueApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_clear_value_user_intent(): void
    {
        $product = new Product();
        $clearValue = new ClearValue('code', 'ecommerce', 'en_US');
        $this->updater->expects($this->once())->method('update')->with(
            $product,
            [
                        'values' => [
                            'code' => [
                                [
                                    'locale' => 'en_US',
                                    'scope' => 'ecommerce',
                                    'data' => null,
                                ],
                            ],
                        ],
                    ]
        );
        $this->sut->apply($clearValue, $product, 1);
    }

    public function test_it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->apply($setEnabledUserIntent, $product, 1);
    }
}
