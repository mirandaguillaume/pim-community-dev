<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\Application\Applier\RemoveMultiReferenceEntityValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetSimpleReferenceEntityValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetSimpleReferenceEntityValueApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $updater;
    private SetSimpleReferenceEntityValueApplier $sut;

    protected function setUp(): void
    {
        $this->updater = $this->createMock(ObjectUpdaterInterface::class);
        $this->sut = new SetSimpleReferenceEntityValueApplier($this->updater);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetSimpleReferenceEntityValueApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_set_simple_reference_entity_user_intent(): void
    {
        $product = new Product();
        $setSimpleReferenceEntityValue = new SetSimpleReferenceEntityValue(
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
        $this->sut->apply($setSimpleReferenceEntityValue, $product, 1);
    }

    public function test_it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->apply($setEnabledUserIntent, $product, 1);
    }
}
