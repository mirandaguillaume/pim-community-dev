<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\PriceNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PriceNormalizerTest extends TestCase
{
    private PriceNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new PriceNormalizer();
    }

}
