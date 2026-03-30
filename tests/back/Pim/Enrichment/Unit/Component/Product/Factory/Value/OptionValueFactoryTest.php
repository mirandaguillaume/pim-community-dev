<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\OptionValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class OptionValueFactoryTest extends TestCase
{
    private OptionValueFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new OptionValueFactory();
    }

    public function test_it_is_a_read_value_factory(): void
    {
        $this->assertInstanceOf(ValueFactory::class, $this->sut);
    }

    public function test_it_supports_simple_select_attribute_type(): void
    {
        $this->assertSame(AttributeTypes::OPTION_SIMPLE_SELECT, $this->sut->supportedAttributeType());
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
                $value = $this->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', 'michel');
        $value->shouldBeLike(OptionValue::scopableLocalizableValue('an_attribute', 'michel', 'ecommerce', 'fr_FR'));
    }

    public function test_it_creates_a_localizable_value(): void
    {
        $attribute = $this->getAttribute(true, false);
        /** @var ScalarValue $value */
                $value = $this->createWithoutCheckingData($attribute, null, 'fr_FR', 'michel');
        $value->shouldBeLike(OptionValue::localizableValue('an_attribute', 'michel', 'fr_FR'));
    }

    public function test_it_creates_a_scopable_value(): void
    {
        $attribute = $this->getAttribute(false, true);
        /** @var ScalarValue $value */
                $value = $this->createWithoutCheckingData($attribute, 'ecommerce', null, 'michel');
        $value->shouldBeLike(OptionValue::scopableValue('an_attribute', 'michel', 'ecommerce'));
    }

    public function test_it_creates_a_non_localizable_and_non_scopable_value(): void
    {
        $attribute = $this->getAttribute(false, false);
        /** @var ScalarValue $value */
                $value = $this->createWithoutCheckingData($attribute, null, null, 'michel');
        $value->shouldBeLike(OptionValue::value('an_attribute', 'michel'));
    }

    public function test_it_throws_an_exception_if_it_is_not_a_string(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->createByCheckingData($this->getAttribute(false, false), null, null, new \stdClass());
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
            return new Attribute('an_attribute', AttributeTypes::OPTION_SIMPLE_SELECT, [], $isLocalizable, $isScopable, null, null, false, 'option', []);
        }
}
