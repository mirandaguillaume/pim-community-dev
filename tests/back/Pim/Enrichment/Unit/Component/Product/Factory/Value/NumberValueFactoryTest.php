<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\NumberValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\NumberValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class NumberValueFactoryTest extends TestCase
{
    private NumberValueFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new NumberValueFactory();
    }

    public function test_it_is_a_read_value_factory(): void
    {
        $this->assertInstanceOf(ValueFactory::class, $this->sut);
    }

    public function test_it_supports_number_attribute_type(): void
    {
        $this->assertSame(AttributeTypes::NUMBER, $this->sut->supportedAttributeType());
    }

    public function test_it_does_not_support_empty_values(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->createByCheckingData($this->getAttribute(true, true),
                    'ecommerce',
                    'fr_FR',
                    null);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->createByCheckingData($this->getAttribute(true, true),
                    'ecommerce',
                    'fr_FR',
                    ' ');
    }

    public function test_it_does_not_support_non_numeric_types(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->createByCheckingData($this->getAttribute(true, true),
                    'ecommerce',
                    'fr_FR',
                    true);
    }

    public function test_it_generates_a_number_value_from_a_float(): void
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->sut->createByCheckingData($attribute, null, null, 123.456);
        $this->assertEquals(NumberValue::value('an_attribute', 123.456), $value);
    }

    public function test_it_generates_a_number_value_from_an_integer(): void
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->sut->createByCheckingData($attribute, null, null, -11);
        $this->assertEquals(NumberValue::value('an_attribute', -11), $value);
    }

    public function test_it_generates_a_number_value_from_a_non_empty_string(): void
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->sut->createByCheckingData($attribute, null, null, '123.456');
        $this->assertEquals(NumberValue::value('an_attribute', '123.456'), $value);
    }

    public function test_it_creates_a_localizable_and_scopable_value_without_checking_the_data(): void
    {
        $attribute = $this->getAttribute(true, true);
        $value = $this->sut->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', 1234567890);
        $this->assertEquals(NumberValue::scopableLocalizableValue('an_attribute', 1234567890, 'ecommerce', 'fr_FR'), $value);
    }

    public function test_it_creates_a_localizable_value_without_checking_the_data(): void
    {
        $attribute = $this->getAttribute(true, false);
        $value = $this->sut->createWithoutCheckingData($attribute, null, 'fr_FR', 1234567890);
        $this->assertEquals(NumberValue::localizableValue('an_attribute', 1234567890, 'fr_FR'), $value);
    }

    public function test_it_creates_a_scopable_value_without_checking_the_data(): void
    {
        $attribute = $this->getAttribute(false, true);
        $value = $this->sut->createWithoutCheckingData($attribute, 'ecommerce', null, 1234567890);
        $this->assertEquals(NumberValue::scopableValue('an_attribute', 1234567890, 'ecommerce'), $value);
    }

    public function test_it_creates_a_non_localizable_and_non_scopable_value_without_checking_the_data(): void
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->sut->createWithoutCheckingData($attribute, null, null, 1234567890);
        $this->assertEquals(NumberValue::value('an_attribute', 1234567890), $value);
    }

    public function test_it_creates_a_non_localizable_and_non_scopable_value_without_checking_the_data_with_space(): void
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->sut->createWithoutCheckingData($attribute, null, null, ' 1234567890');
        $this->assertEquals(NumberValue::value('an_attribute', 1234567890), $value);
    }

    public function test_it_throws_an_exception_if_it_is_not_a_scalar(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->createByCheckingData($this->getAttribute(true, true),
                    'ecommerce',
                    'fr_FR',
                    new \stdClass());
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
            return new Attribute('an_attribute', AttributeTypes::NUMBER, [], $isLocalizable, $isScopable, null, null, false, 'decimal', []);
        }
}
