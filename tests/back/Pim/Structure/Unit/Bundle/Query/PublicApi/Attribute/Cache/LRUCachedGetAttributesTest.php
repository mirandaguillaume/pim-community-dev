<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Query\PublicApi\Attribute\Cache;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Cache\LRUCachedGetAttributes;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LRUCachedGetAttributesTest extends TestCase
{
    private GetAttributes|MockObject $getAttributes;
    private LRUCachedGetAttributes $sut;

    protected function setUp(): void
    {
        $this->getAttributes = $this->createMock(GetAttributes::class);
        $this->sut = new LRUCachedGetAttributes($this->getAttributes);
    }

    public function test_it_gets_attributes_by_doing_a_query_if_the_cache_is_not_hit(): void
    {
        $aText = new Attribute('a_text', AttributeTypes::TEXT, [], false, false, null, null, false, 'text', []);
        $aTextarea = new Attribute('a_textarea', AttributeTypes::TEXTAREA, [], false, false, null, null, false, 'textarea', []);
        $aBoolean = new Attribute('a_boolean', AttributeTypes::BOOLEAN, [], false, false, null, null, false, 'boolean', []);
        $this->getAttributes->method('forCodes')->with(['a_text', 'a_textarea', 'a_boolean'])->willReturn(['a_text' => $aText, 'a_textarea' => $aTextarea, 'a_boolean' => $aBoolean]);
        $this->assertSame(['a_text' => $aText, 'a_textarea' => $aTextarea, 'a_boolean' => $aBoolean], $this->sut->forCodes(['a_text', 'a_textarea', 'a_boolean']));
    }

    public function test_it_gets_attributes_from_the_cache_when_the_cache_is_hit(): void
    {
        $aText = new Attribute('a_text', AttributeTypes::TEXT, [], false, false, null, null, false, 'text', []);
        $aTextarea = new Attribute('a_textarea', AttributeTypes::TEXTAREA, [], false, false, null, null, false, 'textarea', []);
        $aBoolean = new Attribute('a_boolean', AttributeTypes::BOOLEAN, [], false, false, null, null, false, 'boolean', []);
        $this->getAttributes->method('forCodes')->with(['a_text', 'a_textarea', 'a_boolean', 'michel'])->willReturn(['a_text' => $aText, 'a_textarea' => $aTextarea, 'a_boolean' => $aBoolean, 'michel' => null]);
        $this->getAttributes->method('forCodes')->with(['a_text', 'a_textarea', 'a_boolean', 'michel']);
        $this->getAttributes->method('forCodes')->with([])->willReturn([]);
        $this->assertSame(['a_text' => $aText, 'a_textarea' => $aTextarea, 'a_boolean' => $aBoolean, 'michel' => null], $this->sut->forCodes(['a_text', 'a_textarea', 'a_boolean', 'michel']));
        $this->assertSame(['a_text' => $aText, 'a_textarea' => $aTextarea, 'a_boolean' => $aBoolean, 'michel' => null], $this->sut->forCodes(['a_text', 'a_textarea', 'a_boolean', 'michel']));
    }

    public function test_it_mixes_the_call_between_the_cache_and_the_non_cached(): void
    {
        $aText = new Attribute('a_text', AttributeTypes::TEXT, [], false, false, null, null, false, 'text', []);
        $aTextarea = new Attribute('a_textarea', AttributeTypes::TEXTAREA, [], false, false, null, null, false, 'textarea', []);
        $aBoolean = new Attribute('a_boolean', AttributeTypes::BOOLEAN, [], false, false, null, null, false, 'boolean', []);
        $this->getAttributes->method('forCodes')->with(['a_text', 'a_textarea', 'a_boolean'])->willReturn(['a_text' => $aText, 'a_textarea' => $aTextarea, 'a_boolean' => $aBoolean]);
        $this->getAttributes->method('forCodes')->with(['michel'])->willReturn(['michel' => null]);
        $this->assertSame(['a_text' => $aText, 'a_textarea' => $aTextarea, 'a_boolean' => $aBoolean], $this->sut->forCodes(['a_text', 'a_textarea', 'a_boolean']));
        $this->assertSame(['michel' => null, 'a_text' => $aText, 'a_textarea' => $aTextarea, 'a_boolean' => $aBoolean], $this->sut->forCodes(['a_text', 'a_textarea', 'a_boolean', 'michel']));
    }

    public function test_it_can_get_more_than_the_cache_size(): void
    {
        $attributes = [];
        for ($i = 0; $i < 1500; $i++) {
                    $attributeCode = "an_attribute_$i";
                    $attributes[$attributeCode] = new Attribute($attributeCode, AttributeTypes::TEXT, [], false, false, null, null, false, 'text', []);
                }
        $this->getAttributes->method('forCodes')->with(array_keys($attributes))->willReturn(array_values($attributes));
        $this->assertSame(array_values($attributes), $this->sut->forCodes(array_keys($attributes)));
    }

    public function test_it_clears_the_cache(): void
    {
        $aText = new Attribute('a_text', AttributeTypes::TEXT, [], false, false, null, null, false, 'text', []);
        $this->getAttributes->method('forCodes')->with(['a_text'])->willReturn(['a_text' => $aText]);
        $this->getAttributes->expects($this->exactly(2))->method('forCodes')->with(['a_text']);
        $this->assertSame(['a_text' => $aText], $this->sut->forCodes(['a_text']));
        $this->sut->clearCache();
        $this->assertSame(['a_text' => $aText], $this->sut->forCodes(['a_text']));
    }
}
