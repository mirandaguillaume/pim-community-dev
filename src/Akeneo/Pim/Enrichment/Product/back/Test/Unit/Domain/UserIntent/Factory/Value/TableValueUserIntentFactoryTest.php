<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTableValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\TableValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class TableValueUserIntentFactoryTest extends TestCase
{
    private TableValueUserIntentFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new TableValueUserIntentFactory();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(TableValueUserIntentFactory::class, $this->sut);
    }

    public function test_it_returns_set_table_user_intent(): void
    {
        $this->assertEquals(new SetTableValue('a_table', null, null, [['average_nutritional_value' => 'carbohydrate', 'per_100_g' => '100']]), $this->sut->create(AttributeTypes::TABLE, 'a_table', [
                    'data' => [['average_nutritional_value' => 'carbohydrate', 'per_100_g' => '100']],
                    'locale' => null,
                    'scope' => null,
                ]));
    }

    public function test_it_returns_clear_value_user_intent(): void
    {
        $this->assertEquals(new ClearValue('a_table', null, null), $this->sut->create(AttributeTypes::TABLE, 'a_table', [
                    'data' => null,
                    'locale' => null,
                    'scope' => null,
                ]));
    }

    public function test_it_throws_an_exception_if_data_is_not_valid(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::TABLE, 'a_table', ['value']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::TABLE, 'a_table', ['data' => 'coucou', 'locale' => 'fr_FR']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::TABLE, 'a_table', ['locale' => 'fr_FR', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::TABLE, 'a_table', ['data' => 'coucou', 'locale' => 'fr_FR', 'scope' => 'ecommerce']);
    }
}
