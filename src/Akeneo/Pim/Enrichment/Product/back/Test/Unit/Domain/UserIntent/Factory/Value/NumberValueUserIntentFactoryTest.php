<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\NumberValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class NumberValueUserIntentFactoryTest extends TestCase
{
    private NumberValueUserIntentFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new NumberValueUserIntentFactory();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(NumberValueUserIntentFactory::class, $this->sut);
    }

    public function test_it_returns_set_number_user_intent(): void
    {
        $this->assertEquals(new SetNumberValue('a_number', null, null, '10'), $this->sut->create(AttributeTypes::NUMBER, 'a_number', [
                    'data' => '10',
                    'locale' => null,
                    'scope' => null,
                ]));
        $this->assertEquals(new SetNumberValue('a_number', null, null, '10.02'), $this->sut->create(AttributeTypes::NUMBER, 'a_number', [
                    'data' => '10.02',
                    'locale' => null,
                    'scope' => null,
                ]));
    }

    public function test_it_returns_clear_value(): void
    {
        $this->assertEquals(new ClearValue('a_number', 'ecommerce', 'fr_FR'), $this->sut->create(AttributeTypes::NUMBER, 'a_number', [
                    'data' => null,
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                ]));
        $this->assertEquals(new ClearValue('a_number', 'ecommerce', 'fr_FR'), $this->sut->create(AttributeTypes::NUMBER, 'a_number', [
                    'data' => '',
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                ]));
    }
}
