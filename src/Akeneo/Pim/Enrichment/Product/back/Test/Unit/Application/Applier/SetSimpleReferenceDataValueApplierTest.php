<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceDataValue;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetSimpleReferenceDataValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetSimpleReferenceDataValueApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $updater;
    private SetSimpleReferenceDataValueApplier $sut;

    protected function setUp(): void
    {
        $this->updater = $this->createMock(ObjectUpdaterInterface::class);
        $this->sut = new SetSimpleReferenceDataValueApplier($this->updater);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetSimpleReferenceDataValueApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_set_simple_reference_data_user_intent(): void
    {
        $product = new Product();
        $setSimpleReferenceDataValue = new SetSimpleReferenceDataValue(
            'code',
            null,
            null,
            'Akeneo'
        );
        $this->updater->expects($this->once())->method('update')->with(
            $product,
            [
                        'values' => [
                            'code' => [
                                [
                                    'locale' => null,
                                    'scope' => null,
                                    'data' => 'Akeneo',
                                ],
                            ],
                        ],
                    ]
        );
        $this->sut->apply($setSimpleReferenceDataValue, $product, 1);
    }

    public function test_it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->apply(new SetEnabled(true), new Product(), 1);
    }
}
