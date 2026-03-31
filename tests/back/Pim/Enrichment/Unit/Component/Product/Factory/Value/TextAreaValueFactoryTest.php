<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\TextAreaValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class TextAreaValueFactoryTest extends TestCase
{
    private TextAreaValueFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new TextAreaValueFactory();
    }

    public function test_it_is_a_read_value_factory(): void
    {
        $this->assertInstanceOf(ValueFactory::class, $this->sut);
    }

    public function test_it_supports_textarea_attribute_type(): void
    {
        $this->assertSame(AttributeTypes::TEXTAREA, $this->sut->supportedAttributeType());
    }

    public function test_it_does_not_support_null(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->createByCheckingData($this->getAttribute(true, true),
                    'ecommerce',
                    'fr_FR',
                    null);
    }

    public function test_it_creates_a_localizable_and_scopable_value(): void
    {
        $attribute = $this->getAttribute(true, true);
        /** @var ScalarValue $value */
                $value = $this->sut->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', 'a_textarea');
        $this->assertEquals(ScalarValue::scopableLocalizableValue('an_attribute', 'a_textarea', 'ecommerce', 'fr_FR'), $value);
    }

    public function test_it_creates_a_localizable_value(): void
    {
        $attribute = $this->getAttribute(true, false);
        /** @var ScalarValue $value */
                $value = $this->sut->createWithoutCheckingData($attribute, null, 'fr_FR', 'a_textarea');
        $this->assertEquals(ScalarValue::localizableValue('an_attribute', 'a_textarea', 'fr_FR'), $value);
    }

    public function test_it_creates_a_scopable_value(): void
    {
        $attribute = $this->getAttribute(false, true);
        /** @var ScalarValue $value */
                $value = $this->sut->createWithoutCheckingData($attribute, 'ecommerce', null, 'a_textarea');
        $this->assertEquals(ScalarValue::scopableValue('an_attribute', 'a_textarea', 'ecommerce'), $value);
    }

    public function test_it_creates_a_non_localizable_and_non_scopable_value(): void
    {
        $attribute = $this->getAttribute(false, false);
        /** @var ScalarValue $value */
                $value = $this->sut->createWithoutCheckingData($attribute, null, null, 'a_textarea');
        $this->assertEquals(ScalarValue::value('an_attribute', 'a_textarea'), $value);
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
            return new Attribute('an_attribute', AttributeTypes::TEXTAREA, [], $isLocalizable, $isScopable, null, null, false, 'textarea', [], $value);
        }
}
