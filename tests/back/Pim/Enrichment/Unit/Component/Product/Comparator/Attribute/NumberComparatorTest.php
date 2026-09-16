<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute\NumberComparator;
use PHPUnit\Framework\TestCase;

/**
 * Zero values must be compared as numbers, never as falsy values (PIM-5666).
 *
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberComparatorTest extends TestCase
{
    private NumberComparator $sut;

    protected function setUp(): void
    {
        $this->sut = new NumberComparator(['pim_catalog_number']);
    }

    public function test_it_supports_the_number_attribute_type_only(): void
    {
        $this->assertTrue($this->sut->supports('pim_catalog_number'));
        $this->assertFalse($this->sut->supports('pim_catalog_metric'));
    }

    public function test_a_zero_added_to_a_missing_value_is_a_change(): void
    {
        $data = ['locale' => null, 'scope' => null, 'data' => '0'];

        $this->assertSame($data, $this->sut->compare($data, []));
    }

    public function test_an_integer_zero_added_to_an_empty_value_is_a_change(): void
    {
        $data = ['locale' => null, 'scope' => null, 'data' => 0];

        $this->assertSame($data, $this->sut->compare($data, ['locale' => null, 'scope' => null, 'data' => null]));
    }

    public function test_a_zero_equal_to_the_stored_zero_is_not_a_change(): void
    {
        $this->assertNull($this->sut->compare(
            ['locale' => null, 'scope' => null, 'data' => '0'],
            ['locale' => null, 'scope' => null, 'data' => '0.0000']
        ));
        $this->assertNull($this->sut->compare(
            ['locale' => null, 'scope' => null, 'data' => 0],
            ['locale' => null, 'scope' => null, 'data' => '0']
        ));
    }

    public function test_a_zero_replacing_another_number_is_a_change(): void
    {
        $data = ['locale' => null, 'scope' => null, 'data' => '0'];

        $this->assertSame($data, $this->sut->compare($data, ['locale' => null, 'scope' => null, 'data' => '5.0000']));
    }

    public function test_removing_a_stored_zero_is_a_change(): void
    {
        $data = ['locale' => null, 'scope' => null, 'data' => null];

        $this->assertSame($data, $this->sut->compare($data, ['locale' => null, 'scope' => null, 'data' => '0.0000']));
    }

    public function test_an_empty_value_added_to_a_missing_value_is_not_a_change(): void
    {
        $this->assertNull($this->sut->compare(['locale' => null, 'scope' => null, 'data' => null], []));
    }
}
