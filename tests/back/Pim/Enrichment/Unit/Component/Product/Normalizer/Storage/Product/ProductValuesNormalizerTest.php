<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\Storage\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product\ProductValuesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductValuesNormalizerTest extends TestCase
{
    private ProductValuesNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductValuesNormalizer();
    }

}
