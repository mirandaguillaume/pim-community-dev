<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceDataValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\StringValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class StringValueUserIntentFactoryTest extends TestCase
{
    private StringValueUserIntentFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new StringValueUserIntentFactory();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(StringValueUserIntentFactory::class, $this->sut);
    }

    public function test_it_returns_set_text_user_intent(): void
    {
        $this->assertEquals(new SetTextValue('a_text', null, null, 'coucou'), $this->sut->create(AttributeTypes::TEXT, 'a_text', [
                    'data' => 'coucou',
                    'locale' => null,
                    'scope' => null,
                ]));
    }

    public function test_it_returns_set_text_area_user_intent(): void
    {
        $this->assertEquals(new SetTextareaValue('a_textarea', null, null, '<p>coucou</p>'), $this->sut->create(AttributeTypes::TEXTAREA, 'a_textarea', [
                    'data' => '<p>coucou</p>',
                    'locale' => null,
                    'scope' => null,
                ]));
    }

    public function test_it_returns_set_simple_select_user_intent(): void
    {
        $this->assertEquals(new SetSimpleSelectValue('a_simple_select', null, null, 'coucou'), $this->sut->create(AttributeTypes::OPTION_SIMPLE_SELECT, 'a_simple_select', [
                    'data' => 'coucou',
                    'locale' => null,
                    'scope' => null,
                ]));
    }

    public function test_it_returns_set_simple_reference_entity_user_intent(): void
    {
        $this->assertEquals(new SetSimpleReferenceEntityValue('a_simple_reference_entity', null, null, 'coucou'), $this->sut->create(AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT, 'a_simple_reference_entity', [
                    'data' => 'coucou',
                    'locale' => null,
                    'scope' => null,
                ]));
    }

    public function test_it_returns_set_simple_reference_data_user_intent(): void
    {
        $this->assertEquals(new SetSimpleReferenceDataValue('a_simple_reference_data', null, null, 'coucou'), $this->sut->create(AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT, 'a_simple_reference_data', [
                    'data' => 'coucou',
                    'locale' => null,
                    'scope' => null,
                ]));
    }

    public function test_it_returns_clear_value(): void
    {
        $this->assertEquals(new ClearValue('a_text', 'ecommerce', 'fr_FR'), $this->sut->create(AttributeTypes::TEXT, 'a_text', [
                    'data' => null,
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                ]));
        $this->assertEquals(new ClearValue('a_text', 'ecommerce', 'fr_FR'), $this->sut->create(AttributeTypes::TEXT, 'a_text', [
                    'data' => '',
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                ]));
    }

    public function test_it_throws_an_exception_if_data_is_not_valid(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::TEXT, 'a_text', ['value']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::TEXT, 'a_text', ['data' => 'coucou', 'locale' => 'fr_FR']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::TEXT, 'a_text', ['data' => 'coucou', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::TEXT, 'a_text', ['locale' => 'fr_FR', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::TEXT, 'a_text', ['data' => [], 'locale' => 'fr_FR', 'scope' => 'ecommerce']);
    }
}
