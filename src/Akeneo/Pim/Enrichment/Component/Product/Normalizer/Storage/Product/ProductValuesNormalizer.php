<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for a collection of product values
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValuesNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(private readonly NormalizerInterface $valueNormalizer)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($values, $format = null, array $context = []): array|bool|string|int|float|null|\ArrayObject
    {
        $normalizedValues = [];
        foreach ($values as $value) {
            $normalizedValues[] = $this->valueNormalizer->normalize($value, $format, $context);
        }

        $result = empty($normalizedValues) ? [] : array_replace_recursive(...$normalizedValues);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return 'storage' === $format && $data instanceof WriteValueCollection;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
