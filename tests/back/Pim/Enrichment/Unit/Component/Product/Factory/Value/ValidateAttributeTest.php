<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Exception\LocalizableAndNotScopableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\LocalizableAndScopableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\NotLocalizableAndNotScopableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\NotLocalizableAndScopableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValidateAttribute;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use PHPUnit\Framework\TestCase;

class ValidateAttributeTest extends TestCase
{
    private ValidateAttribute $sut;

    protected function setUp(): void
    {
        $this->sut = new ValidateAttribute();
    }

    public function test_it_is_a_validator_of_the_attribute(): void
    {
        $this->assertInstanceOf(ValidateAttribute::class, $this->sut);
    }

    public function test_it_is_valid_when_attribute_is_localizable_and_scopable_with_provided_locale_code_and_channel_code(): void
    {
        $this->sut->validate($this->getAttribute(true, true), 'ecommerce', 'en_US');
        $this->addToAssertionCount(1);
    }

    public function test_it_is_valid_when_attribute_is_localizable_with_provided_locale_code_and_null_channel_code(): void
    {
        $this->sut->validate($this->getAttribute(false, true), null, 'en_US');
        $this->addToAssertionCount(1);
    }

    public function test_it_is_valid_when_attribute_is_scopable_with_null_locale_code_and_provided_channel_code(): void
    {
        $this->sut->validate($this->getAttribute(false, true), null, 'en_US');
        $this->addToAssertionCount(1);
    }

    public function test_it_is_valid_when_attribute_is_neither_scopable_nor_localizable_with_null_locale_code_and_null_channel_code(): void
    {
        $this->sut->validate($this->getAttribute(false, false), null, null);
        $this->addToAssertionCount(1);
    }

    public function test_it_throws_an_exception_when_attribute_is_localizable_and_scopable_with_null_locale_code(): void
    {
        $this->expectException(LocalizableAndScopableAttributeException::class);
        $this->sut->validate($this->getAttribute(true, true),
                    'ecommerce',
                    null);
    }

    public function test_it_throws_an_exception_when_attribute_is_localizable_and_scopable_with_null_channel_code(): void
    {
        $this->expectException(LocalizableAndScopableAttributeException::class);
        $this->sut->validate($this->getAttribute(true, true),
                    null,
                    'en_US');
    }

    public function test_it_throws_an_exception_when_attribute_is_localizable_with_null_locale_code(): void
    {
        $this->expectException(LocalizableAndNotScopableAttributeException::class);
        $this->sut->validate($this->getAttribute(false, true),
                    null,
                    null);
    }

    public function test_it_throws_an_exception_when_attribute_is_localizable_with_provided_channel_code(): void
    {
        $this->expectException(LocalizableAndNotScopableAttributeException::class);
        $this->sut->validate($this->getAttribute(false, true),
                    'ecommerce',
                    'en_US');
    }

    public function test_it_throws_an_exception_when_attribute_is_scopable_with_provided_locale_code(): void
    {
        $this->expectException(NotLocalizableAndScopableAttributeException::class);
        $this->sut->validate($this->getAttribute(true, false),
                    'ecommerce',
                    'en_US');
    }

    public function test_it_throws_an_exception_when_attribute_is_scopable_with_null_channel_code(): void
    {
        $this->expectException(NotLocalizableAndScopableAttributeException::class);
        $this->sut->validate($this->getAttribute(true, false),
                    null,
                    'en_US');
    }

    public function test_it_throws_an_exception_when_attribute_is_neither_scopable_nor_localizable_with_provided_channel_code(): void
    {
        $this->expectException(NotLocalizableAndNotScopableAttributeException::class);
        $this->sut->validate($this->getAttribute(false, false),
                    'ecommerce',
                    null);
    }

    public function test_it_throws_an_exception_when_attribute_is_neither_scopable_nor_localizable_with_provided_locale_code(): void
    {
        $this->expectException(NotLocalizableAndNotScopableAttributeException::class);
        $this->sut->validate($this->getAttribute(false, false),
                    null,
                    'en_US');
    }

    private function getAttribute(bool $isScopable, bool $isLocalizable): Attribute
    {
            return new Attribute('an_attribute', AttributeTypes::BOOLEAN, [], $isLocalizable, $isScopable, null, null, false, 'boolean', []);
        }
}
