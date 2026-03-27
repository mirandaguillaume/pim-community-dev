<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject\Attribute;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use PHPUnit\Framework\TestCase;

class AttributeAdditionalPropertiesTest extends TestCase
{
    public function test_it_creates_from_empty_array(): void
    {
        $props = AttributeAdditionalProperties::fromArray([]);
        $this->assertSame([], $props->normalize());
    }

    public function test_it_creates_from_valid_array(): void
    {
        $props = AttributeAdditionalProperties::fromArray(['key1' => 'value1', 'key2' => 'value2']);
        $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $props->normalize());
    }

    public function test_it_rejects_non_string_values(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AttributeAdditionalProperties::fromArray(['key' => 123]);
    }

    public function test_it_rejects_empty_string_keys(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AttributeAdditionalProperties::fromArray(['' => 'value']);
    }

    public function test_it_gets_additional_property(): void
    {
        $props = AttributeAdditionalProperties::fromArray(['foo' => 'bar']);
        $this->assertSame('bar', $props->getAdditionalProperty('foo'));
    }

    public function test_it_returns_null_for_missing_property(): void
    {
        $props = AttributeAdditionalProperties::fromArray([]);
        $this->assertNull($props->getAdditionalProperty('missing'));
    }

    public function test_it_sets_additional_property(): void
    {
        $props = AttributeAdditionalProperties::fromArray([]);
        $props->setAdditionalProperty('new_key', 'new_value');
        $this->assertSame('new_value', $props->getAdditionalProperty('new_key'));
    }

    public function test_has_additional_property(): void
    {
        $props = AttributeAdditionalProperties::fromArray(['exists' => 'yes']);
        $this->assertTrue($props->hasAdditionalProperty('exists'));
        $this->assertFalse($props->hasAdditionalProperty('missing'));
    }
}
