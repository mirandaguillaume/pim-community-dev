<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject\Attribute;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use PHPUnit\Framework\TestCase;

class AttributeAdditionalPropertiesTest extends TestCase
{
    public function testItCreatesFromEmptyArray(): void
    {
        $props = AttributeAdditionalProperties::fromArray([]);
        $this->assertSame([], $props->normalize());
    }

    public function testItCreatesFromValidArray(): void
    {
        $props = AttributeAdditionalProperties::fromArray(['key1' => 'value1', 'key2' => 'value2']);
        $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $props->normalize());
    }

    public function testItRejectsNonStringValues(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AttributeAdditionalProperties::fromArray(['key' => 123]);
    }

    public function testItRejectsEmptyStringKeys(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AttributeAdditionalProperties::fromArray(['' => 'value']);
    }

    public function testItGetsAdditionalProperty(): void
    {
        $props = AttributeAdditionalProperties::fromArray(['foo' => 'bar']);
        $this->assertSame('bar', $props->getAdditionalProperty('foo'));
    }

    public function testItReturnsNullForMissingProperty(): void
    {
        $props = AttributeAdditionalProperties::fromArray([]);
        $this->assertNull($props->getAdditionalProperty('missing'));
    }

    public function testItSetsAdditionalProperty(): void
    {
        $props = AttributeAdditionalProperties::fromArray([]);
        $props->setAdditionalProperty('new_key', 'new_value');
        $this->assertSame('new_value', $props->getAdditionalProperty('new_key'));
    }

    public function testHasAdditionalProperty(): void
    {
        $props = AttributeAdditionalProperties::fromArray(['exists' => 'yes']);
        $this->assertTrue($props->hasAdditionalProperty('exists'));
        $this->assertFalse($props->hasAdditionalProperty('missing'));
    }
}
