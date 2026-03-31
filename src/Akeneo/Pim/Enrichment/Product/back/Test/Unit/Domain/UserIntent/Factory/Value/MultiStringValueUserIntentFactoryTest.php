<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetAssetValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFileValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceDataValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\MultiStringValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class MultiStringValueUserIntentFactoryTest extends TestCase
{
    private MultiStringValueUserIntentFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new MultiStringValueUserIntentFactory();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(MultiStringValueUserIntentFactory::class, $this->sut);
    }

    public function test_it_returns_set_multi_select_user_intent(): void
    {
        $this->assertEquals(new SetMultiSelectValue('a_multi_select', null, null, ['option1', 'option2']), $this->sut->create(AttributeTypes::OPTION_MULTI_SELECT, 'a_multi_select', [
                    'data' => ['option1', 'option2'],
                    'locale' => null,
                    'scope' => null,
                ]));
    }

    public function test_it_returns_set_multi_reference_entity_user_intent(): void
    {
        $this->assertEquals(new SetMultiReferenceEntityValue('a_multi_ref_entity', null, null, ['record1', 'record2']), $this->sut->create(AttributeTypes::REFERENCE_ENTITY_COLLECTION, 'a_multi_ref_entity', [
                    'data' => ['record1', 'record2'],
                    'locale' => null,
                    'scope' => null,
                ]));
    }

    public function test_it_returns_set_multi_reference_data_user_intent(): void
    {
        $this->assertEquals(new SetMultiReferenceDataValue('a_multi_ref_data', null, null, ['record1', 'record2']), $this->sut->create(AttributeTypes::REFERENCE_DATA_MULTI_SELECT, 'a_multi_ref_data', [
                    'data' => ['record1', 'record2'],
                    'locale' => null,
                    'scope' => null,
                ]));
    }

    public function test_it_returns_set_asset_collection_user_intent(): void
    {
        $this->assertEquals(new SetAssetValue('an_asset_collection', null, null, ['asset1', 'asset2']), $this->sut->create(AttributeTypes::ASSET_COLLECTION, 'an_asset_collection', [
                    'data' => ['asset1', 'asset2'],
                    'locale' => null,
                    'scope' => null,
                ]));
    }

    public function test_it_returns_clear_value(): void
    {
        $this->assertEquals(new ClearValue('a_multi_select', 'ecommerce', 'fr_FR'), $this->sut->create(AttributeTypes::OPTION_MULTI_SELECT, 'a_multi_select', [
                    'data' => [],
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                ]));
    }

    public function test_it_throws_an_exception_if_data_is_not_valid(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::OPTION_MULTI_SELECT, 'a_multi_select', ['value']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::OPTION_MULTI_SELECT, 'a_multi_select', ['data' => ['coucou'], 'locale' => 'fr_FR']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::OPTION_MULTI_SELECT, 'a_multi_select', ['data' => ['coucou'], 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::OPTION_MULTI_SELECT, 'a_multi_select', ['locale' => 'fr_FR', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::OPTION_MULTI_SELECT, 'a_multi_select', ['data' => 'coucou', 'locale' => 'fr_FR', 'scope' => 'ecommerce']);
    }
}
