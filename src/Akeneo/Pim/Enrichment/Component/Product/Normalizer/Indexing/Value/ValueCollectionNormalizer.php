<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for product value collection
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueCollectionNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    final public const INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX = 'indexing_product_and_product_model';

    public function __construct(private readonly NormalizerInterface $normalizer) {}

    /**
     * {@inheritdoc}
     */
    public function normalize($values, $format = null, array $context = []): array|bool|string|int|float|\ArrayObject|null
    {
        $normalizedValues = [];
        foreach ($values as $value) {
            $normalizedValues[] = $this->normalizer->normalize($value, $format, $context);
        }

        $result = empty($normalizedValues) ? [] : array_replace_recursive(...$normalizedValues);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof WriteValueCollection && (
            $format === self::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        );
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
