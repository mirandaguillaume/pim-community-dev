<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject;

use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextValue;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ValueCollectionTest extends TestCase
{
    private ValueCollection $sut;

    protected function setUp(): void
    {
    }

    public function testItGetsValue(): void
    {
        $givenValues = [
            TextValue::fromApplier(
                value: 'Meta shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US',
            ),
        ];
        $this->sut = ValueCollection::fromArray($givenValues);
        $this->assertTrue(is_a(ValueCollection::class, ValueCollection::class, true));
        $expectedValue = TextValue::fromApplier(
            value: 'Meta shoes',
            uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            code: 'seo_meta_description',
            channel: 'ecommerce',
            locale: 'en_US',
        );
        $this->assertEquals($expectedValue, $this->sut->getValue(
            attributeCode: 'seo_meta_description',
            attributeUuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            channel: 'ecommerce',
            localeCode: 'en_US',
        ));
    }

    public function testItReturnsNullWhenValueNotFound(): void
    {
        $givenValues = [
            TextValue::fromApplier(
                value: 'Meta shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US',
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

    public function testItCreatesValueOnEmptyValueCollectionWhenSettingValue(): void
    {
        $this->sut = ValueCollection::fromArray([]);
        $this->assertTrue(is_a(ValueCollection::class, ValueCollection::class, true));
        $expectedData = ValueCollection::fromArray([
            TextValue::fromApplier(
                value: 'Meta shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US',
            ),
        ]);
        $setValue = TextValue::fromApplier(
            value: 'Meta shoes',
            uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            code: 'seo_meta_description',
            channel: 'ecommerce',
            locale: 'en_US',
        );
        $this->assertEquals($expectedData, $this->sut->setValue($setValue));
    }

    public function testItAddsValueWhenSettingValue(): void
    {
        $givenValues = [
            TextValue::fromApplier(
                value: 'Description',
                uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
                code: 'description',
                channel: 'ecommerce',
                locale: 'en_US',
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
                    locale: 'en_US',
                ),
                TextValue::fromApplier(
                    value: 'Meta shoes',
                    uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                    code: 'seo_meta_description',
                    channel: 'ecommerce',
                    locale: 'en_US',
                ),
            ],
        );
        $this->assertEquals($expectedValues, $this->sut->setValue(
            TextValue::fromApplier(
                value: 'Meta shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US',
            ),
        ));
    }

    public function testItCouldNotHaveDuplicateAttributeCodesWhenSettingValue(): void
    {
        $givenValue = TextValue::fromApplier(
            value: 'My description',
            uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
            code: 'description',
            channel: 'ecommerce',
            locale: 'en_US',
        );
        $this->sut = ValueCollection::fromArray([$givenValue]);
        $this->assertTrue(is_a(ValueCollection::class, ValueCollection::class, true));
        $this->assertCount(1, $this->sut->setValue($givenValue));
    }

    public function testItUpdatesValuesOnDuplicateKeyWhenSettingValue(): void
    {
        $newValue = 'New Description Value';
        $givenValue = TextValue::fromApplier(
            value: 'Description',
            uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
            code: 'description',
            channel: 'ecommerce',
            locale: 'en_US',
        );
        $this->sut = ValueCollection::fromArray([$givenValue]);
        $this->assertTrue(is_a(ValueCollection::class, ValueCollection::class, true));
        $expectedValues = ValueCollection::fromArray([
            TextValue::fromApplier(
                value: $newValue,
                uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
                code: 'description',
                channel: 'ecommerce',
                locale: 'en_US',
            ),
        ]);
        $this->assertEquals($expectedValues, $this->sut->setValue(
            TextValue::fromApplier(
                value: $newValue,
                uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
                code: 'description',
                channel: 'ecommerce',
                locale: 'en_US',
            ),
        ));
    }

    public function testItNormalizes(): void
    {
        $givenDescriptionValue = TextValue::fromApplier(
            value: 'Nice shoes',
            uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
            code: 'description',
            channel: 'ecommerce',
            locale: 'en_US',
        );
        $givenSeoDescriptionValue = TextValue::fromApplier(
            value: 'Meta shoes',
            uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            code: 'seo_meta_description',
            channel: 'ecommerce',
            locale: 'en_US',
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

    public function testItGetsAllValues(): void
    {
        $givenDescriptionValue = TextValue::fromApplier(
            value: 'Nice shoes',
            uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
            code: 'description',
            channel: 'ecommerce',
            locale: 'en_US',
        );
        $givenSeoDescriptionValue = TextValue::fromApplier(
            value: 'Meta shoes',
            uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            code: 'seo_meta_description',
            channel: 'ecommerce',
            locale: 'en_US',
        );
        $this->sut = ValueCollection::fromArray([$givenDescriptionValue, $givenSeoDescriptionValue]);
        $expectedValues = [
            $givenDescriptionValue,
            $givenSeoDescriptionValue,
        ];
        $this->assertEquals($expectedValues, $this->sut->getValues());
    }

    public function testItThrowsInvalidArgumentExceptionWhenCreatingValueWithWrongFormat(): void
    {
        $givenValue = $this->createMock(AttributeText::class);

        $this->expectException(\InvalidArgumentException::class);
        ValueCollection::fromArray([$givenValue]);
    }

    public function testItThrowsInvalidArgumentExceptionWhenCreatingValueWithDuplicateValue(): void
    {
        $givenDuplicateValues = [
            TextValue::fromApplier(
                value: 'description',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US',
            ),
            TextValue::fromApplier(
                value: 'other description',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US',
            ),
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate value for seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d|ecommerce|en_US');
        ValueCollection::fromArray($givenDuplicateValues);
    }

    public function testItCreatesValueCollectionFromDatabase(): void
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
            locale: 'en_US',
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
