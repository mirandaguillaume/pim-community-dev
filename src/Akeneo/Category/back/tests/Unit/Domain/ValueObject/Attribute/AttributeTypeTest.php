<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject\Attribute;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use PHPUnit\Framework\TestCase;

class AttributeTypeTest extends TestCase
{
    public function testItCreatesTextType(): void
    {
        $type = new AttributeType(AttributeType::TEXT);
        $this->assertSame('text', (string) $type);
    }

    public function testItCreatesTextareaType(): void
    {
        $type = new AttributeType(AttributeType::TEXTAREA);
        $this->assertSame('textarea', (string) $type);
    }

    public function testItCreatesRichTextType(): void
    {
        $type = new AttributeType(AttributeType::RICH_TEXT);
        $this->assertSame('richtext', (string) $type);
    }

    public function testItCreatesImageType(): void
    {
        $type = new AttributeType(AttributeType::IMAGE);
        $this->assertSame('image', (string) $type);
    }

    public function testItRejectsInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AttributeType('invalid');
    }

    public function testItRejectsEmptyType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AttributeType('');
    }
}
