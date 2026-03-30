<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\Versioning\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product\ReferenceDataNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ReferenceDataNormalizerTest extends TestCase
{
    private ReferenceDataNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new ReferenceDataNormalizer();
    }

}
