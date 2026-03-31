<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\NumberValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use PHPUnit\Framework\TestCase;

class NumberValueTest extends TestCase
{
    private NumberValue $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_initializable(): void
    {
        $this->assertTrue(is_a(NumberValue::class, ValueInterface::class, true));
        $this->assertTrue(is_a(NumberValue::class, NumberValue::class, true));
    }

    public function test_it_equals_other_number_values(): void
    {
        $this->sut = NumberValue::scopableLocalizableValue('number', 10.356, 'ecommerce', 'en_US');
        $this->assertSame(true, $this->sut->isEqual(NumberValue::scopableLocalizableValue('number', 10.356, 'ecommerce', 'en_US')));
        $this->assertSame(true, $this->sut->isEqual(NumberValue::scopableLocalizableValue('number', '10.356', 'ecommerce', 'en_US')));
        $this->assertSame(true, $this->sut->isEqual(NumberValue::scopableLocalizableValue('number', '0010.35600', 'ecommerce', 'en_US')));
        $this->assertSame(true, $this->sut->isEqual(NumberValue::scopableLocalizableValue('number', 1.0356E1, 'ecommerce', 'en_US')));
        $this->assertSame(true, $this->sut->isEqual(NumberValue::scopableLocalizableValue('number', '1.0356E1', 'ecommerce', 'en_US')));
    }

    public function test_it_equals_other_number_values_with_big_numbers(): void
    {
        $this->sut = NumberValue::value('number', '1234567890.09876543212345');
        $this->assertSame(true, $this->sut->isEqual(NumberValue::value('number', '1234567890.09876543212345')));
        $this->assertSame(true, $this->sut->isEqual(NumberValue::value('number', '001234567890.0987654321234500')));
        $this->assertSame(true, $this->sut->isEqual(NumberValue::value('number', '1.23456789009876543212345E9')));
        $this->assertSame(true, $this->sut->isEqual(NumberValue::value('number', 1234567890.09876543212345)));
    }

    public function test_it_does_not_equal_number_values_with_different_data(): void
    {
        $this->sut = NumberValue::scopableLocalizableValue('number', 10.356, 'ecommerce', 'en_US');
        $this->assertSame(false, $this->sut->isEqual(NumberValue::scopableLocalizableValue('number', 10.357, 'ecommerce', 'en_US')));
        $this->assertSame(false, $this->sut->isEqual(NumberValue::scopableLocalizableValue('number', 10.35600005, 'ecommerce', 'en_US')));
        $this->assertSame(false, $this->sut->isEqual(NumberValue::scopableLocalizableValue('number', '11.356', 'ecommerce', 'en_US')));
        $this->assertSame(false, $this->sut->isEqual(NumberValue::scopableLocalizableValue('number', '0011.35600', 'ecommerce', 'en_US')));
        $this->assertSame(false, $this->sut->isEqual(NumberValue::scopableLocalizableValue('number', '1.1356E1', 'ecommerce', 'en_US')));
        $this->assertSame(false, $this->sut->isEqual(NumberValue::scopableLocalizableValue('number', 10, 'ecommerce', 'en_US')));
        $this->assertSame(false, $this->sut->isEqual(NumberValue::scopableLocalizableValue('number', 'abc', 'ecommerce', 'en_US')));
        $this->assertSame(false, $this->sut->isEqual(NumberValue::scopableLocalizableValue('number', true, 'ecommerce', 'en_US')));
        $this->assertSame(false, $this->sut->isEqual(NumberValue::scopableLocalizableValue('number', 'A10.356', 'ecommerce', 'en_US')));
    }

    public function test_it_does_not_equal_non_number_values(): void
    {
        $this->sut = NumberValue::scopableLocalizableValue('number', 10.356, 'ecommerce', 'en_US');
        $this->assertSame(false, $this->sut->isEqual(ScalarValue::scopableLocalizableValue('number', 10.356, 'ecommerce', 'en_US')));
    }

    public function test_it_does_not_equal_value_with_different_scope(): void
    {
        $this->sut = NumberValue::scopableLocalizableValue('number', 10.356, 'ecommerce', 'en_US');
        $this->assertSame(false, $this->sut->isEqual(NumberValue::scopableLocalizableValue('number', 10.356, 'print', 'en_US')));
        $this->assertSame(false, $this->sut->isEqual(NumberValue::localizableValue('number', 10.356, 'en_US')));
    }

    public function test_it_does_not_equal_value_with_different_locale(): void
    {
        $this->sut = NumberValue::scopableLocalizableValue('number', 10.356, 'ecommerce', 'en_US');
        $this->assertSame(false, $this->sut->isEqual(NumberValue::scopableLocalizableValue('number', 10.356, 'ecommerce', 'fr_FR')));
        $this->assertSame(false, $this->sut->isEqual(NumberValue::scopableValue('number', 10.356, 'ecommerce')));
    }
}
