<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AutoNumberTest extends TestCase
{
    private AutoNumber $sut;

    protected function setUp(): void
    {
        $this->sut = AutoNumber::fromValues(5,2);
    }

    public function test_it_is_a_auto_number(): void
    {
        $this->assertInstanceOf(AutoNumber::class, $this->sut);
    }

    public function test_it_cannot_be_instantiated_with_number_min_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AutoNumber::fromValues(-5,2);
    }

    public function test_it_cannot_be_instantiated_with_digits_min_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AutoNumber::fromValues(5,-2);
    }

    public function test_it_returns_a_number_min(): void
    {
        $this->assertSame(5, $this->sut->numberMin());
    }

    public function test_it_returns_a_digits_min(): void
    {
        $this->assertSame(2, $this->sut->digitsMin());
    }

    public function test_it_normalize_an_auto_number(): void
    {
        $this->assertSame([
                    'type' => 'auto_number',
                    'numberMin' => 5,
                    'digitsMin' => 2,
                ], $this->sut->normalize());
    }

    public function test_it_creates_from_normalized(): void
    {
        $this->assertEquals(AutoNumber::fromValues(7, 8), $this->sut->fromNormalized([
                    'type' => 'auto_number',
                    'numberMin' => 7,
                    'digitsMin' => 8,
                ]));
    }

    public function test_it_throws_an_exception_when_type_is_bad(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AutoNumber::fromNormalized([
                    'type' => 'bad',
                    'numberMin' => 7,
                    'digitsMin' => 8,
                ]);
    }

    public function test_it_throws_an_exception_when_type_key_is_missing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AutoNumber::fromNormalized([
                    'numberMin' => 7,
                    'digitsMin' => 8,
                ]);
    }

    public function test_it_throws_an_exception_when_number_min_key_is_missing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AutoNumber::fromNormalized([
                    'type' => 'auto_number',
                    'digitsMin' => 8,
                ]);
    }

    public function test_it_throws_an_exception_when_digits_min_key_is_missing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AutoNumber::fromNormalized([
                    'type' => 'auto_number',
                    'numberMin' => 7,
                ]);
    }

    public function test_it_throws_an_exception_from_normalized_with_number_min_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AutoNumber::fromNormalized([
                    'type' => 'auto_number',
                    'numberMin' => -7,
                    'digitsMin' => 8,
                ]);
    }

    public function test_it_throws_an_exception_from_normalized_with_digits_min_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AutoNumber::fromNormalized([
                    'type' => 'auto_number',
                    'numberMin' => 7,
                    'digitsMin' => -8,
                ]);
    }

    public function test_it_throws_an_exception_from_normalized_with_digits_min_is_zero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AutoNumber::fromNormalized([
                    'type' => 'auto_number',
                    'numberMin' => 7,
                    'digitsMin' => 0,
                ]);
    }

    public function test_it_throws_an_exception_from_normalized_with_digits_min_greater_than_limit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AutoNumber::fromNormalized([
                    'type' => 'auto_number',
                    'numberMin' => 7,
                    'digitsMin' => (AutoNumber::LIMIT_DIGITS_MAX + 1),
                ]);
    }
}
