<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\IdentifierValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class IdentifierValueFactoryTest extends TestCase
{
    private IdentifierValueFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new IdentifierValueFactory();
    }

    public function test_it_is_a_read_value_factory(): void
    {
        $this->assertInstanceOf(ValueFactory::class, $this->sut);
    }

    public function test_it_supports_identifier_attribute_type(): void
    {
        $this->assertSame(AttributeTypes::IDENTIFIER, $this->sut->supportedAttributeType());
    }

    public function test_it_does_not_support_null(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->createByCheckingData($this->getAttribute(true, true),
                    'ecommerce',
                    'fr_FR',
                    null);
    }

    public function test_it_cannot_create_a_localizable_and_scopable_value(): void
    {
        $attribute = $this->getAttribute(true, true);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createByCheckingData($attribute, 'ecommerce', 'fr_FR', 'my_identifier');
    }

    public function test_it_cannot_create_a_localizable_value(): void
    {
        $attribute = $this->getAttribute(true, false);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createByCheckingData($attribute, null, 'fr_FR', 'my_identifier');
    }

    public function test_it_cannot_create_a_scopable_value(): void
    {
        $attribute = $this->getAttribute(false, true);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createByCheckingData($attribute, 'ecommerce', null, 'my_identifier');
    }

    public function test_it_cannot_create_a_value_with_a_non_string_value(): void
    {
        $attribute = $this->getAttribute(false, false);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->createByCheckingData($attribute, null, null, 42);
    }

    public function test_it_cannot_create_a_value_with_an_empty_string_value(): void
    {
        $attribute = $this->getAttribute(false, false);
        $this->expectException(InvalidPropertyException::class);
        $this->sut->createByCheckingData($attribute, null, null, '');
    }

    public function test_it_throws_an_exception_if_it_is_not_a_string(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->createByCheckingData($this->getAttribute(true, true),
                    'ecommerce',
                    'fr_FR',
                    new \stdClass());
    }

    public function test_it_creates_a_value_for_the_main_identifier_attribute(): void
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->sut->createByCheckingData($attribute, null, null, 'my_identifier');
        $this->assertEquals(IdentifierValue::value('an_attribute', true, 'my_identifier'), $value);
    }

    public function test_it_creates_a_value_for_another_identifier_attribute(): void
    {
        $attribute = $this->getAttribute(false, false, false);
        $value = $this->sut->createByCheckingData($attribute, null, null, 'my_identifier');
        $this->assertEquals(IdentifierValue::value('an_attribute', false, 'my_identifier'), $value);
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable, bool $isMainIdentifier = true): Attribute
    {
            return new Attribute(
                'an_attribute',
                AttributeTypes::IDENTIFIER,
                [],
                $isLocalizable,
                $isScopable,
                null,
                null,
                false,
                'text',
                [],
                null,
                [],
                $isMainIdentifier
            , $value);
        }
}
