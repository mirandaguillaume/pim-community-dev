<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\ArrayConverter\StandardToFlat;

use Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\AttributeGroup;
use PHPUnit\Framework\TestCase;

/**
 * The flat format written by the attribute group exports
 * (pim_connector.array_converter.standard_to_flat.attribute_group, shared by the CSV and XLSX writers).
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeGroupTest extends TestCase
{
    private AttributeGroup $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeGroup();
    }

    public function test_it_converts_an_attribute_group(): void
    {
        // Same key order as the standard AttributeGroupNormalizer: code, sort_order, attributes, labels.
        $this->assertSame(
            [
                'code' => 'info',
                'sort_order' => '1',
                'attributes' => 'sku,name,manufacturer',
                'label-en_US' => 'Product information',
                'label-fr_FR' => 'Informations',
            ],
            $this->sut->convert([
                'code' => 'info',
                'sort_order' => 1,
                'attributes' => ['sku', 'name', 'manufacturer'],
                'labels' => ['en_US' => 'Product information', 'fr_FR' => 'Informations'],
            ])
        );
    }

    public function test_it_converts_an_empty_attribute_group(): void
    {
        $this->assertSame(
            ['code' => 'empty', 'sort_order' => '0', 'attributes' => ''],
            $this->sut->convert([
                'code' => 'empty',
                'sort_order' => 0,
                'attributes' => [],
                'labels' => [],
            ])
        );
    }

    public function test_it_keeps_an_empty_label_as_null(): void
    {
        // The standard TranslationNormalizer turns an empty label into null.
        $this->assertSame(
            ['code' => 'sizes', 'label-en_US' => null],
            $this->sut->convert(['code' => 'sizes', 'labels' => ['en_US' => null]])
        );
    }

    public function test_it_writes_a_single_attribute_without_separator(): void
    {
        $this->assertSame(
            ['code' => 'sizes', 'sort_order' => '3', 'attributes' => 'size'],
            $this->sut->convert(['code' => 'sizes', 'sort_order' => 3, 'attributes' => ['size']])
        );
    }
}
