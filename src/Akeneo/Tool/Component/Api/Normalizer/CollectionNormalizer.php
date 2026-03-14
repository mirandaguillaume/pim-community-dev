<?php

namespace Akeneo\Tool\Component\Api\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Normalize a collection for the external api
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CollectionNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var Serializer $serializer */
    protected $serializer;

    /**
     * {@inheritdoc}
     */
    public function normalize($elements, $format = null, array $context = []): array|bool|string|int|float|\ArrayObject|null
    {
        $normalizedElements = [];

        foreach ($elements as $element) {
            $normalizedElements[] = $this->serializer->normalize($element, $format, $context);
        }

        return $normalizedElements;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return (is_iterable($data)) && 'external_api' === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
