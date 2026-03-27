<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject\Attribute\Value;

use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextAreaValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\Value;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TextAreaValueTest extends TestCase
{
    private TextAreaValue $sut;

    protected function setUp(): void {}

    public function test_it_creates_text_value_from_applier(): void
    {
        $this->sut = TextAreaValue::fromApplier(
            "Meta <p>shoes</p>",
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'seo_meta_description',
            'ecommerce',
            'en_US',
        );
        $this->assertTrue(is_a(TextAreaValue::class, TextAreaValue::class, true));
        $this->assertTrue(is_a(TextAreaValue::class, AbstractValue::class, true));
        $this->assertTrue(is_a(TextAreaValue::class, Value::class, true));
    }

    public function test_it_creates_text_value_from_array(): void
    {
        $givenArray = [
            'data' => "Meta <p>shoes</p>",
            'type' => 'textarea',
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'attribute_code' => 'seo_meta_description|02274dac-e99a-4e1d-8f9b-794d4c3ba330',
        ];
        $this->sut = TextAreaValue::fromArray($givenArray);
        $this->assertTrue(is_a(TextAreaValue::class, TextAreaValue::class, true));
        $this->assertTrue(is_a(TextAreaValue::class, AbstractValue::class, true));
        $this->assertTrue(is_a(TextAreaValue::class, Value::class, true));
    }

    public function test_it_throws_invalid_argument_exception_from_array(): void
    {
        $givenArray = [
            'data' => "Meta <p>shoes</p>",
            'type' => 'textarea',
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'attribute_code' => '',
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot find code and uuid.");
        TextAreaValue::fromArray($givenArray);
    }

    public function test_it_normalizes(): void
    {
        $this->sut = TextAreaValue::fromApplier(
            "Meta <p>shoes</p>",
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'seo_meta_description',
            'ecommerce',
            'en_US',
        );
        $key = 'seo_meta_description' . AbstractValue::SEPARATOR . '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s%s',
            $key,
            AbstractValue::SEPARATOR . "ecommerce",
            AbstractValue::SEPARATOR . "en_US",
        );
        $expectedValue = [
            $localeChannelKey => [
                'data' => "Meta <p>shoes</p>",
                'type' => 'textarea',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => $key,
            ],
        ];
        $this->assertEquals($expectedValue, $this->sut->normalize());
    }

    public function test_it_normalizes_with_no_locale(): void
    {
        $textareaValue = "Meta <p>shoes</p>";
        $this->sut = TextAreaValue::fromApplier(
            $textareaValue,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'seo_meta_description',
            'ecommerce',
            null,
        );
        $key = 'seo_meta_description' . AbstractValue::SEPARATOR . '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s',
            $key,
            AbstractValue::SEPARATOR . "ecommerce"
        );
        $expectedValue = [
            $localeChannelKey => [
                'data' => "Meta <p>shoes</p>",
                'type' => 'textarea',
                'channel' => 'ecommerce',
                'locale' => null,
                'attribute_code' => $key,
            ],
        ];
        $this->assertEquals($expectedValue, $this->sut->normalize());
    }

    public function test_it_normalizes_with_no_channel(): void
    {
        $textareaValue = "Meta <p>shoes</p>";
        $this->sut = TextAreaValue::fromApplier(
            $textareaValue,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'seo_meta_description',
            null,
            'en_US',
        );
        $key = 'seo_meta_description' . AbstractValue::SEPARATOR . '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s',
            $key,
            AbstractValue::SEPARATOR . "en_US",
        );
        $expectedValue = [
            $localeChannelKey => [
                'data' => "Meta <p>shoes</p>",
                'type' => 'textarea',
                'channel' => null,
                'locale' => 'en_US',
                'attribute_code' => $key,
            ],
        ];
        $this->assertEquals($expectedValue, $this->sut->normalize());
    }

    public function test_it_normalizes_with_no_value(): void
    {
        $this->sut = TextAreaValue::fromApplier(
            null,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'seo_meta_description',
            null,
            'en_US',
        );
        $key = 'seo_meta_description' . AbstractValue::SEPARATOR . '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s',
            $key,
            AbstractValue::SEPARATOR . "en_US",
        );
        $expectedValue = [
            $localeChannelKey => [
                'data' => null,
                'type' => 'textarea',
                'channel' => null,
                'locale' => 'en_US',
                'attribute_code' => $key,
            ],
        ];
        $this->assertEquals($expectedValue, $this->sut->normalize());
    }
}
