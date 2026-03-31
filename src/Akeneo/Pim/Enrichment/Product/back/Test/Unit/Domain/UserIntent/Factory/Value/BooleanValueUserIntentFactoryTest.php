<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\BooleanValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class BooleanValueUserIntentFactoryTest extends TestCase
{
    private BooleanValueUserIntentFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new BooleanValueUserIntentFactory();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(BooleanValueUserIntentFactory::class, $this->sut);
    }

    public function test_it_returns_set_boolean_user_intent(): void
    {
        $this->assertEquals(new SetBooleanValue('a_bool', null, null, true), $this->sut->create(AttributeTypes::BOOLEAN, 'a_bool', [
                    'data' => true,
                    'locale' => null,
                    'scope' => null,
                ]));
    }

    public function test_it_returns_clear_value(): void
    {
        $this->assertEquals(new ClearValue('a_bool', 'ecommerce', 'fr_FR'), $this->sut->create(AttributeTypes::BOOLEAN, 'a_bool', [
                    'data' => null,
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                ]));
        $this->assertEquals(new ClearValue('a_bool', 'ecommerce', 'fr_FR'), $this->sut->create(AttributeTypes::BOOLEAN, 'a_bool', [
                    'data' => '',
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                ]));
    }

    public function test_it_throws_an_exception_if_data_is_not_valid(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::BOOLEAN, 'a_bool', ['value']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::BOOLEAN, 'a_bool', ['data' => 'coucou', 'locale' => 'fr_FR']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::BOOLEAN, 'a_bool', ['data' => 'coucou', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::BOOLEAN, 'a_bool', ['locale' => 'fr_FR', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::BOOLEAN, 'a_bool', ['data' => 'je suis false', 'locale' => 'fr_FR', 'scope' => 'ecommerce']);
    }
}
