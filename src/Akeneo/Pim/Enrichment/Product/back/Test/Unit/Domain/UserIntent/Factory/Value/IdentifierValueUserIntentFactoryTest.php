<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\IdentifierValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class IdentifierValueUserIntentFactoryTest extends TestCase
{
    private IdentifierValueUserIntentFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new IdentifierValueUserIntentFactory();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(IdentifierValueUserIntentFactory::class, $this->sut);
    }

    public function test_it_returns_set_identifier_user_intent(): void
    {
        $this->assertEquals(new SetIdentifierValue('my_identifier', 'my_sku'), $this->sut->create(AttributeTypes::IDENTIFIER, 'my_identifier', ['data' => 'my_sku']));
    }

    public function test_it_throws_an_error_if_data_is_not_valid(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::IDENTIFIER, 'my_identifier', 'coucou');
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::IDENTIFIER, 'my_identifier', ['data' => []]);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::IDENTIFIER, 'my_identifier', ['coucou']);
    }
}
