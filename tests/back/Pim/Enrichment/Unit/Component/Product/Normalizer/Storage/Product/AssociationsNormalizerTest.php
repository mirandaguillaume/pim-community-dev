<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\Storage\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product\AssociationsNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssociationsNormalizerTest extends TestCase
{
    private AssociationsNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new AssociationsNormalizer();
    }

}
