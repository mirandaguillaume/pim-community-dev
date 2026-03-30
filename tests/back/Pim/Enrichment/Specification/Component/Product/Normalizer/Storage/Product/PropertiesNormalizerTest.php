<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\Storage\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product\PropertiesNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PropertiesNormalizerTest extends TestCase
{
    private PropertiesNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new PropertiesNormalizer();
    }

}
