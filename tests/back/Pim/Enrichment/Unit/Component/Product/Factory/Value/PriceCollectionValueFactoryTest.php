<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\PriceCollectionValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class PriceCollectionValueFactoryTest extends TestCase
{
    private PriceCollectionValueFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new PriceCollectionValueFactory();
    }

    public function test_it_is_a_read_value_factory(): void
    {
        $this->assertInstanceOf(ValueFactory::class, $this->sut);
    }

    public function test_it_supports_price_collection_attribute_type(): void
    {
        $this->assertSame(AttributeTypes::PRICE_COLLECTION, $this->sut->supportedAttributeType());
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
        $priceCollection = new PriceCollection([new ProductPrice(5, 'EUR'), new ProductPrice(5, 'USD')]);
        $attribute = $this->getAttribute(true, true);
        $value = $this->sut->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', [['amount' => 5, 'currency' => 'EUR'], ['amount' => 5, 'currency' => 'USD']]);
        $this->assertEquals(PriceCollectionValue::scopableLocalizableValue('an_attribute', $priceCollection, 'ecommerce', 'fr_FR'), $value);
    }

    public function test_it_creates_a_localizable_value(): void
    {
        $priceCollection = new PriceCollection([new ProductPrice(5, 'EUR'), new ProductPrice(5, 'USD')]);
        $attribute = $this->getAttribute(true, false);
        $value = $this->sut->createWithoutCheckingData($attribute, null, 'fr_FR', [['amount' => 5, 'currency' => 'EUR'], ['amount' => 5, 'currency' => 'USD']]);
        $this->assertEquals(PriceCollectionValue::localizableValue('an_attribute', $priceCollection, 'fr_FR'), $value);
    }

    public function test_it_creates_a_scopable_value(): void
    {
        $priceCollection = new PriceCollection([new ProductPrice(5, 'EUR'), new ProductPrice(5, 'USD')]);
        $attribute = $this->getAttribute(false, true);
        $value = $this->sut->createWithoutCheckingData($attribute, 'ecommerce', null, [['amount' => 5, 'currency' => 'EUR'], ['amount' => 5, 'currency' => 'USD']]);
        $this->assertEquals(PriceCollectionValue::scopableValue('an_attribute', $priceCollection, 'ecommerce'), $value);
    }

    public function test_it_creates_a_non_localizable_and_non_scopable_value(): void
    {
        $priceCollection = new PriceCollection([new ProductPrice(5, 'EUR'), new ProductPrice(5, 'USD')]);
        $attribute = $this->getAttribute(false, false);
        $value = $this->sut->createWithoutCheckingData($attribute, null, null, [['amount' => 5, 'currency' => 'EUR'], ['amount' => 5, 'currency' => 'USD']]);
        $this->assertEquals(PriceCollectionValue::value('an_attribute', $priceCollection), $value);
    }

    public function test_it_sorts_the_result(): void
    {
        $priceCollection = new PriceCollection([new ProductPrice(5, 'EUR'), new ProductPrice(5, 'USD')]);
        $attribute = $this->getAttribute(false, false);
        $value = $this->sut->createWithoutCheckingData($attribute, null, null, [['amount' => 5, 'currency' => 'USD'], ['amount' => 5, 'currency' => 'EUR']]);
        $this->assertEquals(PriceCollectionValue::value('an_attribute', $priceCollection), $value);
    }

    public function test_it_throws_an_exception_if_it_is_not_an_array_of_amount_currency(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->createByCheckingData($this->getAttribute(false, false), null, null, new \stdClass());
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
            return new Attribute('an_attribute', AttributeTypes::PRICE_COLLECTION, [], $isLocalizable, $isScopable, null, null, false, 'prices', [], $value);
        }
}
