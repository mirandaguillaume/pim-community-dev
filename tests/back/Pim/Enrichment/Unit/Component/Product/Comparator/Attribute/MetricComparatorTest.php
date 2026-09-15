<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute\MetricComparator;
use PHPUnit\Framework\TestCase;

/**
 * A zero amount must be compared as a number, never as a falsy value (PIM-5666).
 *
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricComparatorTest extends TestCase
{
    private MetricComparator $sut;

    protected function setUp(): void
    {
        $this->sut = new MetricComparator(['pim_catalog_metric']);
    }

    public function test_it_supports_the_metric_attribute_type_only(): void
    {
        $this->assertTrue($this->sut->supports('pim_catalog_metric'));
        $this->assertFalse($this->sut->supports('pim_catalog_number'));
    }

    public function test_a_zero_amount_added_to_a_missing_value_is_a_change(): void
    {
        $data = ['locale' => null, 'scope' => null, 'data' => ['amount' => '0', 'unit' => 'KILOWATT']];
        $this->assertSame($data, $this->sut->compare($data, []));

        $data = ['locale' => null, 'scope' => null, 'data' => ['amount' => 0, 'unit' => 'KILOWATT']];
        $this->assertSame($data, $this->sut->compare($data, []));
    }

    public function test_a_zero_amount_equal_to_the_stored_amount_is_not_a_change(): void
    {
        $this->assertNull($this->sut->compare(
            ['locale' => null, 'scope' => null, 'data' => ['amount' => '0', 'unit' => 'KILOWATT']],
            ['locale' => null, 'scope' => null, 'data' => ['amount' => '0.0000', 'unit' => 'KILOWATT']]
        ));
    }

    public function test_a_zero_amount_replacing_another_amount_is_a_change(): void
    {
        $data = ['locale' => null, 'scope' => null, 'data' => ['amount' => '0', 'unit' => 'KILOWATT']];

        $this->assertSame($data, $this->sut->compare(
            $data,
            ['locale' => null, 'scope' => null, 'data' => ['amount' => '12.5000', 'unit' => 'KILOWATT']]
        ));
    }

    public function test_a_zero_amount_in_another_unit_is_a_change(): void
    {
        $data = ['locale' => null, 'scope' => null, 'data' => ['amount' => '0', 'unit' => 'WATT']];

        $this->assertSame($data, $this->sut->compare(
            $data,
            ['locale' => null, 'scope' => null, 'data' => ['amount' => '0.0000', 'unit' => 'KILOWATT']]
        ));
    }

    public function test_removing_a_stored_zero_amount_gives_an_empty_value(): void
    {
        $this->assertSame(
            ['scope' => null, 'locale' => null, 'data' => null],
            $this->sut->compare(
                ['locale' => null, 'scope' => null, 'data' => ['amount' => null, 'unit' => 'KILOWATT']],
                ['locale' => null, 'scope' => null, 'data' => ['amount' => '0.0000', 'unit' => 'KILOWATT']]
            )
        );
    }

    public function test_an_empty_amount_added_to_a_missing_value_is_not_a_change(): void
    {
        $this->assertNull($this->sut->compare(
            ['locale' => null, 'scope' => null, 'data' => ['amount' => null, 'unit' => null]],
            []
        ));
    }
}
