<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\InternalApi;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupNormalizer implements NormalizerInterface
{
    /** @var array $supportedFormats */
    protected $supportedFormats = ['internal_api'];

    protected \Symfony\Component\Serializer\Normalizer\NormalizerInterface $normalizer;

    protected \Doctrine\Persistence\ObjectRepository $attributeRepository;

    public function __construct(NormalizerInterface $normalizer, ObjectRepository $attributeRepository)
    {
        $this->normalizer          = $normalizer;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attributeGroup, $format = null, array $context = []): array|bool|string|int|float|\ArrayObject|null
    {
        $standardAttributeGroup = $this->normalizer->normalize($attributeGroup, 'standard', $context);

        $attributes = $this->attributeRepository->findBy(
            ['code' => $standardAttributeGroup['attributes']]
        );
        $sortOrder = [];
        foreach ($attributes as $attribute) {
            $sortOrder[$attribute->getCode()] = $attribute->getSortOrder();
        }
        $standardAttributeGroup['attributes_sort_order'] = $sortOrder;
        $standardAttributeGroup['meta'] = [
            'id' => $attributeGroup->getId(),
        ];

        return $standardAttributeGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof AttributeGroupInterface && in_array($format, $this->supportedFormats);
    }
}
