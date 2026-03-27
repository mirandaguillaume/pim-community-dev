<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject\Attribute;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use PHPUnit\Framework\TestCase;

class AttributeTypeTest extends TestCase
{
    public function test_it_creates_text_type(): void
    {
        $type = new AttributeType(AttributeType::TEXT);
        $this->assertSame('text', (string) $type);
    }

    public function test_it_creates_textarea_type(): void
    {
        $type = new AttributeType(AttributeType::TEXTAREA);
        $this->assertSame('textarea', (string) $type);
    }

    public function test_it_creates_rich_text_type(): void
    {
        $type = new AttributeType(AttributeType::RICH_TEXT);
        $this->assertSame('richtext', (string) $type);
    }

    public function test_it_creates_image_type(): void
    {
        $type = new AttributeType(AttributeType::IMAGE);
        $this->assertSame('image', (string) $type);
    }

    public function test_it_rejects_invalid_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AttributeType('invalid');
    }

    public function test_it_rejects_empty_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AttributeType('');
    }
}
