<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\PriceCollectionValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class PriceCollectionValueUserIntentFactoryTest extends TestCase
{
    private PriceCollectionValueUserIntentFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new PriceCollectionValueUserIntentFactory();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(PriceCollectionValueUserIntentFactory::class, $this->sut);
    }

    public function test_it_returns_set_price_collection_user_intent(): void
    {
        $this->assertEquals(new SetPriceCollectionValue(
            'a_price',
            null,
            null,
            [
                        new PriceValue(20, 'EUR'),
                        new PriceValue(10, 'USD'),
                    ]
        ), $this->sut->create(AttributeTypes::PRICE_COLLECTION, 'a_price', [
                    'data' => [
                        ['amount' => 20, 'currency' => 'EUR'],
                        ['amount' => 10, 'currency' => 'USD'],
                    ],
                    'locale' => null,
                    'scope' => null,
                ]));
    }

    public function test_it_returns_clear_value(): void
    {
        $this->assertEquals(new ClearValue('a_price', 'ecommerce', 'fr_FR'), $this->sut->create(AttributeTypes::PRICE_COLLECTION, 'a_price', [
                    'data' => null,
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                ]));
        $this->assertEquals(new ClearValue('a_price', 'ecommerce', 'fr_FR'), $this->sut->create(AttributeTypes::PRICE_COLLECTION, 'a_price', [
                    'data' => [],
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                ]));
    }

    public function test_it_throws_an_exception_if_data_is_not_valid(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::METRIC, 'a_metric', ['value']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::METRIC, 'a_metric', ['data' => 'coucou', 'locale' => 'fr_FR']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::METRIC, 'a_metric', ['data' => 'coucou', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::METRIC, 'a_metric', ['locale' => 'fr_FR', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::METRIC, 'a_metric', ['data' => ['coucou'], 'locale' => 'fr_FR', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::METRIC, 'a_metric', ['data' => ['amount' => 20], 'locale' => 'fr_FR', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::METRIC, 'a_metric', ['data' => ['unit' => 'KILOMETER'], 'locale' => 'fr_FR', 'scope' => 'ecommerce']);
    }
}
