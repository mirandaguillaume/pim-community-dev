<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceDataValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceDataValue;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetMultiReferenceDataValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetMultiReferenceDataValueApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $updater;
    private SetMultiReferenceDataValueApplier $sut;

    protected function setUp(): void
    {
        $this->updater = $this->createMock(ObjectUpdaterInterface::class);
        $this->sut = new SetMultiReferenceDataValueApplier($this->updater);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetMultiReferenceDataValueApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_set_multi_reference_data_user_intent(): void
    {
        $product = new Product();
        $setMultiReferenceDataValue = new SetMultiReferenceDataValue(
            'code',
            null,
            null,
            ['Akeneo']
        );
        $this->updater->expects($this->once())->method('update')->with(
            $product,
            [
                        'values' => [
                            'code' => [
                                [
                                    'locale' => null,
                                    'scope' => null,
                                    'data' => ['Akeneo'],
                                ],
                            ],
                        ],
                    ]
        );
        $this->sut->apply($setMultiReferenceDataValue, $product, 1);
    }

    public function test_it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->apply(new SetEnabled(true), new Product(), 1);
    }
}
