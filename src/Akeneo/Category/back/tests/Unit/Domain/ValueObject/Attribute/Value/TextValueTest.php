<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject\Attribute\Value;

use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\Value;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TextValueTest extends TestCase
{
    private TextValue $sut;

    protected function setUp(): void
    {
    }

    public function testItCreatesTextValueFromApplier(): void
    {
        $this->sut = TextValue::fromApplier(
            'Meta shoes',
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'seo_meta_description',
            'ecommerce',
            'en_US',
        );
        $this->assertTrue(is_a(TextValue::class, TextValue::class, true));
        $this->assertTrue(is_a(TextValue::class, AbstractValue::class, true));
        $this->assertTrue(is_a(TextValue::class, Value::class, true));
    }

    public function testItCreatesTextValueFromArray(): void
    {
        $givenArray = [
            'data' => 'Meta shoes',
            'type' => 'text',
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'attribute_code' => 'seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d',
        ];
        $this->sut = TextValue::fromArray($givenArray);
        $this->assertTrue(is_a(TextValue::class, TextValue::class, true));
        $this->assertTrue(is_a(TextValue::class, AbstractValue::class, true));
        $this->assertTrue(is_a(TextValue::class, Value::class, true));
    }

    public function testItThrowsInvalidArgumentExceptionFromArray(): void
    {
        $givenArray = [
            'data' => 'Meta shoes',
            'type' => 'text',
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'attribute_code' => '',
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot find code and uuid.');
        TextValue::fromArray($givenArray);
    }

    public function testItNormalizes(): void
    {
        $this->sut = TextValue::fromApplier(
            'Meta shoes',
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'seo_meta_description',
            'ecommerce',
            'en_US',
        );
        $key = 'seo_meta_description'.AbstractValue::SEPARATOR.'02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s%s',
            $key,
            AbstractValue::SEPARATOR.'ecommerce',
            AbstractValue::SEPARATOR.'en_US',
        );
        $expectedValue = [
            $localeChannelKey => [
                'data' => 'Meta shoes',
                'type' => 'text',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => $key,
            ],
        ];
        $this->assertEquals($expectedValue, $this->sut->normalize());
    }

    public function testItNormalizesWithNoLocale(): void
    {
        $textValue = 'Meta shoes';
        $this->sut = TextValue::fromApplier(
            $textValue,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'seo_meta_description',
            'ecommerce',
            null,
        );
        $key = 'seo_meta_description'.AbstractValue::SEPARATOR.'02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s',
            $key,
            AbstractValue::SEPARATOR.'ecommerce',
        );
        $expectedValue = [
            $localeChannelKey => [
                'data' => 'Meta shoes',
                'type' => 'text',
                'channel' => 'ecommerce',
                'locale' => null,
                'attribute_code' => $key,
            ],
        ];
        $this->assertEquals($expectedValue, $this->sut->normalize());
    }

    public function testItNormalizesWithNoChannel(): void
    {
        $textValue = 'Meta shoes';
        $this->sut = TextValue::fromApplier(
            $textValue,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'seo_meta_description',
            null,
            'en_US',
        );
        $key = 'seo_meta_description'.AbstractValue::SEPARATOR.'02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s',
            $key,
            AbstractValue::SEPARATOR.'en_US',
        );
        $expectedValue = [
            $localeChannelKey => [
                'data' => 'Meta shoes',
                'type' => 'text',
                'channel' => null,
                'locale' => 'en_US',
                'attribute_code' => $key,
            ],
        ];
        $this->assertEquals($expectedValue, $this->sut->normalize());
    }

    public function testItNormalizesWithNoValue(): void
    {
        $this->sut = TextValue::fromApplier(
            null,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'seo_meta_description',
            null,
            'en_US',
        );
        $key = 'seo_meta_description'.AbstractValue::SEPARATOR.'02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s',
            $key,
            AbstractValue::SEPARATOR.'en_US',
        );
        $expectedValue = [
            $localeChannelKey => [
                'data' => null,
                'type' => 'text',
                'channel' => null,
                'locale' => 'en_US',
                'attribute_code' => $key,
            ],
        ];
        $this->assertEquals($expectedValue, $this->sut->normalize());
    }
}
