<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\Versioning\Product;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product\CollectionNormalizer;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CollectionNormalizerTest extends TestCase
{
    private CollectionNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new CollectionNormalizer();
    }

}
