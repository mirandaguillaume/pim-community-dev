<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\Application\Applier\AddMultipleValuesApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddMultipleValuesApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $updater;
    private AddMultipleValuesApplier $sut;

    protected function setUp(): void
    {
        $this->updater = $this->createMock(ObjectUpdaterInterface::class);
        $this->sut = new AddMultipleValuesApplier($this->updater);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AddMultipleValuesApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_add_multiple_values_user_intent(): void
    {
        $product = new Product();
        $addMultiSelectValue = new AddMultiSelectValue('code', 'ecommerce', 'en_US', ['option1', 'option2']);
        $this->updater->expects($this->once())->method('update')->with(
            $product,
            [
                        'values' => [
                            'code' => [
                                [
                                    'locale' => 'en_US',
                                    'scope' => 'ecommerce',
                                    'data' => ['option1', 'option2'],
                                ],
                            ],
                        ],
                    ]
        );
        $this->sut->apply($addMultiSelectValue, $product, 1);
    }

    public function test_it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->apply($setEnabledUserIntent, $product, 1);
    }
}
