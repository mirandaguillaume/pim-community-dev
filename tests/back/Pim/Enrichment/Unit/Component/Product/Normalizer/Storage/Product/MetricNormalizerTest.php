<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\Storage\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product\MetricNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MetricNormalizerTest extends TestCase
{
    private MetricNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new MetricNormalizer();
    }

}
