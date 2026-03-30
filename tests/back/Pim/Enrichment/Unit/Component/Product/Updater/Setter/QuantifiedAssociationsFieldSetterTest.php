<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\QuantifiedAssociationsFieldSetter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuantifiedAssociationsFieldSetterTest extends TestCase
{
    private QuantifiedAssociationsFieldSetter $sut;

    protected function setUp(): void
    {
        $this->sut = new QuantifiedAssociationsFieldSetter();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(QuantifiedAssociationsFieldSetter::class, $this->sut);
    }

    public function test_it_is_a_field_setter(): void
    {
        $this->assertInstanceOf(FieldSetterInterface::class, $this->sut);
    }

    public function test_it_only_work_with_quantified_associations_field(): void
    {
        $this->assertSame(true, $this->sut->supportsField('quantified_associations'));
        $this->assertSame(false, $this->sut->supportsField('family'));
    }

    public function test_it_override_quantified_associations_to_a_product(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $submittedQuantifiedAssociations = [
                    'PRODUCTSET_A' => [
                        'products' => [
                            ['identifier' => 'AKN_TS1_ALT', 'quantity' => 200],
                        ],
                    ],
                ];
        $product->expects($this->once())->method('patchQuantifiedAssociations')->with($submittedQuantifiedAssociations);
        $this->sut->setFieldData($product, 'quantified_associations', $submittedQuantifiedAssociations);
    }
}
