<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\Storage\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product\ProductValueNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductValueNormalizerTest extends TestCase
{
    private ProductValueNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductValueNormalizer();
    }

}
