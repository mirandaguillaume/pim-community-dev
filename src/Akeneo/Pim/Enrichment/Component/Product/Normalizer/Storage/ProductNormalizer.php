<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Transform a product object to a standardized array
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    final public const FIELD_ASSOCIATIONS = 'associations';

    public function __construct(private readonly NormalizerInterface $propertiesNormalizer, private readonly NormalizerInterface $associationsNormalizer)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $data = $this->propertiesNormalizer->normalize($product, $format, $context);
        $data[self::FIELD_ASSOCIATIONS] = $this->associationsNormalizer->normalize($product, $format, $context);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductInterface && 'storage' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
