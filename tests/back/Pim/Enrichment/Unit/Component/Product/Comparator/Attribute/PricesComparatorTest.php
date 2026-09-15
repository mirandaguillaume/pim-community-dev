<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute\PricesComparator;
use PHPUnit\Framework\TestCase;

/**
 * A zero price must be compared as a number and survive the filtering of empty prices (PIM-5666).
 *
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PricesComparatorTest extends TestCase
{
    private PricesComparator $sut;

    protected function setUp(): void
    {
        $this->sut = new PricesComparator(['pim_catalog_price_collection']);
    }

    public function test_it_supports_the_price_collection_attribute_type_only(): void
    {
        $this->assertTrue($this->sut->supports('pim_catalog_price_collection'));
        $this->assertFalse($this->sut->supports('pim_catalog_number'));
    }

    public function test_a_zero_price_added_to_a_missing_value_is_a_change_and_keeps_the_zero_price(): void
    {
        $data = [
            'locale' => null,
            'scope' => null,
            'data' => [['amount' => '0', 'currency' => 'EUR'], ['amount' => null, 'currency' => 'USD']],
        ];

        $this->assertSame(
            ['locale' => null, 'scope' => null, 'data' => [['amount' => '0', 'currency' => 'EUR']]],
            $this->sut->compare($data, [])
        );
    }

    public function test_an_integer_zero_price_added_to_a_missing_value_is_a_change(): void
    {
        $data = ['locale' => null, 'scope' => null, 'data' => [['amount' => 0, 'currency' => 'EUR']]];

        $this->assertSame($data, $this->sut->compare($data, []));
    }

    public function test_a_zero_price_equal_to_the_stored_price_is_not_a_change(): void
    {
        $this->assertNull($this->sut->compare(
            ['locale' => null, 'scope' => null, 'data' => [['amount' => '0', 'currency' => 'EUR']]],
            ['locale' => null, 'scope' => null, 'data' => [['amount' => '0.00', 'currency' => 'EUR']]]
        ));
    }

    public function test_a_zero_price_replacing_another_price_is_a_change(): void
    {
        $data = ['locale' => null, 'scope' => null, 'data' => [['amount' => '0', 'currency' => 'EUR']]];

        $this->assertSame($data, $this->sut->compare(
            $data,
            ['locale' => null, 'scope' => null, 'data' => [['amount' => '10.50', 'currency' => 'EUR']]]
        ));
    }

    public function test_removing_a_stored_zero_price_is_a_change(): void
    {
        $this->assertSame(
            ['locale' => null, 'scope' => null, 'data' => []],
            $this->sut->compare(
                ['locale' => null, 'scope' => null, 'data' => [['amount' => null, 'currency' => 'EUR']]],
                ['locale' => null, 'scope' => null, 'data' => [['amount' => '0.00', 'currency' => 'EUR']]]
            )
        );
    }

    public function test_empty_prices_added_to_a_missing_value_are_not_a_change(): void
    {
        $this->assertNull($this->sut->compare(
            [
                'locale' => null,
                'scope' => null,
                'data' => [['amount' => null, 'currency' => 'EUR'], ['amount' => null, 'currency' => 'USD']],
            ],
            []
        ));
    }
}
