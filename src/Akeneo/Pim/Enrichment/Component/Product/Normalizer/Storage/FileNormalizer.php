<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage;

use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileNormalizer implements NormalizerInterface
{
    protected \Symfony\Component\Serializer\Normalizer\NormalizerInterface $stdNormalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->stdNormalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($file, $format = null, array $context = []): array|bool|string|int|float|\ArrayObject|null
    {
        return $this->stdNormalizer->normalize($file, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof FileInfoInterface && 'storage' === $format;
    }
}
