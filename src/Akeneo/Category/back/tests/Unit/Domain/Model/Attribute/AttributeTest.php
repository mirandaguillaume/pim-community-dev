<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\Model\Attribute;

use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\Model\Attribute\AttributeTextArea;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use PHPUnit\Framework\TestCase;

class AttributeTest extends TestCase
{
    private function createAttribute(string $typeString): Attribute
    {
        return Attribute::fromType(
            new AttributeType($typeString),
            AttributeUuid::fromString('e30177ee-d8e8-46a4-9491-ea6c3579e727'),
            new AttributeCode('test_attr'),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsScopable::fromBoolean(false),
            AttributeIsLocalizable::fromBoolean(false),
            LabelCollection::fromArray(['en_US' => 'Test']),
            TemplateUuid::fromString('b60bb301-33e3-43ef-8a2c-a95361b607c2'),
            AttributeAdditionalProperties::fromArray([]),
        );
    }

    public function test_it_creates_rich_text_attribute(): void
    {
        $attr = $this->createAttribute(AttributeType::RICH_TEXT);
        $this->assertInstanceOf(AttributeRichText::class, $attr);
        $this->assertSame('richtext', (string) $attr->getType());
    }

    public function test_it_creates_text_attribute(): void
    {
        $attr = $this->createAttribute(AttributeType::TEXT);
        $this->assertInstanceOf(AttributeText::class, $attr);
        $this->assertSame('text', (string) $attr->getType());
    }

    public function test_it_creates_image_attribute(): void
    {
        $attr = $this->createAttribute(AttributeType::IMAGE);
        $this->assertInstanceOf(AttributeImage::class, $attr);
        $this->assertSame('image', (string) $attr->getType());
    }

    public function test_it_creates_textarea_attribute(): void
    {
        $attr = $this->createAttribute(AttributeType::TEXTAREA);
        $this->assertInstanceOf(AttributeTextArea::class, $attr);
        $this->assertSame('textarea', (string) $attr->getType());
    }

    public function test_it_normalizes_text_attribute(): void
    {
        $attr = $this->createAttribute(AttributeType::TEXT);
        $normalized = $attr->normalize();
        $this->assertIsArray($normalized);
        $this->assertSame('e30177ee-d8e8-46a4-9491-ea6c3579e727', $normalized['uuid']);
        $this->assertSame('test_attr', $normalized['code']);
        $this->assertSame('text', $normalized['type']);
        $this->assertSame(1, $normalized['order']);
        $this->assertFalse($normalized['is_required']);
        $this->assertFalse($normalized['is_scopable']);
        $this->assertFalse($normalized['is_localizable']);
        $this->assertArrayHasKey('labels', $normalized);
        $this->assertArrayHasKey('template_uuid', $normalized);
        $this->assertArrayHasKey('additional_properties', $normalized);
    }

    public function test_it_normalizes_image_attribute(): void
    {
        $attr = $this->createAttribute(AttributeType::IMAGE);
        $normalized = $attr->normalize();
        $this->assertSame('image', $normalized['type']);
        $this->assertSame('test_attr', $normalized['code']);
    }

    public function test_it_normalizes_rich_text_attribute(): void
    {
        $attr = $this->createAttribute(AttributeType::RICH_TEXT);
        $normalized = $attr->normalize();
        $this->assertSame('richtext', $normalized['type']);
    }

    public function test_it_normalizes_textarea_attribute(): void
    {
        $attr = $this->createAttribute(AttributeType::TEXTAREA);
        $normalized = $attr->normalize();
        $this->assertSame('textarea', $normalized['type']);
    }

    public function test_get_uuid_returns_correct_value(): void
    {
        $attr = $this->createAttribute(AttributeType::TEXT);
        $this->assertSame('e30177ee-d8e8-46a4-9491-ea6c3579e727', (string) $attr->getUuid());
    }

    public function test_get_code_returns_correct_value(): void
    {
        $attr = $this->createAttribute(AttributeType::TEXT);
        $this->assertSame('test_attr', (string) $attr->getCode());
    }

    public function test_get_order_returns_correct_value(): void
    {
        $attr = $this->createAttribute(AttributeType::TEXT);
        $this->assertSame(1, $attr->getOrder()->intValue());
    }
}
