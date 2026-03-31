<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ReferenceDataValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class ReferenceDataValueFactoryTest extends TestCase
{
    private ReferenceDataValueFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new ReferenceDataValueFactory();
    }

    public function test_it_is_a_read_value_factory(): void
    {
        $this->assertInstanceOf(ValueFactory::class, $this->sut);
    }

    public function test_it_supports_reference_data_attribute_type(): void
    {
        $this->assertSame(AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT, $this->sut->supportedAttributeType());
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
        $value = $this->sut->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', 'blue');
        $this->assertEquals(ReferenceDataValue::scopableLocalizableValue('an_attribute', 'blue', 'ecommerce', 'fr_FR'), $value);
    }

    public function test_it_creates_a_localizable_value(): void
    {
        $attribute = $this->getAttribute(true, false);
        $value = $this->sut->createWithoutCheckingData($attribute, null, 'fr_FR', 'blue');
        $this->assertEquals(ReferenceDataValue::localizableValue('an_attribute', 'blue', 'fr_FR'), $value);
    }

    public function test_it_creates_a_scopable_value(): void
    {
        $attribute = $this->getAttribute(false, true);
        $value = $this->sut->createWithoutCheckingData($attribute, 'ecommerce', null, 'blue');
        $this->assertEquals(ReferenceDataValue::scopableValue('an_attribute', 'blue', 'ecommerce'), $value);
    }

    public function test_it_creates_a_non_localizable_and_non_scopable_value(): void
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->sut->createWithoutCheckingData($attribute, null, null, 'blue');
        $this->assertEquals(ReferenceDataValue::value('an_attribute', 'blue'), $value);
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
            return new Attribute('an_attribute', AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT, ['reference_data_name' => 'color'], $isLocalizable, $isScopable, null, null, false, 'reference_data_option', [], $value);
        }
}
