<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAttributeValueTypeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDateAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\DateValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\DateValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class DateValueFactoryTest extends TestCase
{
    private DateValueFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new DateValueFactory();
    }

    public function test_it_is_a_read_value_factory(): void
    {
        $this->assertInstanceOf(ValueFactory::class, $this->sut);
    }

    public function test_it_supports_date_attribute_types(): void
    {
        $this->assertSame(AttributeTypes::DATE, $this->sut->supportedAttributeType());
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
        $value = $this->createByCheckingData($attribute, 'ecommerce', 'fr_FR', '2019-05-21 07:29:04');
        $value->shouldBeLike(DateValue::scopableLocalizableValue('an_attribute', new \DateTime('2019-05-21 07:29:04'), 'ecommerce', 'fr_FR'));
    }

    public function test_it_creates_a_localizable_value(): void
    {
        $attribute = $this->getAttribute(true, false);
        $value = $this->createByCheckingData($attribute, null, 'fr_FR', '2019-05-21 07:29:04');
        $value->shouldBeLike(DateValue::localizableValue('an_attribute', new \DateTime('2019-05-21 07:29:04'), 'fr_FR'));
    }

    public function test_it_creates_a_scopable_value(): void
    {
        $attribute = $this->getAttribute(false, true);
        $value = $this->createByCheckingData($attribute, 'ecommerce', null, '2019-05-21 07:29:04');
        $value->shouldBeLike(DateValue::scopableValue('an_attribute', new \DateTime('2019-05-21 07:29:04'), 'ecommerce'));
    }

    public function test_it_creates_a_non_localizable_and_non_scopable_value(): void
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->createByCheckingData($attribute, null, null, '2019-05-21 07:29:04');
        $value->shouldBeLike(DateValue::value('an_attribute', new \DateTime('2019-05-21 07:29:04')));
    }

    public function test_it_creates_a_value_without_checking_type(): void
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->createWithoutCheckingData($attribute, null, null, '2019-05-21 07:29:04');
        $value->shouldBeLike(DateValue::value('an_attribute', new \DateTime('2019-05-21 07:29:04')));
    }

    public function test_it_throws_an_exception_when_provided_data_is_not_a_string(): void
    {
        $attribute = $this->getAttribute(false, false);
        $this->expectException(InvalidAttributeValueTypeException::class);
        $this->sut->createByCheckingData($attribute, 'ecommerce', 'en_US', []);
    }

    public function test_it_throws_an_exception_when_provided_data_is_not_a_date(): void
    {
        $attribute = $this->getAttribute(false, false);
        $this->expectException(InvalidDateAttributeException::class);
        $this->sut->createByCheckingData($attribute, 'ecommerce', 'en_US', 'foobar is no date');
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
            return new Attribute('an_attribute', AttributeTypes::DATE, [], $isLocalizable, $isScopable, null, null, false, 'date', []);
        }
}
