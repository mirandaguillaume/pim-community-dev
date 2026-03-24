<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\Versioning;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Flat attribute group normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupNormalizer implements NormalizerInterface
{
    final public const string ITEM_SEPARATOR = ',';

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    protected \Symfony\Component\Serializer\Normalizer\NormalizerInterface $standardNormalizer;

    protected \Symfony\Component\Serializer\Normalizer\NormalizerInterface $translationNormalizer;

    public function __construct(
        NormalizerInterface $standardNormalizer,
        NormalizerInterface $translationNormalizer
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param AttributeGroupInterface $attributeGroup
     *
     * @return array
     */
    public function normalize($attributeGroup, $format = null, array $context = []): array|bool|string|int|float|\ArrayObject|null
    {
        $standardAttributeGroup = $this->standardNormalizer->normalize($attributeGroup, 'standard', $context);
        $flatAttributeGroup = $standardAttributeGroup;

        $flatAttributeGroup['attributes'] = implode(self::ITEM_SEPARATOR, $standardAttributeGroup['attributes']);

        unset($flatAttributeGroup['labels']);
        $flatAttributeGroup += $this->translationNormalizer->normalize(
            $standardAttributeGroup['labels'],
            'flat',
            $context
        );

        return $flatAttributeGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof AttributeGroupInterface && in_array($format, $this->supportedFormats);
    }
}
