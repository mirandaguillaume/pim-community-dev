<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\OptionsValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class OptionsValueFactoryTest extends TestCase
{
    private OptionsValueFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new OptionsValueFactory();
    }

    public function test_it_is_a_read_value_factory(): void
    {
        $this->assertInstanceOf(ValueFactory::class, $this->sut);
    }

    public function test_it_supports_multi_select_attribute_type(): void
    {
        $this->assertSame(AttributeTypes::OPTION_MULTI_SELECT, $this->sut->supportedAttributeType());
    }

    public function test_it_does_not_support_null(): void
    {
        $this->expectException(\Throwable::class);
        $this->sut->createByCheckingData($this->getAttribute(true, true),
                    'ecommerce',
                    'fr_FR',
                    null);
    }

    public function test_it_creates_a_localizable_and_scopable_value(): void
    {
        $attribute = $this->getAttribute(true, true);
        $value = $this->sut->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', ['michel', 'sardou']);
        $this->assertEquals(OptionsValue::scopableLocalizableValue('an_attribute', ['michel', 'sardou'], 'ecommerce', 'fr_FR'), $value);
    }

    public function test_it_creates_a_localizable_value(): void
    {
        $attribute = $this->getAttribute(true, false);
        $value = $this->sut->createWithoutCheckingData($attribute, null, 'fr_FR', ['michel', 'sardou']);
        $this->assertEquals(OptionsValue::localizableValue('an_attribute', ['michel', 'sardou'], 'fr_FR'), $value);
    }

    public function test_it_creates_a_scopable_value(): void
    {
        $attribute = $this->getAttribute(false, true);
        $value = $this->sut->createWithoutCheckingData($attribute, 'ecommerce', null, ['michel', 'sardou']);
        $this->assertEquals(OptionsValue::scopableValue('an_attribute', ['michel', 'sardou'], 'ecommerce'), $value);
    }

    public function test_it_creates_a_non_localizable_and_non_scopable_value(): void
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->sut->createWithoutCheckingData($attribute, null, null, ['michel', 'sardou']);
        $this->assertEquals(OptionsValue::value('an_attribute', ['michel', 'sardou']), $value);
    }

    public function test_it_sorts_the_result(): void
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->sut->createWithoutCheckingData($attribute, null, null, ['sardou', 'michel']);
        $this->assertEquals(OptionsValue::value('an_attribute', ['michel', 'sardou']), $value);
    }

    public function test_it_throws_an_exception_if_it_is_not_an_array(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->createByCheckingData($this->getAttribute(true, true),
                        null,
                        null,
                        'foo');
    }

    public function test_it_throws_an_exception_if_not_an_array_of_string(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->createByCheckingData($this->getAttribute(true, true),
                        null,
                        null,
                        [new \stdClass()]);
    }

    public function test_it_throws_an_exception_if_one_of_the_options_is_empty(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->createByCheckingData($this->getAttribute(true, true),
                        null,
                        null,
                        [""]);
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
            return new Attribute('an_attribute', AttributeTypes::OPTION_MULTI_SELECT, [], $isLocalizable, $isScopable, null, null, false, 'options', [], $value);
        }
}
