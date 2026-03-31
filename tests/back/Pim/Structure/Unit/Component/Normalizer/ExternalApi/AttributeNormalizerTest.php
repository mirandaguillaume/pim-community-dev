<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\ExternalApi;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\AttributeNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizerTest extends TestCase
{
    private NormalizerInterface|MockObject $stdNormalizer;
    private NormalizerInterface|MockObject $translationNormalizer;
    private AttributeNormalizer $sut;

    protected function setUp(): void
    {
        $this->stdNormalizer = $this->createMock(NormalizerInterface::class);
        $this->translationNormalizer = $this->createMock(NormalizerInterface::class);
        $this->sut = new AttributeNormalizer($this->stdNormalizer, $this->translationNormalizer);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AttributeNormalizer::class, $this->sut);
    }

    public function test_it_supports_an_attribute(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'whatever'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'external_api'));
        $this->assertSame(false, $this->sut->supportsNormalization($attribute, 'whatever'));
        $this->assertSame(true, $this->sut->supportsNormalization($attribute, 'external_api'));
    }

    public function test_it_normalizes_an_attribute(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getGroup')->willReturn(null);
        $attribute->method('getType')->willReturn(null);
        $data = [
                    'code' => 'my_attribute',
                    'labels' => ['en_US' => 'english_label'],
                ];
        $this->stdNormalizer->expects($this->once())->method('normalize')->with($attribute, 'standard', [])->willReturn($data);
        $this->assertEquals(array_merge($data, ['group_labels' => null]), $this->sut->normalize($attribute, 'external_api', []));
    }

    public function test_it_normalizes_an_attribute_with_its_group_labels(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);
        $group = $this->createMock(AttributeInterface::class);

        $attribute->method('getGroup')->willReturn($group);
        $attribute->method('getType')->willReturn(null);
        $data = [
                    'code' => 'my_attribute',
                    'labels' => ['en_US' => 'english_label'],
                    'group' => 'attributeGroupA',
                ];
        $this->stdNormalizer->method('normalize')->with($attribute, 'standard', [])->willReturn($data);
        $this->translationNormalizer->expects($this->once())->method('normalize')->with($group, 'external_api', [])->willReturn(['en_US' => 'attribute group A']);
        $this->assertEquals(array_merge($data, ['group_labels' => ['en_US' => 'attribute group A']]), $this->sut->normalize($attribute, 'external_api', []));
    }

    public function test_it_normalizes_an_attribute_with_empty_labels(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);
        $group = $this->createMock(AttributeInterface::class);

        $attribute->method('getGroup')->willReturn($group);
        $attribute->method('getType')->willReturn(null);
        $data = ['code' => 'my_attribute', 'labels' => [], 'group' => 'attributeGroupA'];
        $this->stdNormalizer->method('normalize')->with($attribute, 'standard', [])->willReturn($data);
        $this->translationNormalizer->expects($this->once())->method('normalize')->with($group, 'external_api', [])->willReturn([]);
        $this->assertEquals([
                        'code' => 'my_attribute',
                        'labels' => (object)[],
                        'group' => 'attributeGroupA',
                        'group_labels' => (object)[],
                    ], $this->sut->normalize($attribute, 'external_api', []));
    }

    public function test_it_normalizes_identifier_attribute(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getGroup')->willReturn(null);
        $attribute->method('getType')->willReturn(AttributeTypes::IDENTIFIER);
        $attribute->expects($this->once())->method('isMainIdentifier')->willReturn(true);
        $data = [
                    'code' => 'my_identifier_attribute',
                    'labels' => ['en_US' => 'english_label'],
                ];
        $this->stdNormalizer->expects($this->once())->method('normalize')->with($attribute, 'standard', [])->willReturn($data);
        $dataNormalizedexpected = array_merge($data, ['group_labels' => null, 'is_main_identifier' => true]);
        $this->assertEquals($dataNormalizedexpected, $this->sut->normalize($attribute, 'external_api', []));
    }
}
