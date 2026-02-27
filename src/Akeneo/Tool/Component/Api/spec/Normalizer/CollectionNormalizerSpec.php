<?php

namespace spec\Akeneo\Tool\Component\Api\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CollectionNormalizerSpec extends ObjectBehavior
{
    public function let(SerializerInterface $serializer)
    {
        $serializer->implement(\Symfony\Component\Serializer\Normalizer\NormalizerInterface::class);
        $this->setSerializer($serializer);
    }

    public function it_is_a_serializer_aware_normalizer()
    {
        $this->shouldBeAnInstanceOf(\Symfony\Component\Serializer\Normalizer\NormalizerInterface::class);
        $this->shouldBeAnInstanceOf(\Symfony\Component\Serializer\SerializerAwareInterface::class);
    }

    public function it_supports_iterables()
    {
        $this->supportsNormalization(new ArrayCollection([]), 'external_api')->shouldReturn(true);
        $this->supportsNormalization([], 'external_api')->shouldReturn(true);
    }

    public function it_normalize_collection_of_families(
        $serializer,
        FamilyInterface $familyA,
        FamilyInterface $familyB,
        ArrayCollection $familyCollection,
        \ArrayIterator $familyIterator
    ) {
        $familyCollection->getIterator()->willReturn($familyIterator);
        $familyIterator->rewind()->shouldBeCalled();

        $familyIterator->valid()->willReturn(true, true, false);
        $familyIterator->current()->willReturn($familyA, $familyB);
        $familyIterator->next()->shouldBeCalled();

        $serializer->normalize($familyA, 'external_api', [])->willReturn(
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
            ]
        );
        $serializer->normalize($familyB, 'external_api', [])->willReturn(
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
            ]
        );

        $this->normalize($familyCollection, 'external_api')->shouldReturn(
            [
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
            ]
        );
    }
}
