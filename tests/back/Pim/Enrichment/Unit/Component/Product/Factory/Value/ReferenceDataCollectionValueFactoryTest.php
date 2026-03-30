<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ReferenceDataCollectionValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class ReferenceDataCollectionValueFactoryTest extends TestCase
{
    private ReferenceDataCollectionValueFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new ReferenceDataCollectionValueFactory();
    }

    public function test_it_is_a_read_value_factory(): void
    {
        $this->assertInstanceOf(ValueFactory::class, $this->sut);
    }

    public function test_it_supports_reference_data_attribute_type(): void
    {
        $this->assertSame(AttributeTypes::REFERENCE_DATA_MULTI_SELECT, $this->sut->supportedAttributeType());
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
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', ['blue', 'green']);
        $value->shouldBeLike(ReferenceDataCollectionValue::scopableLocalizableValue('an_attribute', ['blue', 'green'], 'ecommerce', 'fr_FR'));
    }

    public function test_it_creates_a_localizable_value(): void
    {
        $attribute = $this->getAttribute(true, false);
        $value = $this->createWithoutCheckingData($attribute, null, 'fr_FR', ['blue', 'green']);
        $value->shouldBeLike(ReferenceDataCollectionValue::localizableValue('an_attribute', ['blue', 'green'], 'fr_FR'));
    }

    public function test_it_creates_a_scopable_value(): void
    {
        $attribute = $this->getAttribute(false, true);
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', null, ['blue', 'green']);
        $value->shouldBeLike(ReferenceDataCollectionValue::scopableValue('an_attribute', ['blue', 'green'], 'ecommerce'));
    }

    public function test_it_creates_a_non_localizable_and_non_scopable_value(): void
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->createWithoutCheckingData($attribute, null, null, ['blue', 'green']);
        $value->shouldBeLike(ReferenceDataCollectionValue::value('an_attribute', ['blue', 'green']));
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
            return new Attribute('an_attribute', AttributeTypes::REFERENCE_DATA_MULTI_SELECT, ['reference_data_name' => 'color'], $isLocalizable, $isScopable, null, null, false, 'reference_data_options', []);
        }
}
