<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject\Attribute\Value;

use Akeneo\Category\Domain\ValueObject\Attribute\Value\ImageDataValue;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ImageDataValueTest extends TestCase
{
    private const VALID_DATA = [
        'size' => 1024,
        'extension' => 'png',
        'file_path' => '/images/test.png',
        'mime_type' => 'image/png',
        'original_filename' => 'test.png',
    ];

    public function test_it_creates_from_valid_array(): void
    {
        $value = ImageDataValue::fromArray(self::VALID_DATA);
        $this->assertInstanceOf(ImageDataValue::class, $value);
    }

    public function test_it_returns_size(): void
    {
        $value = ImageDataValue::fromArray(self::VALID_DATA);
        $this->assertSame(1024, $value->getSize());
    }

    public function test_it_returns_extension(): void
    {
        $value = ImageDataValue::fromArray(self::VALID_DATA);
        $this->assertSame('png', $value->getExtension());
    }

    public function test_it_returns_file_path(): void
    {
        $value = ImageDataValue::fromArray(self::VALID_DATA);
        $this->assertSame('/images/test.png', $value->getFilePath());
    }

    public function test_it_returns_mime_type(): void
    {
        $value = ImageDataValue::fromArray(self::VALID_DATA);
        $this->assertSame('image/png', $value->getMimeType());
    }

    public function test_it_returns_original_filename(): void
    {
        $value = ImageDataValue::fromArray(self::VALID_DATA);
        $this->assertSame('test.png', $value->getOriginalFilename());
    }

    public function test_it_normalizes(): void
    {
        $value = ImageDataValue::fromArray(self::VALID_DATA);
        $normalized = $value->normalize();
        $this->assertSame(self::VALID_DATA, $normalized);
        $this->assertSame(1024, $normalized['size']);
        $this->assertSame('png', $normalized['extension']);
        $this->assertSame('/images/test.png', $normalized['file_path']);
        $this->assertSame('image/png', $normalized['mime_type']);
        $this->assertSame('test.png', $normalized['original_filename']);
    }

    public function test_normalize_returns_all_keys(): void
    {
        $value = ImageDataValue::fromArray(self::VALID_DATA);
        $normalized = $value->normalize();
        $this->assertArrayHasKey('size', $normalized);
        $this->assertArrayHasKey('extension', $normalized);
        $this->assertArrayHasKey('file_path', $normalized);
        $this->assertArrayHasKey('mime_type', $normalized);
        $this->assertArrayHasKey('original_filename', $normalized);
        $this->assertCount(5, $normalized);
    }

    public function test_it_throws_when_missing_size(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = self::VALID_DATA;
        unset($data['size']);
        ImageDataValue::fromArray($data);
    }

    public function test_it_throws_when_size_is_not_integer(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = self::VALID_DATA;
        $data['size'] = 'not_an_int';
        ImageDataValue::fromArray($data);
    }

    public function test_it_throws_when_missing_extension(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = self::VALID_DATA;
        unset($data['extension']);
        ImageDataValue::fromArray($data);
    }

    public function test_it_throws_when_extension_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = self::VALID_DATA;
        $data['extension'] = '';
        ImageDataValue::fromArray($data);
    }

    public function test_it_throws_when_missing_file_path(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = self::VALID_DATA;
        unset($data['file_path']);
        ImageDataValue::fromArray($data);
    }

    public function test_it_throws_when_file_path_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = self::VALID_DATA;
        $data['file_path'] = '';
        ImageDataValue::fromArray($data);
    }

    public function test_it_throws_when_missing_mime_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = self::VALID_DATA;
        unset($data['mime_type']);
        ImageDataValue::fromArray($data);
    }

    public function test_it_throws_when_mime_type_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = self::VALID_DATA;
        $data['mime_type'] = '';
        ImageDataValue::fromArray($data);
    }

    public function test_it_throws_when_missing_original_filename(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = self::VALID_DATA;
        unset($data['original_filename']);
        ImageDataValue::fromArray($data);
    }

    public function test_it_throws_when_original_filename_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = self::VALID_DATA;
        $data['original_filename'] = '';
        ImageDataValue::fromArray($data);
    }

    public function test_normalize_round_trips(): void
    {
        $value = ImageDataValue::fromArray(self::VALID_DATA);
        $normalized = $value->normalize();
        $value2 = ImageDataValue::fromArray($normalized);
        $this->assertSame($value->getSize(), $value2->getSize());
        $this->assertSame($value->getExtension(), $value2->getExtension());
        $this->assertSame($value->getFilePath(), $value2->getFilePath());
        $this->assertSame($value->getMimeType(), $value2->getMimeType());
        $this->assertSame($value->getOriginalFilename(), $value2->getOriginalFilename());
    }

    public function test_it_preserves_different_values(): void
    {
        $data = [
            'size' => 999,
            'extension' => 'jpg',
            'file_path' => '/other/path.jpg',
            'mime_type' => 'image/jpeg',
            'original_filename' => 'photo.jpg',
        ];
        $value = ImageDataValue::fromArray($data);
        $this->assertSame(999, $value->getSize());
        $this->assertSame('jpg', $value->getExtension());
        $this->assertSame('/other/path.jpg', $value->getFilePath());
        $this->assertSame('image/jpeg', $value->getMimeType());
        $this->assertSame('photo.jpg', $value->getOriginalFilename());
    }
}
