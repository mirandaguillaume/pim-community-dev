<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Api\Normalizer;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Api\Normalizer\CollectionNormalizer;
use ArrayIterator;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

interface TestSerializerNormalizer extends SerializerInterface, NormalizerInterface
{
}

class CollectionNormalizerTest extends TestCase
{
    private TestSerializerNormalizer|MockObject $serializer;
    private CollectionNormalizer $sut;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(TestSerializerNormalizer::class);
        $this->sut = new CollectionNormalizer();
        $this->sut->setSerializer($this->serializer);
    }

    public function test_it_is_a_serializer_aware_normalizer(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Serializer\Normalizer\NormalizerInterface::class, $this->sut);
        $this->assertInstanceOf(\Symfony\Component\Serializer\SerializerAwareInterface::class, $this->sut);
    }

    public function test_it_supports_iterables(): void
    {
        $this->assertSame(true, $this->sut->supportsNormalization(new ArrayCollection([]), 'external_api'));
        $this->assertSame(true, $this->sut->supportsNormalization([], 'external_api'));
    }

    public function test_it_normalize_collection_of_families(): void
    {
        $familyA = $this->createMock(FamilyInterface::class);
        $familyB = $this->createMock(FamilyInterface::class);
        $familyCollection = $this->createMock(ArrayCollection::class);
        $familyIterator = $this->createMock(ArrayIterator::class);

        $familyCollection->method('getIterator')->willReturn($familyIterator);
        $familyIterator->expects($this->once())->method('rewind');
        $familyIterator->method('valid')->willReturn(true, true, false);
        $familyIterator->method('current')->willReturn($familyA, $familyB);
        $familyIterator->expects($this->once())->method('next');
        $this->serializer->method('normalize')->willReturnCallback(function ($object) use ($familyA, $familyB) {
            if ($object === $familyA) {
                return [
                    'code' => 'familyA',
                    'attributes' => [0 => 'a_date', 1 => 'sku'],
                    'attribute_as_label' => 'sku',
                    'attribute_requirements' => [
                        'ecommerce' => [0 => 'sku'],
                        'tablet' => [0 => 'a_date', 1 => 'sku'],
                    ],
                    'labels' => [],
                ];
            }
            if ($object === $familyB) {
                return [
                    'code' => 'familyB',
                    'attributes' => [0 => 'a_simple_select', 1 => 'sku'],
                    'attribute_as_label' => 'sku',
                    'attribute_requirements' => [
                        'ecommerce' => [0 => 'a_simple_select', 1 => 'sku'],
                        'tablet' => [0 => 'sku'],
                    ],
                    'labels' => [],
                ];
            }
            return null;
        });
        $this->assertSame([
                        [
                            'code' => 'familyA',
                            'attributes' => [
                                0 => 'a_date',
                                1 => 'sku',
                            ],
                            'attribute_as_label' => 'sku',
                            'attribute_requirements' => [
                                'ecommerce' => [
                                    0 => 'sku',
                                ],
                                'tablet' => [
                                    0 => 'a_date',
                                    1 => 'sku',
                                ],
                            ],
                            'labels' => [],
                        ],
                        [
                            'code' => 'familyB',
                            'attributes' => [
                                0 => 'a_simple_select',
                                1 => 'sku',
                            ],
                            'attribute_as_label' => 'sku',
                            'attribute_requirements' => [
                                'ecommerce' => [
                                    0 => 'a_simple_select',
                                    1 => 'sku',
                                ],
                                'tablet' => [
                                    0 => 'sku',
                                ],
                            ],
                            'labels' => [],
                        ],
                    ], $this->sut->normalize($familyCollection, 'external_api'));
    }
}
