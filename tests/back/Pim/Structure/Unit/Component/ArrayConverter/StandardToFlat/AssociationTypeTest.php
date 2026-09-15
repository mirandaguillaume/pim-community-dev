<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\ArrayConverter\StandardToFlat;

use Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\AssociationType;
use PHPUnit\Framework\TestCase;

/**
 * The flat format written by the association type exports
 * (pim_connector.array_converter.standard_to_flat.association_type).
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AssociationTypeTest extends TestCase
{
    private AssociationType $sut;

    protected function setUp(): void
    {
        $this->sut = new AssociationType();
    }

    public function test_it_converts_a_two_way_association_type(): void
    {
        $this->assertSame(
            [
                'code' => 'X_SELL',
                'label-en_US' => 'Cross sell',
                'label-fr_FR' => 'Vente croisée',
                'is_two_way' => 1,
                'is_quantified' => 0,
            ],
            $this->sut->convert([
                'code' => 'X_SELL',
                'labels' => ['en_US' => 'Cross sell', 'fr_FR' => 'Vente croisée'],
                'is_two_way' => true,
                'is_quantified' => false,
            ])
        );
    }

    public function test_it_converts_a_quantified_association_type(): void
    {
        $this->assertSame(
            [
                'code' => 'PACK',
                'label-en_US' => 'Pack',
                'is_two_way' => 0,
                'is_quantified' => 1,
            ],
            $this->sut->convert([
                'code' => 'PACK',
                'labels' => ['en_US' => 'Pack'],
                'is_two_way' => false,
                'is_quantified' => true,
            ])
        );
    }

    public function test_it_keeps_an_empty_label_as_null(): void
    {
        // The standard TranslationNormalizer turns an empty label into null.
        $this->assertSame(
            ['code' => 'UPSELL', 'label-de_DE' => null],
            $this->sut->convert(['code' => 'UPSELL', 'labels' => ['de_DE' => null]])
        );
    }

    public function test_it_writes_no_label_column_without_labels(): void
    {
        $this->assertSame(
            ['code' => 'SUBSTITUTION', 'is_two_way' => 0, 'is_quantified' => 0],
            $this->sut->convert([
                'code' => 'SUBSTITUTION',
                'labels' => [],
                'is_two_way' => false,
                'is_quantified' => false,
            ])
        );
    }

    public function test_it_casts_other_properties_to_string(): void
    {
        $this->assertSame(['code' => '123'], $this->sut->convert(['code' => 123]));
    }
}
