<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductIdentifier;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIdentifierTest extends TestCase
{
    public function test_it_is_a_product_identifier(): void
    {
        $sut = new ProductIdentifier('AKN-123');
        $this->assertInstanceOf(ProductIdentifier::class, $sut);
    }

    public function test_it_generates_simple_prefixes(): void
    {
        $sut = new ProductIdentifier('AKN-123');
        $this->assertSame([
            'AKN-' => 123,
            'AKN-1' => 23,
            'AKN-12' => 3,
        ], $sut->getPrefixes());
    }

    public function test_it_generates_complex_prefixes(): void
    {
        $sut = new ProductIdentifier('AKN-123-foo-456');
        $this->assertSame([
            'AKN-' => 123,
            'AKN-1' => 23,
            'AKN-12' => 3,
            'AKN-123-foo-' => 456,
            'AKN-123-foo-4' => 56,
            'AKN-123-foo-45' => 6,
        ], $sut->getPrefixes());
    }

    public function test_it_does_not_return_any_prefix(): void
    {
        $sut = new ProductIdentifier('AKN-foo');
        $this->assertSame([], $sut->getPrefixes());
    }

    public function test_it_does_not_generate_prefixes_with_too_big_numbers(): void
    {
        // 9223372036854775807123
        $sut = new ProductIdentifier(\sprintf('%d123', PHP_INT_MAX));
        $this->assertSame([
            '922' => 3_372_036_854_775_807_123,
            '9223' => 372_036_854_775_807_123,
            '92233' => 72_036_854_775_807_123,
            '922337' => 2_036_854_775_807_123,
            '9223372' => 36_854_775_807_123,
            '92233720' => 36_854_775_807_123,
            '922337203' => 6_854_775_807_123,
            '9223372036' => 854_775_807_123,
            '92233720368' => 54_775_807_123,
            '922337203685' => 4_775_807_123,
            '9223372036854' => 775_807_123,
            '92233720368547' => 75_807_123,
            '922337203685477' => 5_807_123,
            '9223372036854775' => 807123,
            '92233720368547758' => 7123,
            '922337203685477580' => 7123,
            '9223372036854775807' => 123,
            '92233720368547758071' => 23,
            '922337203685477580712' => 3,
        ], $sut->getPrefixes());
    }
}
