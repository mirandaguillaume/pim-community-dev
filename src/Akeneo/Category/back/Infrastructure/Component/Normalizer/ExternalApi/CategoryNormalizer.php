<?php

namespace Akeneo\Category\Infrastructure\Component\Normalizer\ExternalApi;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Manager\PositionResolverInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(protected NormalizerInterface $stdNormalizer, protected PositionResolverInterface $positionResolver) {}

    public function normalize($category, $format = null, array $context = []): array|bool|string|int|float|\ArrayObject|null
    {
        $normalizedCategory = $this->stdNormalizer->normalize($category, 'standard', $context);

        if (empty($normalizedCategory['labels'])) {
            $normalizedCategory['labels'] = (object) $normalizedCategory['labels'];
        }

        if (in_array('with_position', $context)) {
            $normalizedCategory['position'] = $this->positionResolver->getPosition($category);
        }

        return $normalizedCategory;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof CategoryInterface && 'external_api' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
