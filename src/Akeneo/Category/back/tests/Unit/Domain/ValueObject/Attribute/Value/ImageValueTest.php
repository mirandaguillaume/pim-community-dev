<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject\Attribute\Value;

use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\ImageValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\Value;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ImageValueTest extends TestCase
{
    private ImageValue $sut;

    protected function setUp(): void {}

    public function test_it_creates_text_value_from_applier(): void
    {
        $givenImageDataValue = [
            'size' => 12,
            'extension' => 'jpg',
            'file_path' => 'file/path/logo.jpg',
            'mime_type' => 'image/jpeg',
            'original_filename' => 'logo',
        ];
        $this->sut = ImageValue::fromApplier(
            $givenImageDataValue,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'hero_banner',
            'ecommerce',
            'en_US',
        );
        $this->assertInstanceOf(ImageValue::class, $this->sut);
        $this->assertInstanceOf(AbstractValue::class, $this->sut);
        $this->assertInstanceOf(Value::class, $this->sut);

        // Verify value is set
        $this->assertNotNull($this->sut->getValue());
        $this->assertSame(12, $this->sut->getValue()->getSize());
        $this->assertSame('jpg', $this->sut->getValue()->getExtension());
        $this->assertSame('file/path/logo.jpg', $this->sut->getValue()->getFilePath());
        $this->assertSame('image/jpeg', $this->sut->getValue()->getMimeType());
        $this->assertSame('logo', $this->sut->getValue()->getOriginalFilename());
    }

    public function test_it_creates_image_value_with_null_data(): void
    {
        $this->sut = ImageValue::fromApplier(
            null,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'hero_banner',
            'ecommerce',
            'en_US',
        );
        $this->assertInstanceOf(ImageValue::class, $this->sut);
        $this->assertNull($this->sut->getValue());
    }

    public function test_it_creates_image_value_with_empty_array_data(): void
    {
        $this->sut = ImageValue::fromApplier(
            [],
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'hero_banner',
            'ecommerce',
            'en_US',
        );
        $this->assertInstanceOf(ImageValue::class, $this->sut);
        $this->assertNull($this->sut->getValue());
    }

    public function test_it_creates_text_value_from_array(): void
    {
        $givenImageDataValue = [
            'size' => 12,
            'extension' => 'jpg',
            'file_path' => 'file/path/logo.jpg',
            'mime_type' => 'image/jpeg',
            'original_filename' => 'logo',
        ];
        $givenArray = [
            'data' => $givenImageDataValue,
            'type' => 'image',
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'attribute_code' => 'hero_banner|02274dac-e99a-4e1d-8f9b-794d4c3ba330',
        ];
        $this->sut = ImageValue::fromArray($givenArray);
        $this->assertInstanceOf(ImageValue::class, $this->sut);
        $this->assertInstanceOf(AbstractValue::class, $this->sut);
        $this->assertInstanceOf(Value::class, $this->sut);

        // Verify value is parsed correctly
        $this->assertNotNull($this->sut->getValue());
        $this->assertSame(12, $this->sut->getValue()->getSize());
    }

    public function test_from_array_with_null_data(): void
    {
        $givenArray = [
            'data' => null,
            'type' => 'image',
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'attribute_code' => 'hero_banner|02274dac-e99a-4e1d-8f9b-794d4c3ba330',
        ];
        $this->sut = ImageValue::fromArray($givenArray);
        $this->assertNull($this->sut->getValue());
    }

    public function test_from_array_with_empty_channel(): void
    {
        $givenArray = [
            'data' => ['size' => 1, 'extension' => 'jpg', 'file_path' => '/a.jpg', 'mime_type' => 'image/jpeg', 'original_filename' => 'a'],
            'type' => 'image',
            'channel' => null,
            'locale' => 'en_US',
            'attribute_code' => 'hero_banner|02274dac-e99a-4e1d-8f9b-794d4c3ba330',
        ];
        $this->sut = ImageValue::fromArray($givenArray);
        $this->assertNull($this->sut->getChannel());
    }

    public function test_from_array_with_empty_locale(): void
    {
        $givenArray = [
            'data' => ['size' => 1, 'extension' => 'jpg', 'file_path' => '/a.jpg', 'mime_type' => 'image/jpeg', 'original_filename' => 'a'],
            'type' => 'image',
            'channel' => 'ecommerce',
            'locale' => null,
            'attribute_code' => 'hero_banner|02274dac-e99a-4e1d-8f9b-794d4c3ba330',
        ];
        $this->sut = ImageValue::fromArray($givenArray);
        $this->assertNull($this->sut->getLocale());
    }

    public function test_it_throws_invalid_argument_exception_from_array(): void
    {
        $givenImageDataValue = [
            'size' => 12,
            'extension' => 'jpg',
            'file_path' => 'file/path/logo.jpg',
            'mime_type' => 'image/jpeg',
            'original_filename' => 'logo',
        ];
        $givenArray = [
            'data' => $givenImageDataValue,
            'type' => 'image',
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'attribute_code' => '',
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot find code and uuid.");
        ImageValue::fromArray($givenArray);
    }

    public function test_it_throws_invalid_argument_exception_from_array_single_segment(): void
    {
        $givenArray = [
            'data' => ['size' => 1, 'extension' => 'jpg', 'file_path' => '/a.jpg', 'mime_type' => 'image/jpeg', 'original_filename' => 'a'],
            'type' => 'image',
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'attribute_code' => 'no_separator_here',
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot find code and uuid.");
        ImageValue::fromArray($givenArray);
    }

    public function test_it_normalizes(): void
    {
        $givenImageDataValue = [
            'size' => 12,
            'extension' => 'jpg',
            'file_path' => 'file/path/logo.jpg',
            'mime_type' => 'image/jpeg',
            'original_filename' => 'logo',
        ];
        $this->sut = ImageValue::fromApplier(
            $givenImageDataValue,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'hero_banner',
            'ecommerce',
            'en_US',
        );
        $key = 'hero_banner' . AbstractValue::SEPARATOR . '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s%s',
            $key,
            AbstractValue::SEPARATOR . "ecommerce",
            AbstractValue::SEPARATOR . "en_US",
        );

        $normalized = $this->sut->normalize();
        $this->assertArrayHasKey($localeChannelKey, $normalized);
        $this->assertSame($givenImageDataValue, $normalized[$localeChannelKey]['data']);
        $this->assertSame('image', $normalized[$localeChannelKey]['type']);
        $this->assertSame('ecommerce', $normalized[$localeChannelKey]['channel']);
        $this->assertSame('en_US', $normalized[$localeChannelKey]['locale']);
        $this->assertSame($key, $normalized[$localeChannelKey]['attribute_code']);
    }

    public function test_it_normalizes_with_null_value(): void
    {
        $this->sut = ImageValue::fromApplier(
            null,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'hero_banner',
            'ecommerce',
            'en_US',
        );
        $normalized = $this->sut->normalize();
        $key = 'hero_banner' . AbstractValue::SEPARATOR . '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s%s',
            $key,
            AbstractValue::SEPARATOR . "ecommerce",
            AbstractValue::SEPARATOR . "en_US",
        );
        $this->assertArrayHasKey($localeChannelKey, $normalized);
        $this->assertNull($normalized[$localeChannelKey]['data']);
        $this->assertSame('image', $normalized[$localeChannelKey]['type']);
    }

    public function test_it_normalizes_with_no_locale(): void
    {
        $givenImageDataValue = [
            'size' => 12,
            'extension' => 'jpg',
            'file_path' => 'file/path/logo.jpg',
            'mime_type' => 'image/jpeg',
            'original_filename' => 'logo',
        ];
        $this->sut = ImageValue::fromApplier(
            $givenImageDataValue,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'hero_banner',
            'ecommerce',
            null,
        );
        $key = 'hero_banner' . AbstractValue::SEPARATOR . '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s',
            $key,
            AbstractValue::SEPARATOR . "ecommerce"
        );

        $normalized = $this->sut->normalize();
        $this->assertArrayHasKey($localeChannelKey, $normalized);
        $this->assertSame($givenImageDataValue, $normalized[$localeChannelKey]['data']);
        $this->assertSame('image', $normalized[$localeChannelKey]['type']);
        $this->assertSame('ecommerce', $normalized[$localeChannelKey]['channel']);
        $this->assertNull($normalized[$localeChannelKey]['locale']);
        $this->assertSame($key, $normalized[$localeChannelKey]['attribute_code']);
    }

    public function test_it_normalizes_with_no_channel(): void
    {
        $givenImageDataValue = [
            'size' => 12,
            'extension' => 'jpg',
            'file_path' => 'file/path/logo.jpg',
            'mime_type' => 'image/jpeg',
            'original_filename' => 'logo',
        ];
        $this->sut = ImageValue::fromApplier(
            $givenImageDataValue,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'hero_banner',
            null,
            'en_US',
        );
        $key = 'hero_banner' . AbstractValue::SEPARATOR . '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s',
            $key,
            AbstractValue::SEPARATOR . "en_US",
        );

        $normalized = $this->sut->normalize();
        $this->assertArrayHasKey($localeChannelKey, $normalized);
        $this->assertSame($givenImageDataValue, $normalized[$localeChannelKey]['data']);
        $this->assertSame('image', $normalized[$localeChannelKey]['type']);
        $this->assertNull($normalized[$localeChannelKey]['channel']);
        $this->assertSame('en_US', $normalized[$localeChannelKey]['locale']);
        $this->assertSame($key, $normalized[$localeChannelKey]['attribute_code']);
    }

    public function test_it_normalizes_with_no_channel_and_no_locale(): void
    {
        $givenImageDataValue = [
            'size' => 12,
            'extension' => 'jpg',
            'file_path' => 'file/path/logo.jpg',
            'mime_type' => 'image/jpeg',
            'original_filename' => 'logo',
        ];
        $this->sut = ImageValue::fromApplier(
            $givenImageDataValue,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'hero_banner',
            null,
            null,
        );
        $key = 'hero_banner' . AbstractValue::SEPARATOR . '02274dac-e99a-4e1d-8f9b-794d4c3ba330';

        $normalized = $this->sut->normalize();
        $this->assertArrayHasKey($key, $normalized);
        $this->assertSame($givenImageDataValue, $normalized[$key]['data']);
        $this->assertSame('image', $normalized[$key]['type']);
        $this->assertNull($normalized[$key]['channel']);
        $this->assertNull($normalized[$key]['locale']);
    }

    public function test_get_key_returns_code_separator_uuid(): void
    {
        $this->sut = ImageValue::fromApplier(
            ['size' => 1, 'extension' => 'jpg', 'file_path' => '/a.jpg', 'mime_type' => 'image/jpeg', 'original_filename' => 'a'],
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'hero_banner',
            'ecommerce',
            'en_US',
        );
        $this->assertSame('hero_banner|02274dac-e99a-4e1d-8f9b-794d4c3ba330', $this->sut->getKey());
    }

    public function test_get_uuid_returns_attribute_uuid(): void
    {
        $this->sut = ImageValue::fromApplier(
            ['size' => 1, 'extension' => 'jpg', 'file_path' => '/a.jpg', 'mime_type' => 'image/jpeg', 'original_filename' => 'a'],
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'hero_banner',
            'ecommerce',
            'en_US',
        );
        $this->assertSame('02274dac-e99a-4e1d-8f9b-794d4c3ba330', (string) $this->sut->getUuid());
    }

    public function test_get_code_returns_attribute_code(): void
    {
        $this->sut = ImageValue::fromApplier(
            ['size' => 1, 'extension' => 'jpg', 'file_path' => '/a.jpg', 'mime_type' => 'image/jpeg', 'original_filename' => 'a'],
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'hero_banner',
            'ecommerce',
            'en_US',
        );
        $this->assertSame('hero_banner', (string) $this->sut->getCode());
    }

    public function test_from_applier_with_empty_channel(): void
    {
        $this->sut = ImageValue::fromApplier(
            ['size' => 1, 'extension' => 'jpg', 'file_path' => '/a.jpg', 'mime_type' => 'image/jpeg', 'original_filename' => 'a'],
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'hero_banner',
            '',
            'en_US',
        );
        $this->assertNull($this->sut->getChannel());
    }

    public function test_from_applier_with_empty_locale(): void
    {
        $this->sut = ImageValue::fromApplier(
            ['size' => 1, 'extension' => 'jpg', 'file_path' => '/a.jpg', 'mime_type' => 'image/jpeg', 'original_filename' => 'a'],
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'hero_banner',
            'ecommerce',
            '',
        );
        $this->assertNull($this->sut->getLocale());
    }
}
