<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\DateValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class DateValueUserIntentFactoryTest extends TestCase
{
    private DateValueUserIntentFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new DateValueUserIntentFactory();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(DateValueUserIntentFactory::class, $this->sut);
    }

    public function test_it_returns_set_date_user_intent(): void
    {
        $this->assertEquals(new SetDateValue(
            'a_date',
            null,
            null,
            \DateTimeImmutable::createFromFormat('Y-m-d', '2022-05-20')
        ), $this->sut->create(AttributeTypes::DATE, 'a_date', [
                    'data' => '2022-05-20',
                    'locale' => null,
                    'scope' => null,
                ]));
        $this->assertEquals(new SetDateValue(
            'a_date',
            null,
            null,
            \DateTimeImmutable::createFromFormat('Y-m-d', '2022-05-20')
        ), $this->sut->create(AttributeTypes::DATE, 'a_date', [
                    'data' => '2022-05-20T00:00:00+00:00',
                    'locale' => null,
                    'scope' => null,
                ]));
    }

    public function test_it_returns_clear_value(): void
    {
        $this->assertEquals(new ClearValue('a_date', 'ecommerce', 'fr_FR'), $this->sut->create(AttributeTypes::DATE, 'a_date', [
                    'data' => null,
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                ]));
        $this->assertEquals(new ClearValue('a_date', 'ecommerce', 'fr_FR'), $this->sut->create(AttributeTypes::DATE, 'a_date', [
                    'data' => '',
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                ]));
    }

    public function test_it_throws_an_exception_if_data_is_not_valid(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::DATE, 'a_date', ['value']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::DATE, 'a_date', ['data' => 'coucou', 'locale' => 'fr_FR']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::DATE, 'a_date', ['data' => 'coucou', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::DATE, 'a_date', ['locale' => 'fr_FR', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::DATE, 'a_date', ['data' => [], 'locale' => 'fr_FR', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyException::class);
        $this->sut->create(AttributeTypes::DATE, 'a_date', ['data' => 'je suis une date', 'locale' => 'fr_FR', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyException::class);
        $this->sut->create(AttributeTypes::DATE, 'a_date', ['data' => '2020-20-20', 'locale' => 'fr_FR', 'scope' => 'ecommerce']);
    }
}
