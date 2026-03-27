<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject;

use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextValue;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ValueCollectionTest extends TestCase
{
    private ValueCollection $sut;

    protected function setUp(): void {}

    public function test_it_gets_value(): void
    {
        $givenValues = [
            TextValue::fromApplier(
                value: 'Meta shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            ),
        ];
        $this->sut = ValueCollection::fromArray($givenValues);
        $this->assertTrue(is_a(ValueCollection::class, ValueCollection::class, true));
        $expectedValue = TextValue::fromApplier(
            value: 'Meta shoes',
            uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            code: 'seo_meta_description',
            channel: 'ecommerce',
            locale: 'en_US'
        );
        $this->assertEquals($expectedValue, $this->sut->getValue(
            attributeCode: 'seo_meta_description',
            attributeUuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            channel: 'ecommerce',
            localeCode: 'en_US',
        ));
    }

    public function test_it_returns_null_when_value_not_found(): void
    {
        $givenValues = [
            TextValue::fromApplier(
                value: 'Meta shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            ),
        ];
        $this->sut = ValueCollection::fromArray($givenValues);
        $this->assertTrue(is_a(ValueCollection::class, ValueCollection::class, true));
        $this->assertNull($this->sut->getValue(
            attributeCode: 'seo_keyword',
            attributeUuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            channel: 'ecommerce',
            localeCode: 'fr_FR',
        ));
    }

    public function test_it_creates_value_on_empty_value_collection_when_setting_value(): void
    {
        $this->sut = ValueCollection::fromArray([]);
        $this->assertTrue(is_a(ValueCollection::class, ValueCollection::class, true));
        $expectedData = ValueCollection::fromArray([
            TextValue::fromApplier(
                value: 'Meta shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            ),
        ]);
        $setValue = TextValue::fromApplier(
            value: 'Meta shoes',
            uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            code: 'seo_meta_description',
            channel: 'ecommerce',
            locale: 'en_US'
        );
        $this->assertEquals($expectedData, $this->sut->setValue($setValue));
    }

    public function test_it_adds_value_when_setting_value(): void
    {
        $givenValues = [
            TextValue::fromApplier(
                value: 'Description',
                uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
                code: 'description',
                channel: 'ecommerce',
                locale: 'en_US'
            ),
        ];
        $this->sut = ValueCollection::fromArray($givenValues);
        $this->assertTrue(is_a(ValueCollection::class, ValueCollection::class, true));
        $expectedValues = ValueCollection::fromArray(
            [
                TextValue::fromApplier(
                    value: 'Description',
                    uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
                    code: 'description',
                    channel: 'ecommerce',
                    locale: 'en_US'
                ),
                TextValue::fromApplier(
                    value: 'Meta shoes',
                    uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                    code: 'seo_meta_description',
                    channel: 'ecommerce',
                    locale: 'en_US'
                ),
            ]
        );
        $this->assertEquals($expectedValues, $this->sut->setValue(
            TextValue::fromApplier(
                value: 'Meta shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            )
        ));
    }

    public function test_it_could_not_have_duplicate_attribute_codes_when_setting_value(): void
    {
        $givenValue = TextValue::fromApplier(
            value: 'My description',
            uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
            code: 'description',
            channel: 'ecommerce',
            locale: 'en_US'
        );
        $this->sut = ValueCollection::fromArray([$givenValue]);
        $this->assertTrue(is_a(ValueCollection::class, ValueCollection::class, true));
        $this->assertCount(1, $this->sut->setValue($givenValue));
    }

    public function test_it_updates_values_on_duplicate_key_when_setting_value(): void
    {
        $newValue = 'New Description Value';
        $givenValue = TextValue::fromApplier(
            value: 'Description',
            uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
            code: 'description',
            channel: 'ecommerce',
            locale: 'en_US'
        );
        $this->sut = ValueCollection::fromArray([$givenValue]);
        $this->assertTrue(is_a(ValueCollection::class, ValueCollection::class, true));
        $expectedValues = ValueCollection::fromArray([
            TextValue::fromApplier(
                value: $newValue,
                uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
                code: 'description',
                channel: 'ecommerce',
                locale: 'en_US'
            ),
        ]);
        $this->assertEquals($expectedValues, $this->sut->setValue(
            TextValue::fromApplier(
                value: $newValue,
                uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
                code: 'description',
                channel: 'ecommerce',
                locale: 'en_US'
            )
        ));
    }

    public function test_it_normalizes(): void
    {
        $givenDescriptionValue = TextValue::fromApplier(
            value: 'Nice shoes',
            uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
            code: 'description',
            channel: 'ecommerce',
            locale: 'en_US'
        );
        $givenSeoDescriptionValue = TextValue::fromApplier(
            value: 'Meta shoes',
            uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            code: 'seo_meta_description',
            channel: 'ecommerce',
            locale: 'en_US'
        );
        $this->sut = ValueCollection::fromArray([$givenDescriptionValue, $givenSeoDescriptionValue]);
        $normalizedValueCollection = [
            'description|840fcd1a-f66b-4f0c-9bbd-596629732950|ecommerce|en_US' => [
                'data' => 'Nice shoes',
                'type' => 'text',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => 'description|840fcd1a-f66b-4f0c-9bbd-596629732950',
            ],
            'seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d|ecommerce|en_US' => [
                'data' => 'Meta shoes',
                'type' => 'text',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => 'seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d',
            ],

        ];
        $this->assertEquals($normalizedValueCollection, $this->sut->normalize());
    }

    public function test_it_gets_all_values(): void
    {
        $givenDescriptionValue = TextValue::fromApplier(
            value: 'Nice shoes',
            uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
            code: 'description',
            channel: 'ecommerce',
            locale: 'en_US'
        );
        $givenSeoDescriptionValue = TextValue::fromApplier(
            value: 'Meta shoes',
            uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            code: 'seo_meta_description',
            channel: 'ecommerce',
            locale: 'en_US'
        );
        $this->sut = ValueCollection::fromArray([$givenDescriptionValue, $givenSeoDescriptionValue]);
        $expectedValues = [
            $givenDescriptionValue,
            $givenSeoDescriptionValue,
        ];
        $this->assertEquals($expectedValues, $this->sut->getValues());
    }

    public function test_it_throws_invalid_argument_exception_when_creating_value_with_wrong_format(): void
    {
        $givenValue = $this->createMock(AttributeText::class);

        $this->expectException(\InvalidArgumentException::class);
        ValueCollection::fromArray([$givenValue]);
    }

    public function test_it_throws_invalid_argument_exception_when_creating_value_with_duplicate_value(): void
    {
        $givenDuplicateValues = [
            TextValue::fromApplier(
                value: 'description',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            ),
            TextValue::fromApplier(
                value: 'other description',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            ),
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Duplicate value for seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d|ecommerce|en_US");
        ValueCollection::fromArray($givenDuplicateValues);
    }

    public function test_it_creates_value_collection_from_database(): void
    {
        $givenDatabaseValues = [
            'seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d|ecommerce|en_us' => [
                'data' => 'Meta shoes',
                'type' => 'text',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => 'seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d',
            ],
        ];
        $expectedValue = TextValue::fromApplier(
            value: 'Meta shoes',
            uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            code: 'seo_meta_description',
            channel: 'ecommerce',
            locale: 'en_US'
        );
        $this->sut = ValueCollection::fromDatabase($givenDatabaseValues);
        $this->assertTrue(is_a(ValueCollection::class, ValueCollection::class, true));
        $this->assertCount(1, $this->sut->getValues());
        $this->assertEquals($expectedValue, $this->sut->getValue(
            'seo_meta_description',
            '69e251b3-b876-48b5-9c09-92f54bfb528d',
            'ecommerce',
            'en_US',
        ));
        $this->assertInstanceOf(TextValue::class, $this->sut->getValue(
            'seo_meta_description',
            '69e251b3-b876-48b5-9c09-92f54bfb528d',
            'ecommerce',
            'en_US',
        ));
    }
}
